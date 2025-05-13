<?php

define('PHPUNIT_RUNNING', true);

if (!defined("BASE_URL"))
{
    // Définir BASE_URL si pas encore set
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
}
    

require_once __DIR__ . '/../../../Model/B3/db_connect.php';
require_once __DIR__ . '/../../../Model/B3/Role.php';
require_once __DIR__ . '/../../../Controller/B3/AuthController.php';
require_once __DIR__ . '/../../../Test/B3/BaseTestClass.php';
require_once __DIR__ . '/../../../Model/UserConnectionUtils.php';

class AuthControllerTest extends BaseTestClass
{
    private $authController;


    /* ========================================================== */
    /* ========== TESTS CONNEXION FORMULAIRE INCOMPLET ========== */
    /* ========================================================== */
    public function testConnexionFormulaireIncomplet()
    {
        $this->viderToutesLesTables();
        $this->insererRoles();            
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass1234', Role::UTILISATEUR);
        $user->setInscriptionValide(true);
        $user->setActif(true);
        $user->insertUser();
        $authController = new AuthController();

        // Simuler une soumission POST avec des champs vides
        $_SERVER['REQUEST_METHOD'] = 'POST';        
            
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        $_POST = [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => '', // Email manquant
            'mdp_utilisateur' => '',  // Mot de passe manquant
        ];

        ob_start();
        $result = $authController->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
        $this->assertStringContainsString("Formulaire incomplet", $responseData['message']);
    }

    /* =============================================================== */
    /* =========== TESTS CONNEXION AVEC EMAIL INVALIDE =============== */
    /* =============================================================== */
    public function testConnexionEmailInvalide()
    {
        $this->viderToutesLesTables();
        $this->insererRoles();            
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass1234', Role::UTILISATEUR);
        $user->setInscriptionValide(true);
        $user->setActif(true);
        $user->insertUser();
        $authController = new AuthController();

        // Simuler une requête POST avec un email incorrect
        $_SERVER['REQUEST_METHOD'] = 'POST';


        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        // Formulaire incomplet (prénom manquant)
        $_POST = [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => 'email@invalide.com',
            'mdp_utilisateur' => 'Pass1234',
        ];

        ob_start();
        $result = $authController->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
        $this->assertStringContainsString("Veuillez vérifier vos informations de connexion", $responseData['message']);
    }

    /* ====================================================================== */
    /* =========== TESTS CONNEXION AVEC MOT DE PASSE INVALIDE =============== */
    /* ====================================================================== */
    public function testConnexionMotDePasseInvalide()
    {
        $this->viderToutesLesTables();
        $this->insererRoles();            
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass1234', Role::UTILISATEUR);
        $user->setInscriptionValide(true);
        $user->setActif(true);
        $user->insertUser();
        $authController = new AuthController();
        // Simuler une requête POST avec un mot de passe incorrect
        $_SERVER['REQUEST_METHOD'] = 'POST';


        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        // Formulaire incomplet (prénom manquant)
        $_POST = [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => 'jean@example.com',
            'mdp_utilisateur' => 'MotDePasseIncorrect',
        ];

        ob_start();
        $result = $authController->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
        $this->assertStringContainsString("Veuillez vérifier vos informations de connexion", $responseData['message']);
    }

    /* ============================================================================== */
    /* =========== TESTS CONNEXION AVEC EMAIL ET MOT DE PASSE VALIDES =============== */
    /* ============================================================================== */
    public function testConnexionValide()
    {
        // Crée un utilisateur avec un email et un mot de passe valides
        $this->viderToutesLesTables();

        $this->insererRoles();            
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass1234', Role::UTILISATEUR);
        $user->setInscriptionValide(true);
        $user->setActif(true);
        $user->insertUser();
        $authController = new AuthController();

        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Supposons que 'jean@example.com' et 'Pass1234' sont valides dans la base de données

        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        // Formulaire incomplet (prénom manquant)
        $_POST = [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => 'jean@example.com',
            'mdp_utilisateur' => 'Pass1234',
        ];

        ob_start();
        $result = $authController->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertTrue($result);
        $this->assertEquals('success', $responseData['status']);
        $this->assertStringContainsString('Vous êtes connectés', $responseData['message']);
    }

