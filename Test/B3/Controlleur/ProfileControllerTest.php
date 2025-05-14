<?php

define('PHPUNIT_RUNNING', true);

if (!defined("BASE_URL")) {
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
}

require_once __DIR__ . '/../../../Model/B3/db_connect.php';
require_once __DIR__ . '/../../../Model/B3/Role.php';
require_once __DIR__ . '/../../../Controller/B3/ProfileController.php';
require_once __DIR__ . '/../../../Test/B3/BaseTestClass.php';

class ProfileControllerTest extends BaseTestClass
{
    private $profileController;

        /* ===================================================================================== */
        /* ======================== PARTIE DES TESTS POUR MODIFIER PROFIL ====================== */
        /* ===================================================================================== */

        /* ========================================================== */
        /* =========== TESTS UTILISATEUR NON CONNECTE =============== */
        /* ========================================================== */
        public function testModifierProfilUtilisateurNonConnecte()
        {
            // Réinitialiser la session
            if (session_status() !== PHP_SESSION_NONE) {
                session_unset();
                session_destroy();
            }
            $_SESSION = [];

            $this->viderToutesLesTables();
            $this->insererRoles();            
            $profileController = new ProfileController();

            $_SERVER['REQUEST_METHOD'] = 'POST';

            // Essayer de modifier le profil sans être connecté

            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire complet mais user non connecté
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Dupont',
                'prenom_utilisateur' => 'Jean',
                'mail_utilisateur' => 'nonconnecter@example.com',
                'mdp_utilisateur' => 'Pass1234'
            ];

            ob_start();
            $result = $profileController->updateProfile();
            $jsonOutput = ob_get_clean();
            $responseData = json_decode($jsonOutput, true);

