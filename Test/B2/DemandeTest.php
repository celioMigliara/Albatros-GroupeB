<?php
define('PHPUNIT_RUNNING', true);
use PHPUnit\Framework\TestCase;


    require_once __DIR__ . '/../../Controller/B2/DemandeControllerB2.php';
    require_once __DIR__ . '/../../Model/B2/DemandeB2.php';
    require_once __DIR__ . '/../../Model/ModeleDBB2.php';

    
    class DemandeTest extends TestCase
    {
        private PDO $pdo;
        private DemandeModel $model;
       
        
        protected function setUp(): void
        {
            $this->pdo = Database::getInstance()->getConnection();
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->model = new DemandeModel($this->pdo);
        }
        
       
        // ---------------------
        // TESTS UNITAIRES (sans accès base de données)
        // ---------------------

        // Vérifie que tous les champs sont bien trim()
        public function testTrimEstBienApplique()
        {
            $d = new Demande([
                'sujet' => '   Sujet   ',
                'site' => '   Site ',
                'batiment' => ' B ',
                'lieu' => '  L ',
                'idUtilisateur' => '1'
            ]);

            $this->assertEquals('Sujet', $d->sujet);
            $this->assertEquals('Site', $d->site);
            $this->assertEquals('B', $d->batiment);
            $this->assertEquals('L', $d->lieu);
            $this->assertEquals(1, $d->idUtilisateur);
        }
        public function validerLongueurChamps(Demande $d): array {
            if (mb_strlen($d->sujet) > 50) {
                return ['success' => false, 'message' => "Sujet trop long"];
            }
            if (mb_strlen($d->description) > 512) {
                return ['success' => false, 'message' => "Description trop longue"];
            }
            return ['success' => true];
        }
        
        // Vérifie que si aucun utilisateur n'est fourni, l'id est 0 par défaut
        public function testDemandeSansUtilisateur()
        {
            $d = new Demande([
                'sujet' => 'Sujet',
                'site' => 'Site',
                'batiment' => 'Bat',
                'lieu' => 'Lieu',
            ]);
            $this->assertEquals(0, $d->idUtilisateur);
        }

        // Vérifie que si description n’est pas fournie, elle devient une chaîne vide
        public function testDescriptionAbsenteDevientVide()
        {
            $d = new Demande([
                'sujet' => 'Sujet',
                'site' => 'Site',
                'batiment' => 'Bat',
                'lieu' => 'Lieu',
                'idUtilisateur' => 1
            ]);
            $this->assertEquals('', $d->description);
        }

        // Vérifie que par défaut, il n’y a pas de pièce jointe
        public function testPieceJointeParDefautEstVide()
        {
            $d = new Demande([
                'sujet' => 'Sujet',
                'site' => 'Site',
                'batiment' => 'Bat',
                'lieu' => 'Lieu',
                'idUtilisateur' => 1
            ]);
        
            $this->assertIsArray($d->piecesJointes);
            $this->assertCount(0, $d->piecesJointes); // Teste que c'est un tableau vide
        }
        

        // Vérifie que estValide() retourne vrai quand tous les champs sont remplis
        public function testEstValideFonctionne()
        {
            $d = new Demande([
                'sujet' => 'S',
                'site' => 'A',
                'batiment' => 'B',
                'lieu' => 'C',
                'idUtilisateur' => 1
            ]);
            $this->assertTrue($d->estValide());
        }

        // Vérifie que estValide() retourne faux si un champ requis est manquant
        public function testEstValideRetourneFauxSiChampManquant()
        {
            $d = new Demande(['sujet' => '', 'site' => 'A', 'batiment' => '', 'lieu' => 'C']);
            $this->assertFalse($d->estValide());
        }

        // Vérifie que le numéro de ticket généré a le bon format
        public function testGenererNumeroTicketAvecReelObjet()
        {
            $model = new DemandeModel(); // utilisé sans PDO
            $id = 789;
            $ticket = $model->genererNumeroTicket($id);
            $this->assertEquals(date('Y') . '-' . $id, $ticket);
        }

        // Vérifie que les emojis et caractères accentués sont bien conservés
        public function testDescriptionAvecEmojiEtAccents()
        {
            $d = new Demande([
                'sujet' => 'Test spécial',
                'description' => 'Emoji 😎 avec éèê àç!',
                'site' => 'Site éèê',
                'batiment' => 'Bâtiment &',
                'lieu' => 'Salle <1>',
                'idUtilisateur' => 1
            ]);

            $this->assertEquals('Emoji 😎 avec éèê àç!', $d->description);
            $this->assertEquals('Site éèê', $d->site);
        }

        // ---------------------
        // TESTS D'INTÉGRATION (avec base de données)
        // ---------------------

        // Vérifie une insertion complète en base
        public function testAjoutDemandeValide()
        {
            $demande = new Demande([
                'sujet' => 'Test unitaire',
                'description' => 'Ceci est une description de test',
                'site' => '1',
                'batiment' => '1',
                'lieu' => '1',
                'idUtilisateur' => 1,
                'piecesJointes' => [[
                'nomOriginal' => 'test.pdf',
                'chemin' => '/Test/Uploads/test.pdf'
            ]]
            ]);

            $resultat = $this->model->ajouterDemande($demande);
            $this->assertIsString($resultat);
            $this->assertMatchesRegularExpression('/^\d{4}-\d+$/', $resultat);
        }

        // Teste un sujet trop long (plus de 50 caractères)
        public function testAjoutDemandeAvecSujetTropLong()
        {
            $demande = new Demande([
                'sujet' => str_repeat('a', 51),
                'description' => 'Test',
                'site' => 'TestSite',
                'batiment' => 'BatA',
                'lieu' => 'Salle101',
                'idUtilisateur' => 1
            ]);
            $resultat = $this->model->ajouterDemande($demande);
            $this->assertIsArray($resultat);
            $this->assertFalse($resultat['success']);
        }

        // Teste une description trop longue (plus de 512 caractères)
        public function testAjoutDemandeAvecDescriptionTropLongue()
        {
            $demande = new Demande([
                'sujet' => 'Test long desc',
                'description' => str_repeat('A', 513),
                'site' => '1',
                'batiment' => '1',
                'lieu' => '1',
                'idUtilisateur' => 1
            ]);

            $resultat = $this->model->ajouterDemande($demande);
            $this->assertFalse($resultat['success']);
            $this->assertStringContainsString("description est trop longue", $resultat['message']);
        }

        // Vérifie qu'on peut insérer une demande sans pièce jointe
        public function testAjoutDemandeSansPieceJointe()
        {
            $demande = new Demande([
                'sujet' => 'Test sans fichier',
                'description' => 'Une demande valide sans pièce jointe',
                'site' => '1',
                'batiment' => '1',
                'lieu' => '1',
                'idUtilisateur' => 1
            ]);

            $resultat = $this->model->ajouterDemande($demande);
            $this->assertIsString($resultat);
        }

        // Vérifie qu'une insertion échoue avec un lieu invalide
        public function testLieuInvalide()
        {
            $demande = new Demande([
                'sujet' => 'Lieu invalide',
                'description' => 'Desc',
                'site' => 'Inconnu',
                'batiment' => 'Inconnu',
                'lieu' => 'Inconnu',
                'idUtilisateur' => 1
            ]);

            $resultat = $this->model->ajouterDemande($demande);
            $this->assertFalse($resultat['success']);
            $this->assertStringContainsString('Lieu invalide', $resultat['message']);
        }

        // Vérifie que obtenirIdEndroit retourne un ID correct pour un trio valide
        public function testObtenirIdEndroitValide()
        {
            $id = $this->model->obteniriIdEndroit('1', '1', '1');
            $this->assertEquals(1, $id);
        }

        // Vérifie que obtenirIdEndroit retourne null si le lieu n'existe pas
        public function testObtenirIdEndroitInvalide()
        {
            $id = $this->model->obteniriIdEndroit('X', 'Y', 'Z');
            $this->assertNull($id);
        }

        // Vérifie l'insertion d'un nom de fichier contenant des caractères spéciaux
        public function testNomFichierAvecCaracteresSpeciaux()
        {
            $demande = new Demande([
                'sujet' => 'Nom spécial',
                'description' => 'Fichier .jpg avec éèà!',
                'site' => '1',
                'batiment' => '1',
                'lieu' => '1',
                'idUtilisateur' => 1,
                'piecesJointes' => [[
                    'nomOriginal' => 'éèà test.pdf',
                    'chemin' => '/Test/Uploads/éèà.pdf'
                ]]
            ]);

            $resultat = $this->model->ajouterDemande($demande);
            $this->assertIsString($resultat);
        }

        // Vérifie que l'entrée a bien été ajoutée dans la table `media`
        public function testInsertionDansTableMedia()
        {
            $demande = new Demande([
                'sujet' => 'Test media',
                'description' => 'Vérification media',
                'site' => '1',
                'batiment' => '1',
                'lieu' => '1',
                'idUtilisateur' => 1,
                'piecesJointes' => [[
                    'nomOriginal' => 'test.pdf',
                    'chemin' => '/Test/Uploads/test.pdf'
                ]]
            ]);
        
            $ticket = $this->model->ajouterDemande($demande);
            $idDemande = explode('-', $ticket)[1];
        
            $stmt = $this->pdo->prepare("SELECT * FROM media WHERE id_demande = ?");
            $stmt->execute([$idDemande]);
            $media = $stmt->fetch();
        
            $this->assertEquals('test.pdf', $media['nom_media']);
        }
        

        // Vérifie que le ticket inséré existe bien en base
        public function testTicketInsereDansDemande()
        {
            $demande = new Demande([
                'sujet' => 'Ticket base',
                'description' => 'Ticket test',
                'site' => '1',
                'batiment' => '1',
                'lieu' => '1',
                'idUtilisateur' => 1
            ]);

            $ticket = $this->model->ajouterDemande($demande);

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM demande WHERE num_ticket_dmd = ?");
            $stmt->execute([$ticket]);
            $count = $stmt->fetchColumn();

            $this->assertGreaterThan(0, $count);
        }

        // Vérifie que le statut "Nouvelle" est bien lié à la demande
        public function testStatutEstLieADemande()
        {
            $demande = new Demande([
                'sujet' => 'Statut test',
                'description' => 'Insertion + statut',
                'site' => '1',
                'batiment' => '1',
                'lieu' => '1',
                'idUtilisateur' => 1
            ]);

            $ticket = $this->model->ajouterDemande($demande);
            $idDemande = explode('-', $ticket)[1];

            $stmt = $this->pdo->prepare("SELECT id_statut FROM est WHERE id_demande = ?");
            $stmt->execute([$idDemande]);
            $idStatut = $stmt->fetchColumn();

            $stmt = $this->pdo->prepare("SELECT id_statut FROM statut WHERE nom_statut = 'Nouvelle'");
            $stmt->execute();
            $idAttendu = $stmt->fetchColumn();

            $this->assertEquals($idAttendu, $idStatut);
        }

        // Vérifie que deux demandes très proches ne génèrent pas le même ticket
        public function testEnvoiSimultaneDeDemandes()
        {
            $d1 = new Demande([
                'sujet' => 'Simultané 1',
                'description' => 'Test',
                'site' => '2',
                'batiment' => '1',
                'lieu' => '1',
                'idUtilisateur' => 1
            ]);

            $d2 = new Demande([
                'sujet' => 'Simultané 2',
                'description' => 'Test',
                'site' => '1',
                'batiment' => '1',
                'lieu' => '1',
                'idUtilisateur' => 1
            ]);

            $t1 = $this->model->ajouterDemande($d1);
            $t2 = $this->model->ajouterDemande($d2);

            $this->assertNotEquals($t1, $t2);
        }

        // Vérifie qu'aucune entrée n’est faite dans `media` si pas de fichier
        public function testAucuneInsertionMediaSiAucunePiece()
        {
            $demande = new Demande([
                'sujet' => 'Pas de media',
                'description' => 'Test',
                'site' => '1',
                'batiment' => '1',
                'lieu' => '1',
                'idUtilisateur' => 1,
                    'piecesJointes' => [] // explicite

            ]);

            $ticket = $this->model->ajouterDemande($demande);
            $idDemande = explode('-', $ticket)[1];

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM media WHERE id_demande = ?");
            $stmt->execute([$idDemande]);
            $count = $stmt->fetchColumn();

            $this->assertEquals(0, $count);
        }

        // Vérifie qu'un nom de fichier très long est accepté si la base le permet
        public function testNomFichierTropLong()
        {
            $nomLong = str_repeat('a', 255) . '.pdf';

            $demande = new Demande([
                'sujet' => 'Nom long',
                'description' => 'Test fichier avec nom trop long',
                'site' => '1',
                'batiment' => '1',
                'lieu' => '1',
                'idUtilisateur' => 1,
                'piecesJointes' => [[
                    'nomOriginal' => $nomLong,
                    'chemin' => '/Test/Uploads/' . $nomLong
                ]]
            ]);

            $resultat = $this->model->ajouterDemande($demande);
            $this->assertIsString($resultat);
        }

        // Vérifie que plusieurs fichiers sont bien insérés dans la table media
        public function testInsertionPlusieursFichiersMedia()
    {
        $demande = new Demande([
            'sujet' => 'Plusieurs fichiers',
            'description' => 'Test multiple',
            'site' => '1',
            'batiment' => '1',
            'lieu' => '1',
            'idUtilisateur' => 1,
            'piecesJointes' => [
                ['nomOriginal' => 'fichier1.pdf', 'chemin' => '/Test/Uploads/fichier1.pdf'],
                ['nomOriginal' => 'fichier2.pdf', 'chemin' => '/Test/Uploads/fichier2.pdf']
            ]
        ]);

        $ticket = $this->model->ajouterDemande($demande);
        $idDemande = explode('-', $ticket)[1];

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM media WHERE id_demande = ?");
        $stmt->execute([$idDemande]);
        $count = $stmt->fetchColumn();

        $this->assertEquals(2, $count);
    }

    
    // Vérifie que l’ajout échoue si le statut "Nouvelle" est absent
    public function testErreurSiStatutNouvelleAbsent()
{
    // Supprime le statut "Nouvelle" de la base temporairement
    $stmt = $this->pdo->prepare("SELECT id_statut FROM statut WHERE nom_statut = 'Nouvelle'");
    $stmt->execute();
    $idStatut = $stmt->fetchColumn();

    if ($idStatut) {
        $this->pdo->prepare("DELETE FROM est WHERE id_statut = ?")->execute([$idStatut]);
        $this->pdo->prepare("DELETE FROM statut WHERE id_statut = ?")->execute([$idStatut]);
    }

    // Crée une demande normale
    $demande = new Demande([
        'sujet' => 'Erreur statut',
        'description' => 'Doit échouer',
        'site' => '1',
        'batiment' => '1',
        'lieu' => '1',
        'idUtilisateur' => 1
    ]);

    // Appelle la méthode
    $res = $this->model->ajouterDemande($demande);

    // Vérifie que l'erreur est bien gérée
    $this->assertIsArray($res);
    $this->assertFalse($res['success']);
    $this->assertStringContainsString("Erreur BDD", $res['message']);
    $this->assertStringContainsString("statut", $res['message']);

    // Réinsère le statut pour les tests suivants
    $this->pdo->prepare("INSERT INTO statut (nom_statut) VALUES ('Nouvelle')")->execute();
}
// Vérifie que l’upload retourne un tableau vide s’il n’y a aucun fichier
public function testGererUploadSansFichier()
{
    $files = ['piece_jointe' => ['name' => ['']]];
    $res =gererUpload($files);
    $this->assertFalse($res['erreur']);
    $this->assertIsArray($res['filess']);
    $this->assertCount(0, $res['filess']);
}


}