    /* ======================================================================= */
    /* =========== TESTS CONNEXION AVEC UTILISATEUR NON TROUVÉ =============== */
    /* ======================================================================= */
    public function testConnexionUtilisateurNonTrouve()
    {
        $this->viderToutesLesTables();
        $this->insererRoles();            
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass1234', Role::UTILISATEUR);
        $user->setInscriptionValide(true);
        $user->setActif(true);
        $user->insertUser();
        $authControler = new AuthController();
        // Simuler une requête POST avec des informations incorrectes
        $_SERVER['REQUEST_METHOD'] = 'POST';


        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        // Formulaire incomplet (prénom manquant)
        $_POST = [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => 'nonexistent@example.com',
            'mdp_utilisateur' => 'MotDePasseIncorrect',
        ];

        ob_start();
        $result = $authControler->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
        $this->assertStringContainsString("Veuillez vérifier vos informations de connexion", $responseData['message']);
    }

            /* ============================================================================= */
        /* =========== TESTS CONNEXION AVEC DONNÉES UTILISATEUR ERRONÉES =============== */
        /* ============================================================================== */

    public function testConnexionUtilisateurErrone()
    {
        $this->viderToutesLesTables();
        $this->insererRoles();            
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass1234', Role::UTILISATEUR);
        $user->setInscriptionValide(true);
        $user->setActif(true);
        $user->insertUser();
        $authController = new AuthController();
        // Simuler une soumission POST avec des informations erronées
        $_SERVER['REQUEST_METHOD'] = 'POST';


        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        // Formulaire incomplet (prénom manquant)
        $_POST = [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => 'wrong@example.com',
            'mdp_utilisateur' => 'wrongpassword',
        ];

        ob_start();
        $result = $authController->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
        $this->assertStringContainsString("Veuillez vérifier vos informations de connexion", $responseData['message']);
    }

    /* =============================================================== */
    /* =========== TESTS CONNEXION AVEC SESSION ACTIVE =============== */
    /* =============================================================== */
    public function testConnexionSessionActive()
    {
        // Simuler une connexion avec une session déjà active
        $this->viderToutesLesTables();
        $this->insererRoles();            
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass1234', Role::UTILISATEUR);
        $user->setInscriptionValide(true);
        $user->setActif(true);
        $user->insertUser();
        $authController = new AuthController();
        $_SERVER['REQUEST_METHOD'] = 'POST';

        session_start();
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        // Formulaire incomplet (prénom manquant)
        $_POST = [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => 'jean@example.com',
            'mdp_utilisateur' => 'Pass1234',
        ];

        $_SESSION['user'] = [
            'id' => 1,
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'jean@example.com',
            'role_id' => 2
        ];

        ob_start();
        $result = $authController->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
        $this->assertStringContainsString("Vous êtes déjà connecté", $responseData['message']);
    }

    /* ========================================================== */
    /* ========== TESTS TOKEN CSRF ============================== */
    /* ========================================================== */
    public function testCsrfTokenManquant()
    {
        $authController = new AuthController();

        // Simuler une requête POST sans le token CSRF
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $csrfToken = bin2hex(random_bytes(32));  // CSRF token simulé
        $_SESSION['csrf_token'] = $csrfToken;

        // Formulaire avec un token manquant
        $_POST = [
            'nom_utilisateur' => 'Dupont',
            'prenom_utilisateur' => 'Jean',
            'mail_utilisateur' => 'jean@example.com',
            'mdp_utilisateur' => 'Pass1234',
            'role_utilisateur' => Role::TECHNICIEN,
        ];

        ob_start();
        $result = $authController->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
        $this->assertStringContainsString("Token CSRF invalide", $responseData['message']);
    }

    public function testCsrfTokenInvalide()
    {
        $authController = new AuthController();

        // Simuler une requête POST avec un token CSRF invalide
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $csrfToken = bin2hex(random_bytes(32)); // CSRF token simulé
        $_SESSION['csrf_token'] = $csrfToken;

        // Formulaire avec un token CSRF incorrect
        $_POST = [
            'csrf_token' => 'invalid_token', // Token invalide
            'nom_utilisateur' => 'Dupont',
            'prenom_utilisateur' => 'Jean',
            'mail_utilisateur' => 'jean@example.com',
            'mdp_utilisateur' => 'Pass1234',
            'role_utilisateur' => Role::TECHNICIEN,
        ];

        ob_start();
        $result = $authController->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
        $this->assertStringContainsString("Token CSRF invalide", $responseData['message']);
    }

