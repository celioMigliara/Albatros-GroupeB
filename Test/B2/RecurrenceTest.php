<?php

use PHPUnit\Framework\TestCase;
define('PHPUNIT_RUNNING', true);
require_once __DIR__ . '/../../Secure/B2/session.php';
require_once __DIR__ . '/../../Model/B2/demande.php';

class RecurrenceTest  extends TestCase{

    private PDO $pdo;
    private RecurrenceModel $modele;

    protected function setUp(): void {
        // 🔸 Connexion à la base de test MySQL
        $this->pdo = Database::getInstance()->getConnection();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->modele = new RecurrenceModel($this->pdo);

        // 🔸 Nettoyer les tables utilisées pour le test
        $this->pdo->exec("DELETE FROM est");
        $this->pdo->exec("DELETE FROM demande");
        $this->pdo->exec("DELETE FROM recurrence");
        
    }

    // Teste si la session démarre correctement
    public function testSessionStart()
    {
        // Assure-toi que la session est démarrée
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Vérifie que la session existe
        $this->assertTrue(session_status() == PHP_SESSION_ACTIVE, "La session n'est pas active.");
    }

    // Teste si le token CSRF est bien généré
    public function testGenerateCsrfToken()
    {
        // Démarre une session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
  
        // Génère un token CSRF
        $token = generateCsrfToken();
  
        // Vérifie que le token est bien généré
        $this->assertNotEmpty($token, "Le token CSRF devrait être généré.");
        $this->assertTrue(strlen($token) > 0, "Le token CSRF ne devrait pas être vide.");
    }

    // Teste si le token CSRF est valide
    public function testValidateCsrfToken()
    {
        // Démarre une session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Génère un token CSRF
        $validToken = generateCsrfToken();

        // Ajoute un token CSRF incorrect pour tester
        $invalidToken = 'invalid-token';

        // Teste un token valide
        $this->assertTrue(validateCsrfToken($validToken), "Le token valide ne devrait pas échouer.");
        
        // Teste un token invalide
        $this->assertFalse(validateCsrfToken($invalidToken), "Le token invalide ne devrait pas être validé.");
    }

    // Teste si la session est protégée par le user-agent
    public function testUserAgentSessionProtection()
    {
        // Démarre une session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
 
        // Définit un agent utilisateur fictif pour ce test
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
 
        // Initialise $_SESSION['user_agent']
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
 
        // Vérifie que la session a bien l'agent utilisateur
        $this->assertEquals($_SESSION['user_agent'], $_SERVER['HTTP_USER_AGENT'], "L'agent utilisateur dans la session ne correspond pas.");
    }

    public function testAjouterRecurrence(): void {

        // 🔹 Date actuelle + 1 jour
        $date = new DateTime();
        $date->modify('+1 day');
        $dateStr = $date->format('Y-m-d');
    
        $result = $this->modele->ajouterRecurrence(
            "Test Sujet", "Description de test", $dateStr, 8, null, 2, "mois", null
        );
    
        $this->assertTrue($result['success']);
        $this->assertEquals("Récurrence ajoutée avec succès !", $result['message']);
        
    }

    public function testAjouterRecurrenceEtRecuperer(): void {
         // 🔹 Date actuelle + 1 jour
         $date = new DateTime();
         $date->modify('+1 day');
         $dateStr = $date->format('Y-m-d');

        $result = $this->modele->ajouterRecurrence(
            "Test Sujet 2", "Description de test puis repris", $dateStr,8,null,2,"mois",null
        );

        $this->assertTrue($result['success']);
        $this->assertEquals("Récurrence ajoutée avec succès !", $result['message']);

        // Vérifie que les données sont bien insérées
        $stmt = $this->pdo->query("SELECT id_recurrence , desc_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test Sujet 2'");
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $result = $this->modele->getById($data['id_recurrence']);

        $this->assertNotEmpty($data);
        $this->assertEquals("Description de test puis repris", $data['desc_recurrence']);
    }

