<?php

define('PHPUNIT_RUNNING', true);

if (!defined("BASE_URL")) {
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
}

require_once __DIR__ . '/../../../Model/B3/db_connect.php';
require_once __DIR__ . '/../../../Model/B3/Role.php';
require_once __DIR__ . '/../../../Controller/B3/TechnicienController.php';
require_once __DIR__ . '/../../../Test/B3/BaseTestClass.php';

class TechnicienControllerTest extends BaseTestClass
{
    private $technicienController;


    /* ========================================================== */
    /* ========== TESTS LISTE TÂCHES TECHNICIEN ================ */
    /* ========================================================== */
    public function testListeTachesNonConnecte()
    {
        // Nettoyer complètement la session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        session_start();
        $_SESSION = array();
        
        // Réinitialiser les variables globales
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = array();
        $_POST = array();
        
        // Vider la base de données
        $this->viderToutesLesTables();

        // Créer une nouvelle instance du contrôleur
        $technicienController = new TechnicienController();

        // Capturer la sortie
        ob_start();
        $result = $technicienController->getTechniciens();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        // Vérifications
        $this->assertFalse($result, "Le résultat devrait être false pour un utilisateur non connecté");
        $this->assertEquals('error', $responseData['status'], "Le statut devrait être 'error'");
        $this->assertStringContainsString(
            "Veuillez vous connecter en tant qu'admin pour voir les techniciens.",
            $responseData['message'],
            "Le message d'erreur devrait indiquer qu'une connexion admin est nécessaire"
        );

        // Nettoyer après le test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }

   
        /* ========================================================== */
        /* ============ TESTS AUCUN TECHNICIEN TROUVE =============== */
        /* ========================================================== */
        public function testGetTechniciensAucunTrouve()
        {
            $technicienController = new TechnicienController();

            $_SERVER['REQUEST_METHOD'] = 'GET';

            // Simuler un utilisateur connecté
            $_SESSION['user'] = [
                'id' => 1,
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'email' => 'jean@example.com',
                'role_id' => Role::ADMINISTRATEUR,
            ];
            
            // Vider la table des techniciens pour tester l'absence de techniciens
            $this->viderToutesLesTables();


            // Essayer de récupérer les techniciens alors qu'il n'y en a pas
            ob_start();
            $result = $technicienController->getTechniciens();
            $jsonOutput = ob_get_clean();
            $responseData = json_decode($jsonOutput, true);

            $this->assertFalse($result);
            $this->assertEquals('warning', $responseData['status']);
            $this->assertStringContainsString("Aucun technicien trouvé.", $responseData['message']);
        }

        /* ================================================================= */
        /* ========== TESTS RECUPERATION DE PLUSIEURS TECHNICIENS ========== */
        /* ================================================================= */
        public function testGetTechniciensPlusieursTrouves()
        {
            $technicienController = new TechnicienController();

            $_SERVER['REQUEST_METHOD'] = 'GET';

            // Simuler un utilisateur connecté
            $_SESSION['user'] = [
                'id' => 1,
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'email' => 'jean@example.com',
                'role_id' => Role::ADMINISTRATEUR
            ];
            
            // Inscrire plusieurs techniciens pour tester la récupération
            $this->viderToutesLesTables(); // S'assurer que la table est vide avant l'insertion
            $this->insererRoles();

            $user1 = new UserCredentials('Technicien', 'Alice', 'alice@example.com', 'Pass1234', Role::TECHNICIEN);
            $user1->setInscriptionValide(true);
            $user1->setActif(true);
            $user1->insertUser();

            $user2 = new UserCredentials('Technicien', 'Bob', 'bob@example.com', 'Pass1234', Role::TECHNICIEN);
            $user2->setInscriptionValide(true);
            $user2->setActif(true);
            $user2->insertUser();

            $technicienController = new TechnicienController();

            // Essayer de récupérer les techniciens
            ob_start();
            $result = $technicienController->getTechniciens();
            $jsonOutput = ob_get_clean();
            $responseData = json_decode($jsonOutput, true);

            $this->assertTrue($result);
            $this->assertEquals('success', $responseData['status']);
            $this->assertCount(2, $responseData['technicians']); // Vérifie que 2 techniciens sont renvoyés
            $this->assertStringContainsString('Technicien', $responseData['technicians'][0]['nom_utilisateur']);
            $this->assertStringContainsString('Technicien', $responseData['technicians'][1]['nom_utilisateur']);
        }

        /* ========================================================== */
        /* =============== TESTS METHOD NON AUTORISEE =============== */
        /* ========================================================== */
        public function testGetTechniciensMethodNonAutorisee()
        {
            $technicienController = new TechnicienController();            
            $_SERVER['REQUEST_METHOD'] = 'POST'; // Utilisation de la méthode POST à la place de GET


            // Essayer d'appeler la méthode avec une mauvaise méthode HTTP
            ob_start();
            $result = $technicienController->getTechniciens();
            $jsonOutput = ob_get_clean();
            $responseData = json_decode($jsonOutput, true);

            $this->assertFalse($result);
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString("Méthode non autorisée", $responseData['message']);
        }



} 