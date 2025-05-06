<?php

use PHPUnit\Framework\TestCase;
define('PHPUNIT_RUNNING', true);


require_once __DIR__ . '/../../Model/B2/DemandeB2.php';
require_once __DIR__ . '/../../ScriptRecurenceAutomatiqueB2/RecurenceAutomatique.php';

class RecurrenceServiceTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        //Supprimer dans l'ordre inverse des dépendances pour respecter les clés étrangères
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
        $this->pdo->exec("DELETE FROM tache");
        $this->pdo->exec("DELETE FROM media");
        $this->pdo->exec("DELETE FROM est");
        $this->pdo->exec("DELETE FROM demande");
        $this->pdo->exec("DELETE FROM recurrence");
    
        // On peut aussi réinitialiser les ID si besoin
        $this->pdo->exec("ALTER TABLE tache AUTO_INCREMENT = 1");
        $this->pdo->exec("ALTER TABLE demande AUTO_INCREMENT = 1");
        $this->pdo->exec("ALTER TABLE recurrence AUTO_INCREMENT = 1");
    
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
        // Vérifie qu'au moins le statut "Nouvelle" existe (utile pour tous les tests)
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM statut WHERE nom_statut = 'Nouvelle'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $this->pdo->exec("INSERT INTO statut (nom_statut) VALUES ('Nouvelle')");
        }
    }
    

    private function ajouterRecurrence(array $data): void
    { 
    

        $stmt = $this->pdo->prepare("
            INSERT INTO recurrence (
                id_recurrence, sujet_reccurrence, desc_recurrence, date_anniv_recurrence,
                valeur_freq_recurrence, valeur_rappel_recurrence, id_lieu, id_unite, id_unite_1
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['id_recurrence'],
            $data['sujet'],
            $data['desc'],
            $data['date_anniv'],
            $data['frequence'],
            $data['rappel'],
            $data['lieu'],
            $data['unite'],
            $data['unite1']
        ]);
    }

   // Vérifie que la génération fonctionne bien lorsque la date de rappel correspond au jour J
    public function testGenererDemandeLeJourJ()
    {
        $today = new DateTime();
        $anniv = (clone $today)->modify('+1 day')->format('Y-m-d');

        $this->ajouterRecurrence([
            'id_recurrence' => 1,
            'sujet' => 'Test Sujet',
            'desc' => 'Description test',
            'date_anniv' => $anniv,
            'frequence' => 1,
            'rappel' => 1,
            'lieu' => 1,
            'unite' => 1,
            'unite1' => 1
        ]);

        $service = new RecurrenceService($this->pdo, $today);
        $logs = $service->genererDemandes();

        $this->assertStringContainsString("Demande générée", implode("\n", $logs));
    }
    
    // Vérifie que la méthode ne crée pas une nouvelle demande si elle a déjà été générée ce jour-là
    public function testPasDeDuplicationLeJourJ()
    {
        $today = new DateTime();
        $anniv = (clone $today)->modify('+1 day')->format('Y-m-d');

        $this->ajouterRecurrence([
            'id_recurrence' => 3,
            'sujet' => 'Déjà générée',
            'desc' => 'Demande déjà générée',
            'date_anniv' => $anniv,
            'frequence' => 1,
            'rappel' => 1,
            'lieu' => 1,
            'unite' => 1,
            'unite1' => 1
        ]);

        // Crée une demande manuellement
        $this->pdo->prepare("
            INSERT INTO demande (num_ticket_dmd, sujet_dmd, description_dmd, date_creation_dmd, id_recurrence, id_utilisateur, id_lieu)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            '2025-001', 'Déjà générée', 'Description test', $today->format('Y-m-d'), 3, 1, 1
        ]);

        $service = new RecurrenceService($this->pdo, $today);
        $logs = $service->genererDemandes();

        $this->assertStringContainsString("Demande déjà générée aujourd'hui", implode("\n", $logs));
    }
    