    public function testAjouterRecurrenceFreqNull(): void {
        $date = new DateTime();
        $date->modify('+1 day');
        $dateStr = $date->format('Y-m-d');
    
        $result = $this->modele->ajouterRecurrence(
            "Test Sujet Freq Invalide", "Description de test", $dateStr, null, null, 2, "mois", null
        );
    
        $this->assertFalse($result['success']);
        $this->assertEquals("Entrez un nombre pour la fréquence", $result['message']);
    }
    
    public function testAjouterRecurrenceFreqNegative(): void {
        $date = new DateTime();
        $date->modify('+1 day');
        $dateStr = $date->format('Y-m-d');
    
        $result = $this->modele->ajouterRecurrence(
            "Test Sujet Freq Invalide", "Description de test", $dateStr, -5, null, 2, "mois", null
        );
    
        $this->assertFalse($result['success']);
        $this->assertEquals("La fréquence doit être un nombre positif", $result['message']);
    }
    
    public function testAjouterRecurrenceSansUnitefrequence(): void {
        $date = new DateTime();
        $date->modify('+1 day');
        $dateStr = $date->format('Y-m-d');
    
        $result = $this->modele->ajouterRecurrence(
            "TestSujet", "Description de test", $dateStr, 8, null, 2, null, "jour"
        );
    
        $this->assertFalse($result['success']);
        $this->assertEquals("Unité de temps invalide.", $result['message']);
    }
    
    public function testAjouterRecurrenceSansTitre(): void {
        $date = new DateTime();
        $date->modify('+1 day');
        $dateStr = $date->format('Y-m-d');
    
        $result = $this->modele->ajouterRecurrence(
            "", "Description de test", $dateStr, 8, null, 2, "mois", null
        );
    
        $this->assertFalse($result['success']);
        $this->assertEquals("Entrez un titre pour la maintenance", $result['message']);
    }