            $this->assertFalse($result);
        }

        /* ========================================================== */
        /* =============== TESTS AUCUN CHAMP MODIFIE ================ */
        /* ========================================================== */
        public function testModifierProfilAucunChampModifie()
        {
            $_SERVER['REQUEST_METHOD'] = 'POST';

            // Simuler un utilisateur connecté
            $_SESSION['user'] = ['id' => 1, 'nom' => 'Dupont', 'prenom' => 'Jean', 'email' => 'jean@example.com'];

            $this->viderToutesLesTables();
            $profileController = new ProfileController();

            // Essayer de modifier sans changer de champ

            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
            ];

            ob_start();
            $result = $profileController->updateProfile();
            $jsonOutput = ob_get_clean();
            $responseData = json_decode($jsonOutput, true);

            $this->assertFalse($result);
            $this->assertEquals('warning', $responseData['status']);
            $this->assertStringContainsString("Aucun champ à modifier n'est fourni", $responseData['message']);
        }

        public function testModificationProfilEmailDejaUtilise()
        {
            $this->viderToutesLesTables();
            $this->insererRoles();            
            $user1 = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass1234', Role::UTILISATEUR);
            $user1->setInscriptionValide(true);
            $user1->setActif(true);
            $user1->insertUser();

            $user2 = new UserCredentials('Martin', 'Pierre', 'pierre@example.com', 'Pass1234', Role::UTILISATEUR);
            $user2->setInscriptionValide(true);
            $user2->setActif(true);
            $user2->insertUser();

            $this->profileController = new ProfileController();
            
            $_SERVER['REQUEST_METHOD'] = 'POST';
            session_start();
            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;
            $_SESSION['user'] = [
                'id' => 1,
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'email' => 'jean@example.com',
                'role_id' => Role::UTILISATEUR
            ];

            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Dupont',
                'prenom_utilisateur' => 'Jean',
                'mail_utilisateur' => 'pierre@example.com', // Email déjà utilisé
            ];

            ob_start();
            $result = $this->profileController->updateProfile();
            $jsonOutput = ob_get_clean();
            $responseData = json_decode($jsonOutput, true);

            $this->assertFalse($result);
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString("Erreur : L'email est déjà utilisée", $responseData['message']);
        }

                /* ========================================================== */
        /* ========== TESTS FORMAT EMAIL INVALIDE =================== */
        /* ========================================================== */
        public function testModifierProfilEmailInvalide()
        {
            $_SERVER['REQUEST_METHOD'] = 'POST';

            // Simuler un utilisateur connecté
            $_SESSION['user'] = ['id' => 1, 'nom' => 'Dupont', 'prenom' => 'Jean', 'email' => 'jean@example.com'];

            $this->viderToutesLesTables();
            $this->insererRoles();            
            $profileController = new ProfileController();

            // Essayer de modifier l'email avec un format invalide

            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (Email au format incorrect)
            $_POST = [
                'csrf_token' => $csrfToken,
                'mail_utilisateur' => 'invalid-email' // Email au format incorrect
            ];

            ob_start();
            $result = $profileController->updateProfile();
            $jsonOutput = ob_get_clean();
            $responseData = json_decode($jsonOutput, true);

            $this->assertFalse($result);
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString("Le format de l'email est invalide", $responseData['message']);
        }


        /* ========================================================== */
        /* ========== TESTS MODIFICATION PROFIL REUSSIE ============= */
        /* ========================================================== */
        public function testModifierProfilReussi()
        {
            $_SERVER['REQUEST_METHOD'] = 'POST';

            // Simuler un utilisateur connecté
            $_SESSION['user'] = ['id' => 1, 'nom' => 'Dupont', 'prenom' => 'Jean', 'email' => 'jean@example.com'];

            $this->viderToutesLesTables();
            $profilController = new ProfileController();

            // Modifier le nom et le prénom

            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => 'Durand',
                'prenom_utilisateur' => 'Marc',
                'mail_utilisateur' => 'jean@example.com',
                'mdp_utilisateur' => 'NewPass1234'
            ];

            ob_start();
            $result = $profilController->updateProfile();
            $jsonOutput = ob_get_clean();
            $responseData = json_decode($jsonOutput, true);

            $this->assertTrue($result);
            $this->assertEquals('success', $responseData['status']);
            $this->assertStringContainsString("Votre profil a été changé avec succès.", $responseData['message']);
        }


        /* ========================================================== */
        /* ========== TESTS MOT DE PASSE INVALIDE =================== */
        /* ========================================================== */
        public function testModifierProfilMotDePasseInvalide()
        {
            $_SERVER['REQUEST_METHOD'] = 'POST';

            // Simuler un utilisateur connecté
            $_SESSION['user'] = ['id' => 1, 'nom' => 'Dupont', 'prenom' => 'Jean', 'email' => 'jean@example.com'];

            $this->viderToutesLesTables();
            $this->insererRoles();            
            $profilController = new ProfileController();

            // Essayer de modifier le mot de passe avec un mot de passe invalide

            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (mot de passe trop court)
            $_POST = [
                'csrf_token' => $csrfToken,
                'mdp_utilisateur' => 'e123' // Mot de passe trop court
            ];

            ob_start();
            $result = $profilController->updateProfile();
            $jsonOutput = ob_get_clean();
            $responseData = json_decode($jsonOutput, true);

            $this->assertFalse($result);
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString("Erreur : Le nouveau mot de passe n'est pas valide. Il faut au moins 8 caractères avec une minuscule, une majuscule et un chiffre", $responseData['message']);
        }

    public function testModificationMotDePasseValide()
    {
        $this->viderToutesLesTables();
        $this->insererRoles();            
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass1234', Role::UTILISATEUR);
        $user->setInscriptionValide(true);
        $user->setActif(true);
        $user->insertUser();
        $this->profileController = new ProfileController();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        session_start();
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_SESSION['user'] = [
            'id' => 1,
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'jean@example.com',
            'role_id' => Role::UTILISATEUR
        ];

        $_POST = [
            'csrf_token' => $csrfToken,
            'mdp_utilisateur' => 'NouveauPass1234',
        ];

        ob_start();
        $result = $this->profileController->updateProfile();
        $jsonOutput = ob_get_clean();
        $responseData = json_decode($jsonOutput, true);

        $this->assertTrue($result);
        $this->assertEquals('success', $responseData['status']);
        $this->assertStringContainsString('Votre profil a été changé avec succès', $responseData['message']);
    }

           /* ========================================================== */
        /* ========== TESTS FORMAT NOM / PRENOM INVALIDE ============ */
        /* ========================================================== */
        public function testModifierProfilNomPrenomInvalide()
        {
            $_SERVER['REQUEST_METHOD'] = 'POST';

            // Simuler un utilisateur connecté
            $_SESSION['user'] = ['id' => 1, 'nom' => 'Dupont', 'prenom' => 'Jean', 'email' => 'jean@example.com'];

            $this->viderToutesLesTables();
            $this->insererRoles();            
            $profileController = new ProfileController();

            // Essayer de modifier le nom avec un format invalide

            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (Nom invalide)
            $_POST = [
                'csrf_token' => $csrfToken,
                'nom_utilisateur' => '1234', // Nom invalide
                'prenom_utilisateur' => 'Jean'
            ];

            ob_start();
            $result = $profileController->updateProfile();
            $jsonOutput = ob_get_clean();
            $responseData = json_decode($jsonOutput, true);

            $this->assertFalse($result);
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString("Le format du nom est invalide.", $responseData['message']);
        }
} 