// Vérifie que la date de rappel est correctement calculée un jour avant la date anniversaire 
    public function testRappelJourAvant()
    {
        $today = new DateTime('2025-04-06');
        $dateAnniv = '2020-04-06';

        $dateRappel = RecurrenceService::calculerProchaineDateAvecRappel(
            dateAnniv: $dateAnniv, valeurFreq: 1, valeurRappel: 1, idUniteFrequence: 1, idUniteRappel: 1, dateCourante: $today
        );

        $this->assertEquals('2025-04-05', $dateRappel->format('Y-m-d'));
    }

    // Vérifie que si le rappel est à 0, la date retournée est bien celle du jour J
    public function testProchaineDateAvecRappelZeroRetourneJourJ()
    {
        $today = new DateTime('2025-04-10');
        $dateAnniv = '2024-04-10';

        $date = RecurrenceService::calculerProchaineDateAvecRappel(
            dateAnniv: $dateAnniv, valeurFreq: 1, valeurRappel: 0, idUniteFrequence: 1, idUniteRappel: 1, dateCourante: $today
        );

        $this->assertNotNull($date);
        $this->assertEquals('2025-04-10', $date->format('Y-m-d'));
    }

    // Vérifie que si le rappel est à 0, la date retournée est bien celle du jour J
    public function testDateAvecFrequenceEnJours()
    {
        $today = new DateTime('2025-04-10');
        $dateAnniv = '2025-04-01';

        $date = RecurrenceService::calculerProchaineDateAvecRappel(
            $dateAnniv, 3, 1, 1, 1, $today
        );

        $this->assertEquals('2025-04-09', $date->format('Y-m-d'));
    }

    // Vérifie que si la fréquence est à 0, aucune date n’est retournée (null)
    public function filestestRetourNullSiFrequenceZero()
    {
        $today = new DateTime('2025-04-06');
        $date = RecurrenceService::calculerProchaineDateAvecRappel(
            '2024-04-06', 0, 0, 1, 1, $today
        );

        $this->assertNull($date);
    }

    // Vérifie le calcul correct lorsque l’unité est en mois
    public function testDateAvecRappelEnMois()
    {
        $today = new DateTime('2025-06-01');
        $dateAnniv = '2025-01-01';

        $date = RecurrenceService::calculerProchaineDateAvecRappel(
            $dateAnniv, 1, 1, 2, 2, $today
        );

        $this->assertEquals('2025-05-01', $date->format('Y-m-d'));
    }

    // Vérifie le calcul correct pour une fréquence annuelle
    public function testDateAvecRappelEnAnnees()
    {
        $today = new DateTime('2027-01-01');
        $dateAnniv = '2020-01-01';

        $date = RecurrenceService::calculerProchaineDateAvecRappel(
            $dateAnniv, 1, 1, 3, 3, $today
        );

        $this->assertEquals('2026-01-01', $date->format('Y-m-d'));
    }

     // Vérifie que le code lève une exception si l’unité de fréquence est invalide
    public function testUniteInvalideAvecException()
    {
        $this->expectException(InvalidArgumentException::class);

        RecurrenceService::calculerProchaineDateAvecRappel(
            '2025-04-06',
            1,  // fréquence
            1,  // rappel
            999, // Unité invalide
            1,   // Unité de rappel
            new DateTime('2025-04-06')
        );
    }

      // Vérifie que s’il n’y a aucune récurrence, rien n’est généré
    public function testPasDeRecurrence()
    {
        $today = new DateTime();
        $service = new RecurrenceService($this->pdo, $today);
        $logs = $service->genererDemandes();
        $this->assertEmpty($logs);
    }

    // Vérifie que la demande est bien générée même sans rappel (valeur 0)
    public function testDemandeSansRappel()
    {
        $today = new DateTime();
        $anniv = $today->format('Y-m-d');  // Anniversaire aujourd'hui
    
        $this->ajouterRecurrence([
            'id_recurrence' => 2,
            'sujet' => 'Test Sujet sans rappel',
            'desc' => 'Description test sans rappel',
            'date_anniv' => $anniv,
            'frequence' => 1, // 1 jour
            'rappel' => 0, // Pas de rappel
            'lieu' => 1,
            'unite' => 1,
            'unite1' => 1
        ]);
    
        $service = new RecurrenceService($this->pdo, $today);
        $logs = $service->genererDemandes();
        $this->assertStringContainsString("Demande générée", implode("\n", $logs));
    }
    
     // Vérifie que la demande n’est pas générée si la date prévue est ultérieure
    public function testPasDeDemandeSiPasAujourdHui()
    {
        $today = new DateTime();
        // On avance l'anniversaire de 10 jours pour simuler que ce n'est pas prévu aujourd'hui
        $anniv = (clone $today)->modify('+10 days')->format('Y-m-d'); 
    
        $this->ajouterRecurrence([
            'id_recurrence' => 3,
            'sujet' => 'Test Sujet non généré',
            'desc' => 'Description test',
            'date_anniv' => $anniv,
            'frequence' => 1,
            'rappel' => 1,
            'lieu' => 1,
            'unite' => 1,
            'unite1' => 1
        ]);
    
        $service = new RecurrenceService($this->pdo, $today);
        $logs = $service->genererDemandes();
        
        // Vérification que "Pas prévu aujourd'hui" est bien dans les logs
        $this->assertStringContainsString("Pas prévu aujourd'hui", implode("\n", $logs));
    }
    
    // Vérifie que l’exception est bien levée pour une unité de fréquence invalide
    public function testExceptionSiUniteFrequenceInvalide()
    {
        $this->expectException(InvalidArgumentException::class);
        
        RecurrenceService::calculerProchaineDateAvecRappel(
            '2025-04-06',
            1,  // fréquence
            1,  // rappel
            999, // Unité invalide pour la fréquence
            1,   // Unité de rappel
            new DateTime('2025-04-06')
        );
    }

    // Vérifie que le système ignore la génération si la date calculée ne correspond pas au jour courant
    public function testRecurrenceIgnoréeSiDateDiffèreDuJourJ()
{
    $today = new DateTime(); // Date actuelle
    // Assurons-nous que l'anniversaire est dans le futur (par exemple dans 10 jours)
    $anniv = (clone $today)->modify('+10 days')->format('Y-m-d'); // Date d'anniversaire dans 10 jours

    // Ajout de la récurrence avec une date d'anniversaire dans le futur
    $this->ajouterRecurrence([ 
        'id_recurrence' => 2,
        'sujet' => 'Test Sujet non généré',
        'desc' => 'Description test',
        'date_anniv' => $anniv,  // L'anniversaire est dans 10 jours
        'frequence' => 1,  // fréquence en jours
        'rappel' => 1,  // Un rappel est défini
        'lieu' => 1,
        'unite' => 1,
        'unite1' => 1
    ]);

    // Exécution de la méthode pour générer les demandes
    $service = new RecurrenceService($this->pdo, $today);
    $logs = $service->genererDemandes();

    // Vérification que "Pas prévu aujourd'hui" apparaît bien dans les logs
    $this->assertStringContainsString("Pas prévu aujourd'hui", implode("\n", $logs));
}

