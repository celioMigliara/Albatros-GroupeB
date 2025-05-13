<?php

define('PHPUNIT_RUNNING', true);

if (!defined("BASE_URL")) {
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
}

require_once __DIR__ . '/../../../Model/B3/db_connect.php';
require_once __DIR__ . '/../../../Model/B3/Role.php';
require_once __DIR__ . '/../../../Controller/B3/TaskController.php';
require_once __DIR__ . '/../../../Test/B3/BaseTestClass.php';

class TaskControllerTest extends BaseTestClass
{
    private $taskController;


        /* ===================================================================================================== */
        /* ========================== PARTIE DES TESTS POUR RECUPERER LES TACHE DE UN TECHNICIEN =============== */
        /* ===================================================================================================== */

        /* ========================================================== */
        /* ========== TESTS UTILISATEUR NON CONNECTE ========== */
        /* ========================================================== */
        public function testGetTasksByTechnicianUtilisateurNonConnecte()
        {
            $taskController = new TaskController();
            $_SERVER['REQUEST_METHOD'] = 'GET';

            // Simuler un utilisateur non connecté
            $_SESSION = []; // Assurez-vous que l'utilisateur n'est pas connecté

            $taskController = new TaskController();

            // Essayer de récupérer les tâches pour un technicien sans être connecté
            $_GET['technicien_id'] = 1; // ID du technicien (peu importe ici)

            ob_start();
            $result = $taskController->getTasksForTechnician();
            $jsonOutput = ob_get_clean();
            $responseData = json_decode($jsonOutput, true);

            $this->assertFalse($result);
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString("Veuillez vous connecter en tant qu'admin pour voir les tâches.", $responseData['message']);
        }

        /* ========================================================== */
        /* ========== TESTS AUCUN TECHNICIEN ID SPECIFIE ========== */
        /* ========================================================== */
        public function testGetTasksByTechnicianAucunTechnicienId()
        {
            $taskController = new TaskController();

            $_SERVER['REQUEST_METHOD'] = 'GET';

            // Simuler un utilisateur connecté
            $_SESSION['user'] = [
                'id' => 1,
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'email' => 'jean@example.com',
                'role_id' => Role::ADMINISTRATEUR,
            ];

            $taskController = new TaskController();

            // Essayer de récupérer les tâches sans spécifier d'ID de technicien
            $_GET = []; // Aucun technicien_id dans la requête

            ob_start();
            $result = $taskController->getTasksForTechnician();
            $jsonOutput = ob_get_clean();
            $responseData = json_decode($jsonOutput, true);

            $this->assertFalse($result);
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString("ID technicien manquant", $responseData['message']);
        }

        /* ========================================================== */
        /* ========== TESTS TECHNICIEN INVALIDE ========== */
        /* ========================================================== */
        public function testGetTasksByTechnicianTechnicienInvalide()
        {
            $taskController = new TaskController();

            $this->viderToutesLesTables();
            $this->insererRoles();            
            $technicien = new UserCredentials('Technicien', 'Alice', 'alice@example.com', 'Pass1234', Role::TECHNICIEN);
            $technicien->setInscriptionValide(false);
            $technicien->setActif(false);
            $technicien->insertUser();

            $_SERVER['REQUEST_METHOD'] = 'GET';

            // Simuler un utilisateur connecté
            $_SESSION['user'] = [
                'id' => 1,
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'email' => 'jean@example.com',
                'role_id' => Role::ADMINISTRATEUR,
            ];
            $taskController = new TaskController();

            // Essaye de récupérer les tâches avec un ID de technicien invalide
            $_GET['technicien_id'] = 999; // ID inexistant

            ob_start();
            $result = $taskController->getTasksForTechnician();
            $jsonOutput = ob_get_clean();
            $responseData = json_decode($jsonOutput, true);

            $this->assertFalse($result);
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString("Technicien invalide ou inexistant.", $responseData['message']);
        }