    public function testCsrfTokenValide()
    {
        $this->viderToutesLesTables();
        $this->insererRoles();            
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass1234', Role::UTILISATEUR);
        $user->setInscriptionValide(true);
        $user->setActif(true);
        $user->insertUser();
        $this->authController = new AuthController();

        // Initialiser la session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = []; // Nettoyer la session

        // Simuler une requête POST avec un token CSRF valide
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        // Formulaire de connexion avec un token CSRF valide
        $_POST = [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => 'jean@example.com',
            'mdp_utilisateur' => 'Pass1234'
        ];

        ob_start();
        $result = $this->authController->login();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        // Assertions
        $this->assertTrue($result);
        $this->assertEquals('success', $responseData['status']);
        $this->assertStringContainsString('Vous êtes connectés', $responseData['message']);
        
        // Nettoyer la session après le test
        session_destroy();
    }

    /* ========================================================== */
    /* ========== TESTS INSCRIPTION ============================ */
    /* ========================================================== */


        /* ================================================ */
        /* ========== TESTS FORMULAIRE INCOMPLET ========== */
        /* ================================================ */
        public function testFormulaireIncomplet()
        {
            $authController = new AuthController();

            // Simuler une soumission POST
            $_SERVER['REQUEST_METHOD'] = 'POST';

            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Dupont',
                'prenom_utilisateur' => '',  // Champ requis manquant
                'mail_utilisateur' => 'jean@example.com',
                'confirmer_mail' => 'jean@example.com',
                'mdp_utilisateur' => 'Pass1234',
                'confirmer_mots_de_passe' => 'Pass1234',
                'role_utilisateur' => Role::ADMINISTRATEUR,
            ];

            // Activer la temporisation de sortie pour éviter les erreurs headers
            ob_start();
            $result = $authController->register();
            $jsonOutput = ob_get_clean();

            // Décoder le JSON
            $responseData = json_decode($jsonOutput, true);