// Vérifie que l’exception est bien levée si l’unité de rappel est invalide
public function testExceptionSiUniteRappelInvalide()
{
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Unité de rappel invalide');

    RecurrenceService::calculerProchaineDateAvecRappel(
        '2025-04-06',
        1,    // fréquence
        1,    // rappel > 0
        1,    // unité de fréquence valide
        999,  // unité de rappel invalide (déclenche le throw)
        new DateTime('2025-04-06')
    );
}

// Vérifie que la demande n’est pas générée si la date avec rappel ne tombe pas sur le jour J
public function testRappelMaisPasLeBonJour()
{
    $today = new DateTime('2025-04-10'); // aujourd’hui
    $anniv = '2025-04-12'; // dans deux jours
    // rappel de 1 jour → attendrait 2025-04-11 → pas aujourd’hui

    $this->ajouterRecurrence([
        'id_recurrence' => 9,
        'sujet' => 'Pas prévu ce jour',
        'desc' => 'Demande prévue demain',
        'date_anniv' => $anniv,
        'frequence' => 1,
        'rappel' => 1,
        'lieu' => 1,
        'unite' => 1,
        'unite1' => 1
    ]);

    $service = new RecurrenceService($this->pdo, $today);
    $logs = $service->genererDemandes();

    $this->assertStringContainsString("Pas prévu aujourd'hui", implode("\n", $logs));
}