    public function testAjouterRecurrenceLieuInactif(): void {
        $sujet = 'Test';
        $description = 'Description test';
        $dateAnniv = (new DateTime('+1 day'))->format('Y-m-d');
        $frequence = 5; 
        $rappel = 1;

        // Vérifier si le lieu existe déjà
        $check = $this->pdo->prepare("SELECT id_lieu FROM lieu WHERE nom_lieu = ? AND id_batiment = ?");
        $check->execute(['LieuInactif Test', 2]);
        $existing = $check->fetch();

        if (!$existing) {
            $stmt = $this->pdo->prepare(" 
                INSERT INTO lieu (nom_lieu, actif_lieu, id_batiment) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute(['LieuInactif Test', 0, 2]);
            $idLieu = $this->pdo->lastInsertId();
        } else {
            $idLieu = $existing['id_lieu'];
        }

        $uniteFrequence = 'jour';
        $uniteRappel = 'jour';


        $result = $this->modele->ajouterRecurrence(
            $sujet,$description,$dateAnniv,$frequence,$rappel,$idLieu,$uniteFrequence,$uniteRappel
        );
    
        $this->assertFalse($result['success']);
        $this->assertEquals("Le lieu sélectionné n'est pas valide ou inactif.", $result['message']);
    }
    
    public function testAjouterRecurrenceSansfrequenceRappel(): void {
        $date = new DateTime();
        $date->modify('+1 day');
        $dateStr = $date->format('Y-m-d');
    
        $result = $this->modele->ajouterRecurrence(
            "TestSujet", "Description de test", $dateStr, 8, null, 2, "mois", "jour"
        );
    
        $this->assertTrue($result['success']);
    }
    
    public function testAjouterRecurrenceFrequenceEndessousfrequenceRappel(): void {
        $date = new DateTime();
        $date->modify('+1 day');
        $dateStr = $date->format('Y-m-d');
    
        $result = $this->modele->ajouterRecurrence(
            "TestSujet", "Description de test", $dateStr, 2, 5, 7, "jour", "jour"
        );
    
        $this->assertFalse($result['success']);
        $this->assertEquals("Le délai de rappel ne peut être supérieur à la fréquence de la maintenance.", $result['message']);
    }
    
    public function testAjouterRecurrenceSansUniteRappelMaisAvecFrequence(): void {
        $date = new DateTime();
        $date->modify('+1 day');
        $dateStr = $date->format('Y-m-d');
    
        $result = $this->modele->ajouterRecurrence(
            "TestSujet", "Description de test", $dateStr, 2, 8, 2, "mois", null
        );
    
        $this->assertFalse($result['success']);
        $this->assertEquals("Vous ne pouvez pas insérer une fréquence de rappel si vous n'avez pas sélectionné une unité de rappel", $result['message']);
    }

     public function testAjouterRecurrenceUniteRappelEtDelaiPlusEleveQueFreq(): void {
        $sujet = 'Test';
        $description = 'Description test';
        $dateAnniv = (new DateTime('+1 day'))->format('Y-m-d');
        $frequence = 20; 
        $rappel = 21;
        $idLieu = 1;

        $uniteFrequence = 'jour';
        $uniteRappel = 'mois';


        $result = $this->modele->ajouterRecurrence(
            $sujet,$description,$dateAnniv,$frequence,$rappel,$idLieu,$uniteFrequence,$uniteRappel
        );
    
        $this->assertFalse($result['success']);
        $this->assertEquals("Le délai de rappel et l'unité de rappel ne peuvent êtres supérieur à la fréquence de la maintenance.", $result['message']);
    }
    
    public function testAjouterRecurrenceDateInvalide(): void {
        $date = new DateTime();
        $date->modify('-1 month');
        $dateStr = $date->format('Y-m-d');
    
        $result = $this->modele->ajouterRecurrence(
            "TestSujet", "Description de test", $dateStr, 2, null, 2, "mois", null
        );
    
        $this->assertFalse($result['success']);
        $this->assertEquals("La date n'est pas valide", $result['message']);
    }

    public function testAjouterRecurrenceRappelNegatif(): void {
        $date = new DateTime();
        $date->modify('+1 month');
        $dateStr = $date->format('Y-m-d');
    
        $result = $this->modele->ajouterRecurrence(
            "TestSujet", "Description de test", $dateStr, 5, -2, 2, "mois", "jour"
        );
    
        $this->assertFalse($result['success']);
        $this->assertEquals("Le délai de rappel doit être un nombre positif.", $result['message']);
    }

    public function testModifierRecurrence(): void {

        $sujet = 'Test';
        $description = 'Description test';
        $dateAnniv = (new DateTime('+1 day'))->format('Y-m-d');
        $frequence = 5;
        $rappel = null;
        $idLieu = 1;
        $uniteFrequence = 'jour';
        $uniteRappel = null;

        $result = $this->modele->ajouterRecurrence(
            $sujet, $description, $dateAnniv, $frequence, $rappel, $idLieu, $uniteFrequence, $uniteRappel);

        $this->assertTrue($result['success']);

        $stmt = $this->pdo->query("SELECT id_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test Sujet_modif'");
        $idRecurrence = $stmt->fetchColumn(); // On récupère l'ID

        $sujet = 'Test_sujet_modifie';
        $description = 'Description de test modifié';
        $dateAnniv = (new DateTime('+5 day'))->format('Y-m-d');
        $frequence = 9; 
        $rappel = null;
        $idLieu = 1;
        $uniteFrequence = 'mois';
        $uniteRappel = null;

        $result = $this->modele->update($idRecurrence,$sujet,$description, $dateAnniv, $frequence, $rappel, $idLieu, $uniteFrequence, $uniteRappel);
        $this->assertTrue($result['success']);

        $this->assertEquals("Récurrence mise à jour avec succès !", $result['message']);
    }

    public function testModifierRecurrenceEtRecuperer(): void {

        $date = new DateTime();
        $date->modify('+1 day');
        $dateStr = $date->format('Y-m-d');

        $result = $this->modele->ajouterRecurrence(
            "Test Sujet_modif", "Description de test", $dateStr,8,null,2,"mois",null
        );

        $this->assertTrue($result['success']);

        $stmt = $this->pdo->query("SELECT id_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test Sujet_modif'");
        $idRecurrence = $stmt->fetchColumn(); // On récupère l'ID

        $result = $this->modele->update($idRecurrence,"Test_sujet_modifie","Description de test modifié",$dateStr,5,2,4,"année","mois");
        $this->assertTrue($result['success']);

        $this->assertEquals("Récurrence mise à jour avec succès !", $result['message']);

        // Vérifie que les données sont bien insérées
        $stmt = $this->pdo->query("SELECT id_recurrence , desc_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test_sujet_modifie'");
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $result = $this->modele->getById($data['id_recurrence']);

        $this->assertNotEmpty($data);
        $this->assertEquals("Description de test modifié", $data['desc_recurrence']);
    }

    public function testRecupererRecurrenceSupprime(): void {
         // 🔹 Date actuelle + 1 jour
         $date = new DateTime();
         $date->modify('+1 day');
         $dateStr = $date->format('Y-m-d');

        $result = $this->modele->ajouterRecurrence(
            "Test Sujet Inex", "Description de test a supprimé puis récupéré", $dateStr,8,null,2,"mois",null
        );

        $this->assertTrue($result['success']);
        $this->assertEquals("Récurrence ajoutée avec succès !", $result['message']);

        $stmt = $this->pdo->query("SELECT id_recurrence , desc_recurrence FROM recurrence WHERE desc_recurrence
         = 'Description de test a supprimé puis récupéré'");
        $idRecurrence = $stmt->fetchColumn(); // On récupère l'ID

        $result = $this->modele->delete($idRecurrence);

        $this->assertTrue($result['success']);

        $stmt = $this->pdo->query("SELECT id_recurrence, desc_recurrence FROM recurrence WHERE desc_recurrence 
        = 'Description de test a supprimé puis récupéré'");
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNull($result = $this->modele->getById($data['id_recurrence']));
        
    }

    public function testModifierRecurrenceDate(): void {

        $date = new DateTime();
        $date->modify('+1 day');
        $dateStr = $date->format('Y-m-d');

        $result = $this->modele->ajouterRecurrence(
            "Test Sujet_modif", "Description de test", $dateStr,8,null,2,"mois",null
        );

        $this->assertTrue($result['success']);

        $stmt = $this->pdo->query("SELECT id_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test Sujet_modif'");
        $idRecurrence = $stmt->fetchColumn(); // On récupère l'ID

        
        $date2 = new DateTime();
        $date2->modify('+5 day');
        $dateStr2 = $date2->format('Y-m-d');

        $result = $this->modele->update($idRecurrence,"Test_sujet_modifie_Date","Description de test modifié",$dateStr2,5,2,4,"année","mois");
        $this->assertTrue($result['success']);

          // Vérifie la date
          $stmt = $this->pdo->query("SELECT id_recurrence , date_anniv_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test_sujet_modifie_Date'");
          $data = $stmt->fetch(PDO::FETCH_ASSOC);
          $result = $this->modele->getById($data['id_recurrence']);
  
          $this->assertNotEmpty($data);
          $this->assertEquals($dateStr2, $data['date_anniv_recurrence']);
    }

      public function testModifierRecurrenceLieuInactif(): void {
        $sujet = 'Test';
        $description = 'Description test';
        $dateAnniv = (new DateTime('+1 day'))->format('Y-m-d');
        $frequence = 5; 
        $rappel = 1;
        $idLieu = 1;
        $uniteFrequence = 'jour';
        $uniteRappel = 'jour';

        $result = $this->modele->ajouterRecurrence(
            $sujet,$description,$dateAnniv,$frequence,$rappel,$idLieu,$uniteFrequence,$uniteRappel
        );

        $this->assertTrue($result['success']);

        $stmt = $this->pdo->query("SELECT id_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test Sujet_modif'");
        $idRecurrence = $stmt->fetchColumn(); // On récupère l'ID

        $sujet = 'Test_sujet_modifie';
        $description = 'Description de test modifié';
        $dateAnniv = (new DateTime('+2 day'))->format('Y-m-d');
        $frequence = 8; // Valeur invalide
        $rappel = 3;
      
        // Vérifier si le lieu existe déjà
        $check = $this->pdo->prepare("SELECT id_lieu FROM lieu WHERE nom_lieu = ? AND id_batiment = ?");
        $check->execute(['LieuInactif Test', 2]);
        $existing = $check->fetch();

        if (!$existing) {
            $stmt = $this->pdo->prepare(" 
                INSERT INTO lieu (nom_lieu, actif_lieu, id_batiment) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute(['LieuInactif Test', 0, 2]);
            $idLieu = $this->pdo->lastInsertId();
        } else {
            $idLieu = $existing['id_lieu'];
        }

        $uniteFrequence = null;
        $uniteRappel = 'jour';

        $result = $this->modele->update($idRecurrence,$sujet,$description, $dateAnniv, $frequence, $rappel, $idLieu, $uniteFrequence, $uniteRappel);
        $this->assertFalse($result['success']);
        $this->assertEquals("Le lieu sélectionné n'est pas valide ou inactif.", $result['message']);
    }

    public function testModifierRecurrenceFrequenceNegative(): void {

        $date = new DateTime();
        $date->modify('+1 day');
        $dateStr = $date->format('Y-m-d');

        $result = $this->modele->ajouterRecurrence(
            "Test Sujet_modif", "Description de test", $dateStr,8,null,2,"mois",null
        );

        $this->assertTrue($result['success']);

        $stmt = $this->pdo->query("SELECT id_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test Sujet_modif'");
        $idRecurrence = $stmt->fetchColumn(); // On récupère l'ID

        $result = $this->modele->update($idRecurrence,"Test_sujet_modifie","Description de test modifié",$dateStr,-5,null,4,"jour",null);
        $this->assertFalse($result['success']);
        $this->assertEquals("La fréquence doit être un nombre positif", $result['message']);
    }
    
    public function testModifierRecurrenceUniteFreqInexistent(): void {

        $sujet = 'Test';
        $description = 'Description test';
        $dateAnniv = (new DateTime('+1 day'))->format('Y-m-d');
        $frequence = 5; 
        $rappel = 1;
        $idLieu = 1;
        $uniteFrequence = 'jour';
        $uniteRappel = 'jour';

        $result = $this->modele->ajouterRecurrence(
            $sujet, $description, $dateAnniv, $frequence, $rappel, $idLieu, $uniteFrequence, $uniteRappel);

        $this->assertTrue($result['success']);

        $stmt = $this->pdo->query("SELECT id_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test Sujet_modif'");
        $idRecurrence = $stmt->fetchColumn(); // On récupère l'ID

        $sujet = 'Test_sujet_modifie';
        $description = 'Description de test modifié';
        $dateAnniv = (new DateTime('+2 day'))->format('Y-m-d');
        $frequence = 8; 
        $rappel = 3;
        $idLieu = 1;
        $uniteFrequence = null;
        $uniteRappel = 'jour';

        $result = $this->modele->update($idRecurrence,$sujet,$description, $dateAnniv, $frequence, $rappel, $idLieu, $uniteFrequence, $uniteRappel);
        $this->assertFalse($result['success']);
        $this->assertEquals("Unité de temps invalide.", $result['message']);
    }

    public function testModifierRecurrenceUniteDeInexistent(): void {

        $sujet = 'Test';
        $description = 'Description test';
        $dateAnniv = (new DateTime('+1 day'))->format('Y-m-d');
        $frequence = 5; 
        $rappel = 1;
        $idLieu = 1;
        $uniteFrequence = 'jour';
        $uniteRappel = 'jour';

        $result = $this->modele->ajouterRecurrence(
            $sujet, $description, $dateAnniv, $frequence, $rappel, $idLieu, $uniteFrequence, $uniteRappel);

        $this->assertTrue($result['success']);

        $stmt = $this->pdo->query("SELECT id_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test Sujet_modif'");
        $idRecurrence = $stmt->fetchColumn(); // On récupère l'ID

        $sujet = 'Test_sujet_modifie';
        $description = 'Description de test modifié';
        $dateAnniv = (new DateTime('+2 day'))->format('Y-m-d');
        $frequence = 8; 
        $rappel = 3;
        $idLieu = 1;
        $uniteFrequence = 'jour';
        $uniteRappel = null;

        $result = $this->modele->update($idRecurrence,$sujet,$description, $dateAnniv, $frequence, $rappel, $idLieu, $uniteFrequence, $uniteRappel);
        $this->assertFalse($result['success']);
        $this->assertEquals("Vous ne pouvez pas insérer une fréquence de rappel si vous n'avez pas sélectionné une unité de rappel", $result['message']);
    }

    
     public function testModifierRecurrenceUniteRappelEtDelaiPlusEleveQueFreq(): void {
        
        $sujet = 'Test';
        $description = 'Description test';
        $dateAnniv = (new DateTime('+1 day'))->format('Y-m-d');
        $frequence = 5; 
        $rappel = 1;
        $idLieu = 1;
        $uniteFrequence = 'jour';
        $uniteRappel = 'jour';

        $result = $this->modele->ajouterRecurrence(
            $sujet, $description, $dateAnniv, $frequence, $rappel, $idLieu, $uniteFrequence, $uniteRappel);

        $this->assertTrue($result['success']);

        $stmt = $this->pdo->query("SELECT id_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test Sujet_modif'");
        $idRecurrence = $stmt->fetchColumn(); // On récupère l'ID

        $sujet = 'Test_sujet_modifie';
        $description = 'Description de test modifié';
        $dateAnniv = (new DateTime('+2 day'))->format('Y-m-d');
        $frequence = 8; 
        $rappel = 9;
        $idLieu = 1;
        $uniteFrequence = 'jour';
        $uniteRappel = 'mois';

        $result = $this->modele->update($idRecurrence,$sujet,$description, $dateAnniv, $frequence, $rappel, $idLieu, $uniteFrequence, $uniteRappel);
        $this->assertFalse($result['success']);
        $this->assertEquals("Le délai de rappel et l'unité de rappel ne peuvent êtres supérieur à la fréquence de la maintenance.", $result['message']);
    }
    public function testModifierRecurrenceDateInvalide(): void {

        $sujet = 'Test';
        $description = 'Description test';
        $dateAnniv = (new DateTime('+1 day'))->format('Y-m-d');
        $frequence = 5; 
        $rappel = 1;
        $idLieu = 1;
        $uniteFrequence = 'jour';
        $uniteRappel = 'jour';

        $result = $this->modele->ajouterRecurrence(
            $sujet, $description, $dateAnniv, $frequence, $rappel, $idLieu, $uniteFrequence, $uniteRappel);

        $this->assertTrue($result['success']);

        $stmt = $this->pdo->query("SELECT id_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test Sujet_modif'");
        $idRecurrence = $stmt->fetchColumn(); // On récupère l'ID

        $sujet = 'Test_sujet_modifie';
        $description = 'Description de test modifié';
        $dateAnniv = (new DateTime('-5 day'))->format('Y-m-d'); // Valeur invalide
        $frequence = 8; 
        $rappel = 3;
        $idLieu = 1;
        $uniteFrequence = 'jour';
        $uniteRappel = 'jour';

        $result = $this->modele->update($idRecurrence,$sujet,$description, $dateAnniv, $frequence, $rappel, $idLieu, $uniteFrequence, $uniteRappel);
        $this->assertFalse($result['success']);
        $this->assertEquals("La date n'est pas valide", $result['message']);
    }
    public function testModifierRecurrenceRappelPlusGrandFrequen(): void {

        $sujet = 'Test';
        $description = 'Description test';
        $dateAnniv = (new DateTime('+1 day'))->format('Y-m-d');
        $frequence = 5; 
        $rappel = 1;
        $idLieu = 1;
        $uniteFrequence = 'jour';
        $uniteRappel = 'jour';

        $result = $this->modele->ajouterRecurrence(
            $sujet, $description, $dateAnniv, $frequence, $rappel, $idLieu, $uniteFrequence, $uniteRappel);

        $this->assertTrue($result['success']);

        $stmt = $this->pdo->query("SELECT id_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test Sujet_modif'");
        $idRecurrence = $stmt->fetchColumn(); // On récupère l'ID

        $sujet = 'Test_sujet_modifie';
        $description = 'Description de test modifié';
        $dateAnniv = (new DateTime('+2 day'))->format('Y-m-d');
        $frequence = 8; 
        $rappel = 9;
        $idLieu = 1;
        $uniteFrequence = 'jour';
        $uniteRappel = 'jour';

        $result = $this->modele->update($idRecurrence,$sujet,$description, $dateAnniv, $frequence, $rappel, $idLieu, $uniteFrequence, $uniteRappel);
        $this->assertFalse($result['success']);
        $this->assertEquals("Le délai de rappel ne peut être supérieur à la fréquence de la maintenance.", $result['message']);
    }
    public function testModifierRecurrenceDelaiRappelNegatif(): void {

        $sujet = 'Test';
        $description = 'Description test';
        $dateAnniv = (new DateTime('+1 day'))->format('Y-m-d');
        $frequence = 5;
        $rappel = 1;
        $idLieu = 1;
        $uniteFrequence = 'jour';
        $uniteRappel = 'jour';

        $result = $this->modele->ajouterRecurrence(
            $sujet, $description, $dateAnniv, $frequence, $rappel, $idLieu, $uniteFrequence, $uniteRappel);

        $this->assertTrue($result['success']);

        $stmt = $this->pdo->query("SELECT id_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test Sujet_modif'");
        $idRecurrence = $stmt->fetchColumn(); // On récupère l'ID

        $sujet = 'Test_sujet_modifie';
        $description = 'Description de test modifié';
        $dateAnniv = (new DateTime('+2 day'))->format('Y-m-d');
        $frequence = 8; 
        $rappel = -5; // Valeur invalide
        $idLieu = 1;
        $uniteFrequence = 'jour';
        $uniteRappel = null;

        $result = $this->modele->update($idRecurrence,$sujet,$description, $dateAnniv, $frequence, $rappel, $idLieu, $uniteFrequence, $uniteRappel);
        $this->assertFalse($result['success']);
        $this->assertEquals("Le délai de rappel doit être un nombre positif ou alors 0 si vous ne voulez pas de rappel.", $result['message']);
    }

    public function testSupprimerRecurrence(): void {
        $date = new DateTime();
        $date->modify('+1 day');
        $dateStr = $date->format('Y-m-d');

        $result = $this->modele->ajouterRecurrence(
            "Test Sujet supp", "Description de test", $dateStr,8,null,2,"mois",null
        );

        $this->assertTrue($result['success']);

        $stmt = $this->pdo->query("SELECT id_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test Sujet supp'");
        $idRecurrence = $stmt->fetchColumn(); // On récupère l'ID

        $result = $this->modele->delete($idRecurrence);

        $this->assertTrue($result['success']);
    }
    
    public function testSupprimerRecurrenceInexistente(): void {

        $stmt = $this->pdo->query("SELECT id_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test Sujet supp'");
        $idRecurrence = $stmt->fetchColumn(); // On récupère l'ID d'une maintenance qui n'existe pas

        $result = $this->modele->delete($idRecurrence);

        $this->assertfalse($result['success']);
        $this->assertEquals("ID de la récurrence est vide.", $result['message']);
    }

    
    public function testDatabaseUsed(): void {
        $stmt = $this->pdo->query("SELECT DATABASE()");
        $dbName = $stmt->fetchColumn();
        $this->assertEquals("albatros_test", $dbName);
    }

    public function testDatabasefalse(): void {
        $this->expectException(PDOException::class); 
        // Cette table n'existe pas, donc ça déclenche bien une exception
        $this->pdo->query("SELECT * FROM table_qui_pas");
    }

    public function testInvalidDatabaseConnection(): void {
        $this->expectException(PDOException::class);
    
        new PDO("mysql:host=localhost;dbname=bd_invalide", "root", ""); // mauvaise base
    }

}
?>