            // Vérifier que la méthode retourne false pour un formulaire incomplet
            // Vérifications
            $this->assertFalse($result); // La méthode doit retourner false
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString('Rôle invalide', $responseData['message']);
        }
    public function testInscriptionEmailDejaUtilise()
    {
        $this->viderToutesLesTables();
        $this->insererRoles(); 

        $user = new UserCredentials('Dupontf', 'Jeanf', 'jean@example.com', 'Pasds1234', Role::TECHNICIEN);
        $user->setInscriptionValide(true);
        $user->setActif(true);
        $user->insertUser();

        $db = Database::getInstance()->getConnection();

        $db->exec("INSERT INTO `site` (`Id_site`, `nom_site`, `actif_site`) 
        VALUES 
        (1, 'SITE1', '1')");

        // Insérer des bâtiments dans la table `batiment`
        $db->exec("INSERT INTO `batiment` (`Id_batiment`, `nom_batiment`, `actif_batiment`, `Id_site`) 
                   VALUES 
                   (1, 'LAREDOUTE', '1', '1'), 
                   (2, 'ESPIEGLERIE', '1', '1'), 
                   (3, 'LASOURCES', '1', '1')");

        $authController = new AuthController();

        // Simuler une requête POST
        $_SERVER['REQUEST_METHOD'] = 'POST';


        // Simulate a POST request with the same email



        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        // Formulaire incomplet (prénom manquant)
        $_POST = [
            'csrf_token' => $csrfToken,
            'nom_utilisateur' => 'Dupont',
            'prenom_utilisateur' => 'Jean',
            'mail_utilisateur' => 'jean@example.com',
            'mdp_utilisateur' => 'Pass1234',
            'confirmer_mail' => 'jean@example.com',
            'confirmer_mots_de_passe' => 'Pass1234',
            'role_utilisateur' => Role::TECHNICIEN,
        ];

        // Capture the JSON output
        ob_start();
        $result = $authController->register();
        $jsonOutput = ob_get_clean();

        // Decode the JSON
        $responseData = json_decode($jsonOutput, true);

        // Assertions
        $this->assertFalse($result); // The method should return false
        $this->assertEquals('error', $responseData['status']);
        $this->assertStringContainsString('L\'adresse email est déjà utilisée. Veuillez changer d\'adresse email pour votre inscription', $responseData['message']);
    }

    public function testInscriptionRoleInvalide()
    {
        $authController = new AuthController();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;

        $_POST = [
            'csrf_token' => $csrfToken,
            'nom' => 'Martin',
            'prenom' => 'Pierre',
            'mail_utilisateur' => 'pierre@example.com',
            'confirmer_mail' => 'pierre@example.com',
            'mdp_utilisateur' => 'Pass1234',
            'confirmer_mots_de_passe' => 'Pass1234',
            'role' => 999 // Role invalide
        ];

        ob_start();
        $result = $authController->register();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $responseData['status']);
        $this->assertStringContainsString("Rôle invalide", $responseData['message']);
    }


        /* =============================================================================== */
        /* ============ TESTS INSCRIPTION UTILISATEUR AVEC BATIMENT DONC VALIDE ========== */
        /* =============================================================================== */
        public function testInscriptionValide()
        {
            $this->viderToutesLesTables();
            $this->insererRoles();            
            $authController = new AuthController();

            $db = Database::getInstance()->getConnection();

            $db->exec("INSERT INTO `site` (`Id_site`, `nom_site`, `actif_site`) 
            VALUES 
            (1, 'SITE1', '1')");

            // Insérer des bâtiments dans la table `batiment`
            $db->exec("INSERT INTO `batiment` (`Id_batiment`, `nom_batiment`, `actif_batiment`, `Id_site`) 
                       VALUES 
                       (1, 'LAREDOUTE', '1', '1'), 
                       (2, 'ESPIEGLERIE', '1', '1'), 
                       (3, 'LASOURCES', '1', '1')");

            // Simuler une soumission POST pour un technicien valide
            $_SERVER['REQUEST_METHOD'] = 'POST';


            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Dupont',
                'prenom_utilisateur' => 'Jean',
                'mail_utilisateur' => 'jeanvalide@example.com',
                'confirmer_mail' => 'jeanvalide@example.com', // Valeur identique pour que la correspondance soit ok
                'mdp_utilisateur' => 'Pass1234',
                'confirmer_mots_de_passe' => 'Pass1234', // Valeur identique pour que la correspondance soit ok
                'role_utilisateur' => Role::UTILISATEUR,
                'batiments_utilisateur' => ['1', '2'], // Sélection de bâtiments valide
            ];

            // Capture the JSON output
            ob_start();
            $result = $authController
->register();
            $jsonOutput = ob_get_clean();

            // Décoder la réponse JSON
            $responseData = json_decode($jsonOutput, true);

            // Vérifications
            $this->assertTrue($result); // La méthode doit retourner true
            $this->assertEquals('success', $responseData['status']);
            $this->assertStringContainsString('Votre demande d\'inscription a bien été envoyée', $responseData['message']);
        }

                /* ============================================== */
        /* =========== TESTS RÔLE INVALIDE =============== */
        /* ============================================== */
        public function testRoleInvalide()
        {
            $this->viderToutesLesTables();
        $authController = new AuthController();
            $this->insererRoles(); 
            
            // Simuler une requête POST
            $_SERVER['REQUEST_METHOD'] = 'POST';

            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Dupont',
                'prenom_utilisateur' => 'Jean',
                'mail_utilisateur' => 'jean@example.com',
                'mdp_utilisateur' => 'Pass1234',
                'confirmer_mail' => 'jean@example.com',
                'confirmer_mots_de_passe' => 'Pass1234',
                'role_utilisateur' => 999 // Utiliser un entier comme les rôles
            ];

            // Capturer la sortie JSON
            ob_start();
            $result = $authController
->register();
            $jsonOutput = ob_get_clean();

            // Décoder le JSON
            $responseData = json_decode($jsonOutput, true);


            // Vérifications
            $this->assertFalse($result); // La méthode doit retourner false
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString('Rôle invalide', $responseData['message']);
        }
        public function testRoleInvalideAvecEmailDejaUtilise()
        {
            $this->viderToutesLesTables();
            $this->insererRoles(); 
            // Crée un utilisateur avec un email valide
            $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass1234', Role::UTILISATEUR);
            $user->insertUser();

        $authController = new AuthController();

            // Simuler une requête POST avec un rôle invalide
            $_SERVER['REQUEST_METHOD'] = 'POST';

            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Dupont',
                'prenom_utilisateur' => 'Jean',
                'mail_utilisateur' => 'jean@example.com', // Email déjà utilisé
                'mdp_utilisateur' => 'Pass1234',
                'confirmer_mail' => 'jean@example.com',
                'confirmer_mots_de_passe' => 'Pass1234',
                'role_utilisateur' => 999,  // Rôle invalide
            ];

            // Capture the JSON output
            ob_start();
            $result = $authController
->register();
            $jsonOutput = ob_get_clean();

            // Decode the JSON
            $responseData = json_decode($jsonOutput, true);

            // Assertions
            $this->assertFalse($result); // The method should return false
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString('Rôle invalide', $responseData['message']);
        }

        /* ============================================== */
        /* ============ TESTS RÔLE INVALIDE AVEC BATIMENTS ========== */
        /* ============================================== */
        public function testRoleAvecBatimentsIncoherents()
        {
            $this->viderToutesLesTables();
        $authController = new AuthController();

            // Simuler une soumission POST pour un utilisateur qui a un rôle invalide avec des bâtiments sélectionnés
            $_SERVER['REQUEST_METHOD'] = 'POST';

            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Dupont',
                'prenom_utilisateur' => 'Jean',
                'mail_utilisateur' => 'jean@example.com',
                'mdp_utilisateur' => 'Pass1234',
                'confirmer_mail' => 'jean@example.com',
                'confirmer_mots_de_passe' => 'Pass1234',
                'role_utilisateur' => Role::UTILISATEUR,
                'batiments_utilisateur' => ['batiment1', 'batiment2'],  // Un utilisateur ne doit pas avoir de bâtiments
            ];

            // Capture the JSON output
            ob_start();
            $result = $authController