// Vérifie que la méthode continue si la date calculée avec rappel ne correspond pas à aujourd’hui
public function testProchaineDateDiffereDeJourJ()
{
    $today = new DateTime('2025-04-10'); // aujourd’hui
    $anniv = '2025-04-13'; // dans 3 jours
    // Avec rappel = 1 jour, prochaine date = 2025-04-12 → donc ≠ 10 → "Pas prévu aujourd'hui"

    $this->ajouterRecurrence([
        'id_recurrence' => 42,
        'sujet' => 'Différé',
        'desc' => 'Date avec rappel ≠ aujourd’hui',
        'date_anniv' => $anniv,
        'frequence' => 1,
        'rappel' => 1,
        'lieu' => 1,
        'unite' => 1,   // fréquence = jours
        'unite1' => 1   // rappel = jours
    ]);

    $service = new RecurrenceService($this->pdo, $today);
    $logs = $service->genererDemandes();

    $this->assertStringContainsString("Pas prévu aujourd'hui", implode("\n", $logs));
}

// Vérifie que les logs affichent bien la date de rappel calculée et le message "Pas prévu aujourd'hui"
public function testLogProchaineDateEtPasPrevuAujourdHui()
{
    $today = new DateTime('2025-04-10'); // jour J
    $anniv = '2025-04-13';               // date anniversaire

    // fréquence = 1 jour, donc prochaine = 13
    // rappel = 1 jour => -1 => 12
    // donc date calculée = 2025-04-12 ≠ jourJ = 2025-04-10

    $this->ajouterRecurrence([
        'id_recurrence' => 88,
        'sujet' => 'Mismatch jour',
        'desc' => 'Doit déclencher continue car date ≠ jourJ',
        'date_anniv' => $anniv,
        'frequence' => 1,
        'rappel' => 1,
        'lieu' => 1,
        'unite' => 1,   // jour
        'unite1' => 1   // jour
    ]);

    $service = new RecurrenceService($this->pdo, $today);
    $logs = $service->genererDemandes();

    // Ce log est ajouté UNIQUEMENT dans le bloc que tu veux couvrir
    $this->assertStringContainsString("Pas prévu aujourd'hui", implode("\n", $logs));
    $this->assertStringContainsString("Prochaine date avec rappel : 2025-04-12", implode("\n", $logs));
}

// Vérifie que le système déclenche une exception si le statut "Nouvelle" est manquant dans la base
public function testExceptionSiStatutNouvelleManquant()
{
    // Supprime temporairement le statut "Nouvelle"
    $this->pdo->prepare("DELETE FROM est")->execute();
    $this->pdo->prepare("DELETE FROM statut WHERE nom_statut = 'Nouvelle'")->execute();

    $today = new DateTime('2025-04-10');
    $anniv = '2025-04-11';

    $this->ajouterRecurrence([
        'id_recurrence' => 99,
        'sujet' => 'Statut manquant',
        'desc' => 'Test d’erreur',
        'date_anniv' => $anniv,
        'frequence' => 1,
        'rappel' => 1,
        'lieu' => 1,
        'unite' => 1,
        'unite1' => 1
    ]);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage("Statut 'Nouvelle' introuvable");

    try {
        $service = new RecurrenceService($this->pdo, $today);
        $service->genererDemandes();
    } finally {
        // Toujours remettre le statut, même si l’exception est levée
        $this->pdo->prepare("INSERT INTO statut (nom_statut) VALUES ('Nouvelle')")->execute();
    }
}

 // Vérifie que si la fréquence est négative, la fonction retourne null
public function testRetourNullSiFrequenceNegative()
{
    $today = new DateTime('2025-04-10');

    $resultat = RecurrenceService::calculerProchaineDateAvecRappel(
        '2025-01-01',
        -1, // fréquence négative
        0,
        1,
        1,
        $today
    );

    $this->assertNull($resultat);
}

}