        /* ========================================================== */
        /* ========== TESTS TACHES EXISTANTES POUR UN TECHNICIEN ========== */
        /* ========================================================== */
        public function testGetTasksByTechnicianTachesExistantesAvecDemande()
        {
            $taskController = new TaskController();

            $_SERVER['REQUEST_METHOD'] = 'GET';

            // Simuler un utilisateur connecté
            $_SESSION['user'] = [
                'id' => 1,
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'email' => 'jean@example.com',
                'role_id' => Role::ADMINISTRATEUR,
            ];
            // Insére un technicien avec des tâches associées
            $this->viderToutesLesTables();
            $this->insererRoles();            
            $technicien = new UserCredentials('Technicien', 'Alice', 'alice@example.com', 'Pass1234', Role::TECHNICIEN);
            $technicien->setInscriptionValide(true);
            $technicien->setActif(true);
            $technicien->insertUser();

            // Simuler l'ajout d'une demande pour ce technicien
            $technicienId = UserCredentials::getUserIdWithEmail('alice@example.com');
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare("INSERT INTO site (Id_site, nom_site, actif_site) VALUES (?, ?, ?)");
            $stmt->execute([1, 'Site Principal', 1]);

            // Ajouter un bâtiment lié au site
            $stmt = $db->prepare("INSERT INTO batiment (Id_batiment, nom_batiment, actif_batiment, Id_site) VALUES (?, ?, ?, ?)");
            $stmt->execute([1, 'Bâtiment A', 1, 1]);

            // Ajouter un lieu lié au bâtiment
            $stmt = $db->prepare("INSERT INTO lieu (Id_lieu, nom_lieu, actif_lieu, Id_batiment) VALUES (?, ?, ?, ?)");
            $stmt->execute([1, 'Salle de réunion', 1, 1]);

            // Créer une demande
            $stmt = $db->prepare("INSERT INTO demande 
            (num_ticket_dmd, sujet_dmd, description_dmd, date_creation_dmd, Id_utilisateur, Id_lieu) 
            VALUES (:num_ticket_dmd, :sujet_dmd, :description_dmd, NOW(), :Id_utilisateur, :Id_lieu)");

            $stmt->execute([
                'num_ticket_dmd' => '12345',
                'sujet_dmd' => 'Demande de maintenance',
                'description_dmd' => 'Description de la demande de maintenance.',
                'Id_utilisateur' => $technicienId,
                'Id_lieu' => 1
            ]);

            // Récupérer l'ID de la demande nouvellement insérée
            $idDemande = $db->lastInsertId();

            // Insérer des tâches liées à la demande
            $stmt = $db->prepare("INSERT INTO tache (sujet_tache, description_tache, date_creation_tache, date_planif_tache, date_fin_tache, Id_utilisateur, Id_demande)
                                  VALUES (:sujet_tache, :description_tache, NOW(), NOW(), NOW(), :Id_utilisateur, :Id_demande)");

            // Insérer deux tâches
            $stmt->execute([
                'sujet_tache' => 'Tâche 1',
                'description_tache' => 'Effectuer une maintenance de serveur.',
                'Id_utilisateur' => $technicienId,
                'Id_demande' => $idDemande
            ]);

            $stmt->execute([
                'sujet_tache' => 'Tâche 2',
                'description_tache' => 'Vérifier les performances du serveur.',
                'Id_utilisateur' => $technicienId,
                'Id_demande' => $idDemande
            ]);

            // Récupérer les tâches du technicien
            $_GET['technicien_id'] = $technicienId;

            $taskController = new TaskController();

            // Essayer de récupérer les tâches du technicien
            ob_start();
            $result = $taskController->getTasksForTechnician();
            $jsonOutput = ob_get_clean();
            $responseData = json_decode($jsonOutput, true);

            $this->assertTrue($result);
            $this->assertEquals('success', $responseData['status']);
            $this->assertCount(2, $responseData['tasks']); // Vérifie qu'il y a 2 tâches
            $this->assertStringContainsString('Tâche 1', $responseData['tasks'][0]['sujet_tache']);
            $this->assertStringContainsString('Tâche 2', $responseData['tasks'][1]['sujet_tache']);
        }


        /* ========================================================== */
        /* ========== TESTS METHOD NON AUTORISEE ========== */
        /* ========================================================== */
        public function testGetTasksByTechnicianNonSelectionne()
        {
            $taskController = new TaskController();

            // Simuler la méthode HTTP GET
            $_SERVER['REQUEST_METHOD'] = 'GET';
        
            // Simuler l'absence de l'ID technicien dans la requête
            $_GET['technicien_id'] = null; // Aucun technicien sélectionné
        
            // Simuler un utilisateur connecté avec un rôle admin
            $_SESSION['user'] = [
                'id' => 1,
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'email' => 'jean@example.com',
                'role_id' => Role::ADMINISTRATEUR,
            ];
        
            // Créer le contrôleur
            $taskController = new TaskController();

            // Essayer d'appeler la méthode avec une mauvaise méthode HTTP
            ob_start();
            $result = $taskController->getTasksForTechnician();
            $jsonOutput = ob_get_clean();
            $responseData = json_decode($jsonOutput, true);

            $this->assertFalse($result);
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString("ID technicien manquant", $responseData['message']);
        }



} 