->register();
            $jsonOutput = ob_get_clean();

            // Décoder la réponse JSON
            $responseData = json_decode($jsonOutput, true);

            // Vérifications
            $this->assertFalse($result); // La méthode doit retourner false
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString('Bâtiment non existant ou invalide.', $responseData['message']);
        }

        public function testEmailVide()
        {
            $this->viderToutesLesTables();
        $authController = new AuthController();

            // Simuler une soumission POST
            $_SERVER['REQUEST_METHOD'] = 'POST';


            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Dupont',
                'prenom_utilisateur' => 'Test',  // Champ requis manquant
                'mail_utilisateur' => '',
                'mdp_utilisateur' => 'Pass1234',
                'confirmer_mail' => 'jean@example.com',
                'confirmer_mots_de_passe' => 'Pass1234',
                'role_utilisateur' => Role::TECHNICIEN
            ];

            // Capture the JSON output
            ob_start();
            $result = $authController
->register();
            $jsonOutput = ob_get_clean();

            // Decode the JSON
            $responseData = json_decode($jsonOutput, true);

            // Assertions
            $this->assertFalse($result); // The method should return false
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString("L'adresse e-mail est requise.", $responseData['message']);
        }

        public function testEmailAvecEspaces()
        {
            $this->viderToutesLesTables();
        $authController = new AuthController();

            $db = Database::getInstance()->getConnection();

            $db->exec("INSERT INTO `site` (`Id_site`, `nom_site`, `actif_site`) 
            VALUES 
            (1, 'SITE1', '1')");

            // Insérer des bâtiments dans la table `batiment`
            $db->exec("INSERT INTO `batiment` (`Id_batiment`, `nom_batiment`, `actif_batiment`, `Id_site`) 
                       VALUES 
                       (1, 'LAREDOUTE', '1', '1'), 
                       (2, 'ESPIEGLERIE', '1', '1'), 
                       (3, 'LASOURCES', '1', '1')");

            // Simuler une soumission POST
            $_SERVER['REQUEST_METHOD'] = 'POST';

            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Dupont',
                'prenom_utilisateur' => 'Test',  // Champ requis manquant
                'mail_utilisateur' => '  reaiuzr@example.com',
                'confirmer_mail' => 'reaiuzr@example.com', // Valeur trimée pour que la correspondance soit ok
                'mdp_utilisateur' => 'Pass1234',
                'confirmer_mots_de_passe' => 'Pass1234',
                'role_utilisateur' => Role::TECHNICIEN
            ];

            // Capture the JSON output
            ob_start();
            $result = $authController
->register();
            $jsonOutput = ob_get_clean();

            // Decode the JSON
            $responseData = json_decode($jsonOutput, true);

            // Assertions
            $this->assertFalse($result); // The method should return false
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString("Les emails ne correspondent pas.", $responseData['message']);
        }

        public function testEmailFormatIncorrect()
        {
            $this->viderToutesLesTables();
        $authController = new AuthController();

            // Simuler une soumission POST avec un email invalide
            $_SERVER['REQUEST_METHOD'] = 'POST';

            $db = Database::getInstance()->getConnection();

            $db->exec("INSERT INTO `site` (`Id_site`, `nom_site`, `actif_site`) 
            VALUES 
            (1, 'SITE1', '1')");

            // Insérer des bâtiments dans la table `batiment`
            $db->exec("INSERT INTO `batiment` (`Id_batiment`, `nom_batiment`, `actif_batiment`, `Id_site`) 
                       VALUES 
                       (1, 'LAREDOUTE', '1', '1'), 
                       (2, 'ESPIEGLERIE', '1', '1'), 
                       (3, 'LASOURCES', '1', '1')");


            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Dupont',
                'prenom_utilisateur' => 'Jean',
                'mail_utilisateur' => 'jean@com', // Email avec un format incorrect
                'confirmer_mail' => 'jean@com', // Valeur identique pour que la correspondance soit ok
                'mdp_utilisateur' => 'Pass1234',
                'confirmer_mots_de_passe' => 'Pass1234',
                'role_utilisateur' => Role::TECHNICIEN,
            ];

            // Capture the JSON output
            ob_start();
            $result = $authController
->register();
            $jsonOutput = ob_get_clean();

            // Decode the JSON
            $responseData = json_decode($jsonOutput, true);

            // Assertions
            $this->assertFalse($result); // La méthode doit retourner false
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString("L'adresse e-mail n'est pas valide.", $responseData['message']);
        }

        public function testEmailAvecCaracteresSpeciaux()
        {
            $this->viderToutesLesTables();
        $authController = new AuthController();

            $db = Database::getInstance()->getConnection();

            $db->exec("INSERT INTO `site` (`Id_site`, `nom_site`, `actif_site`) 
            VALUES 
            (1, 'SITE1', '1')");

            // Insérer des bâtiments dans la table `batiment`
            $db->exec("INSERT INTO `batiment` (`Id_batiment`, `nom_batiment`, `actif_batiment`, `Id_site`) 
                       VALUES 
                       (1, 'LAREDOUTE', '1', '1'), 
                       (2, 'ESPIEGLERIE', '1', '1'), 
                       (3, 'LASOURCES', '1', '1')");

            // Simuler une soumission POST avec un email contenant des caractères spéciaux
            $_SERVER['REQUEST_METHOD'] = 'POST';


            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Dupont',
                'prenom_utilisateur' => 'Jean',
                'mail_utilisateur' => 'jean_-è.doe@exa%ple.com', // Email avec caractère spécial '%'
                'confirmer_mail' => 'jean_-è.doe@exa%ple.com', // Valeur identique pour que la correspondance soit ok
                'mdp_utilisateur' => 'Pass1234',
                'confirmer_mots_de_passe' => 'Pass1234',
                'role_utilisateur' => Role::TECHNICIEN,
            ];

            // Capture the JSON output
            ob_start();
            $result = $authController
->register();
            $jsonOutput = ob_get_clean();

            // Decode the JSON
            $responseData = json_decode($jsonOutput, true);

            // Assertions
            $this->assertFalse($result); // The method should return false
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString("L'adresse e-mail n'est pas valide.", $responseData['message']);
        }

        /* ============================================== */
        /* ============ TESTS MOT DE PASSE ============== */
        /* ============================================== */
        public function testMotDePasseTropCourt()
        {
            $this->viderToutesLesTables();
        $authController = new AuthController();

            $db = Database::getInstance()->getConnection();

            $db->exec("INSERT INTO `site` (`Id_site`, `nom_site`, `actif_site`) 
            VALUES 
            (1, 'SITE1', '1')");

            // Insérer des bâtiments dans la table `batiment`
            $db->exec("INSERT INTO `batiment` (`Id_batiment`, `nom_batiment`, `actif_batiment`, `Id_site`) 
                       VALUES 
                       (1, 'LAREDOUTE', '1', '1'), 
                       (2, 'ESPIEGLERIE', '1', '1'), 
                       (3, 'LASOURCES', '1', '1')");

            // Simuler une soumission POST avec un mot de passe trop court
            $_SERVER['REQUEST_METHOD'] = 'POST';


            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Dupont',
                'prenom_utilisateur' => 'Jean',
                'mail_utilisateur' => 'jean@example.com',
                'confirmer_mail' => 'jean@example.com',
                'mdp_utilisateur' => 'Pass', // Mot de passe trop court
                'confirmer_mots_de_passe' => 'Pass', // Valeur identique pour que la correspondance soit ok
                'role_utilisateur' => Role::TECHNICIEN,
            ];

            // Capture the JSON output
            ob_start();
            $result = $authController
->register();
            $jsonOutput = ob_get_clean();

            // Decode the JSON
            $responseData = json_decode($jsonOutput, true);

            // Assertions
            $this->assertFalse($result); // La méthode doit retourner false
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString("Le mot de passe n'est pas valide. Il doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.", $responseData['message']);
        }

        public function testMotDePasseSansMajuscule()
        {
            $this->viderToutesLesTables();
        $authController = new AuthController();

            $db = Database::getInstance()->getConnection();

            $db->exec("INSERT INTO `site` (`Id_site`, `nom_site`, `actif_site`) 
            VALUES 
            (1, 'SITE1', '1')");

            // Insérer des bâtiments dans la table `batiment`
            $db->exec("INSERT INTO `batiment` (`Id_batiment`, `nom_batiment`, `actif_batiment`, `Id_site`) 
                       VALUES 
                       (1, 'LAREDOUTE', '1', '1'), 
                       (2, 'ESPIEGLERIE', '1', '1'), 
                       (3, 'LASOURCES', '1', '1')");

            // Simuler une soumission POST avec un mot de passe sans majuscule
            $_SERVER['REQUEST_METHOD'] = 'POST';


            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Dupont',
                'prenom_utilisateur' => 'Jean',
                'mail_utilisateur' => 'jean@example.com',
                'confirmer_mail' => 'jean@example.com',
                'mdp_utilisateur' => 'pass1234', // Mot de passe sans majuscule
                'confirmer_mots_de_passe' => 'pass1234', // Valeur identique pour que la correspondance soit ok
                'role_utilisateur' => Role::TECHNICIEN,
            ];

            // Capture the JSON output
            ob_start();
            $result = $authController
->register();
            $jsonOutput = ob_get_clean();

            // Decode the JSON
            $responseData = json_decode($jsonOutput, true);

            // Assertions
            $this->assertFalse($result); // La méthode doit retourner false
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString("Le mot de passe n'est pas valide. Il doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.", $responseData['message']);
        }


        /* ========================================================== */
        /* ============ TESTS INSCRIPTION TECHNICIEN VALIDE ========= */
        /* ========================================================== */
        public function testInscriptionTechnicienValide()
        {
            $this->viderToutesLesTables();
            $this->insererRoles();            
        $authController = new AuthController();

            $db = Database::getInstance()->getConnection();

            $db->exec("INSERT INTO `site` (`Id_site`, `nom_site`, `actif_site`) 
            VALUES 
            (1, 'SITE1', '1')");

            // Insérer des bâtiments dans la table `batiment`
            $db->exec("INSERT INTO `batiment` (`Id_batiment`, `nom_batiment`, `actif_batiment`, `Id_site`) 
                       VALUES 
                       (1, 'LAREDOUTE', '1', '1'), 
                       (2, 'ESPIEGLERIE', '1', '1'), 
                       (3, 'LASOURCES', '1', '1')");

            // Simuler une soumission POST pour un technicien valide
            $_SERVER['REQUEST_METHOD'] = 'POST';


            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Dupont',
                'prenom_utilisateur' => 'Jean',
                'mail_utilisateur' => 'jeanvalide@example.com',
                'confirmer_mail' => 'jeanvalide@example.com', // Valeur identique pour que la correspondance soit ok
                'mdp_utilisateur' => 'Pass1234.',
                'confirmer_mots_de_passe' => 'Pass1234.', // Valeur identique pour que la correspondance soit ok
                'role_utilisateur' => Role::TECHNICIEN,
                'valider_register' => 'true', // Simuler la validation de l'register
                'actif' => 'true', // Simuler que l'utilisateur est actif
            ];

            // Capture the JSON output
            ob_start();
            $result = $authController
->register();
            $jsonOutput = ob_get_clean();

            // Decode the JSON
            $responseData = json_decode($jsonOutput, true);

            // Assertions
            $this->assertTrue($result); // La méthode doit retourner true
            $this->assertEquals('success', $responseData['status']);
            $this->assertStringContainsString('Votre demande d\'inscription a bien été envoyée. Elle sera examinée par un administrateur.', $responseData['message']);
        }

        /* =================================================================== */
        /* ============ TESTS TECHNICIEN SANS BATIMENTS DISPONIBLES ========== */
        /* =================================================================== */
        public function testTechnicienSansBatimentsDisponibles()
        {
            $this->viderToutesLesTables();            // Vider la table des bâtiments

            $this->insererRoles();            
            $pdo = Database::getInstance()->getConnection();

        $authController = new AuthController();

            // Simuler une soumission POST pour un technicien sans bâtiments disponibles
            $_SERVER['REQUEST_METHOD'] = 'POST';


            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Dupont',
                'prenom_utilisateur' => 'Jean',
                'mail_utilisateur' => 'jean@example.com',
                'mdp_utilisateur' => 'Pass1234',
                'confirmer_mail' => 'jean@example.com',
                'confirmer_mots_de_passe' => 'Pass1234',
                'role_utilisateur' => Role::TECHNICIEN,
                'batiments_utilisateur' => [], // Aucun bâtiment disponible
            ];

            // Capture the JSON output
            ob_start();
            $result = $authController
->register();
            $jsonOutput = ob_get_clean();

            // Decode the JSON
            $responseData = json_decode($jsonOutput, true);

            // Réinitialiser l'auto-incrémentation de l'ID et réinsérer des bâtiments pour les tests
            $pdo->exec(statement: "INSERT INTO `site` (`Id_site`, `nom_site`, `actif_site`) VALUES (1, 'SITE1', '1')");
                                                                                            
            $pdo->exec("INSERT INTO `batiment` (`Id_batiment`, `nom_batiment`, `actif_batiment`, `Id_site`) VALUES (1, 'REDOUTE', '1', '1'), (2, 'SOURCE', '1', '1'), (3, 'ESPIEGLERIE', '1', '1'), (4, 'TEST', '0', '1')");    // Assertions
            $this->assertFalse($result); // La méthode doit retourner false
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString('Aucun bâtiment disponible', $responseData['message']);
        }

        /* ======================================================= */
        /* ============ TESTS TECHNICIEN AVEC BATIMENTS ========== */
        /* ======================================================= */
        public function testTechnicienAvecBatiments()
        {
            $this->viderToutesLesTables();
        $authController = new AuthController();

            $db = Database::getInstance()->getConnection();

            $db->exec("INSERT INTO `site` (`Id_site`, `nom_site`, `actif_site`) 
            VALUES 
            (1, 'SITE1', '1')");

            // Insérer des bâtiments dans la table `batiment`
            $db->exec("INSERT INTO `batiment` (`Id_batiment`, `nom_batiment`, `actif_batiment`, `Id_site`) 
                       VALUES 
                       (1, 'LAREDOUTE', '1', '1'), 
                       (2, 'ESPIEGLERIE', '1', '1'), 
                       (3, 'LASOURCES', '1', '1')");

            // Simuler une soumission POST pour un technicien qui choisit des bâtiments
            $_SERVER['REQUEST_METHOD'] = 'POST';


            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Dupont',
                'prenom_utilisateur' => 'Jean',
                'mail_utilisateur' => 'jean@example.com',
                'mdp_utilisateur' => 'Pass1234',
                'confirmer_mail' => 'jean@example.com',
                'confirmer_mots_de_passe' => 'Pass1234',
                'role_utilisateur' => Role::TECHNICIEN,
                'batiments_utilisateur' => ['1', '2'], // Bâtiments choisis alors que ce n'est pas permis pour un technicien
            ];

            // Capture the JSON output
            ob_start();
            $result = $authController
->register();
            $jsonOutput = ob_get_clean();

            // Decode the JSON
            $responseData = json_decode($jsonOutput, true);

            // Assertions
            $this->assertFalse($result); // La méthode doit retourner false
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString('Les techniciens ne doivent pas sélectionner de bâtiments', $responseData['message']);
        }


        /* ============================================== */
        /* ============ TESTS UTILISATEUR SANS BATIMENTS ========== */
        /* ============================================== */
        public function testUtilisateurSansBatiments()
        {
            $this->viderToutesLesTables();
        $authController = new AuthController();

            // Simuler une soumission POST pour un utilisateur sans bâtiments sélectionnés
            $_SERVER['REQUEST_METHOD'] = 'POST';


            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (Batiment manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Dupont',
                'prenom_utilisateur' => 'Jean',
                'mail_utilisateur' => 'jean@example.com',
                'mdp_utilisateur' => 'Pass1234',
                'confirmer_mail' => 'jean@example.com',
                'confirmer_mots_de_passe' => 'Pass1234',
                'role_utilisateur' => Role::UTILISATEUR,
                'batiments_utilisateur' => [], // Aucun bâtiment sélectionné
            ];

            // Capture the JSON output
            ob_start();
            $result = $authController->register();
            $jsonOutput = ob_get_clean();

            // Decode the JSON
            $responseData = json_decode($jsonOutput, true);

            // Assertions
            $this->assertFalse($result); // La méthode doit retourner false
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString('Veuillez sélectionner au moins un bâtiment', $responseData['message']);
        }
} 
