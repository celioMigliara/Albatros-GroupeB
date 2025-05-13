<?php

define('PHPUNIT_RUNNING', true);

if (!defined("BASE_URL")) {
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
}

require_once __DIR__ . '/../../../Model/B3/db_connect.php';
require_once __DIR__ . '/../../../Model/B3/Role.php';
require_once __DIR__ . '/../../../Controller/B3/PasswordController.php';
require_once __DIR__ . '/../../../Test/B3/BaseTestClass.php';
require_once __DIR__ . '/../../../Model/B3/Security.php';

class PasswordControllerTest extends BaseTestClass
{
    private $passwordController;

    private function generateCsrfToken() {
        $security = new Security();
        $token = $security->genererCSRFToken();
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    /* ========================================================== */
    /* ========== TESTS MOT DE PASSE OUBLIÉ ==================== */
    /* ========================================================== */
    public function testMotDePasseOublieEmailInvalide()
    {
        $passwordController = new PasswordController();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = $this->generateCsrfToken();
        $_POST = [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => 'invalid-email'
        ];

        ob_start();
        $result = $passwordController->sendResetEmail();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Le format de l\'adresse email est invalide.', $response['message']);
    }

    public function testMotDePasseOublieEmailValide()
    {
        $passwordController = new PasswordController();
        $this->viderToutesLesTables();
        $this->insererRoles();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass1234', Role::UTILISATEUR);
        $user->setInscriptionValide(true);
        $user->setActif(true);
        $user->insertUser();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = $this->generateCsrfToken();
        $_POST = [
            'csrf_token' => $csrfToken,
            'mail_utilisateur' => 'jean@example.com'
        ];

        ob_start();
        $result = $passwordController->sendResetEmail();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertTrue($result);
        $this->assertEquals('success', $response['status']);
        $this->assertStringContainsString('Un email de réinitialisation a été envoyé', $response['message']);
    }

    /* ========================================================== */
    /* ========== TESTS RÉINITIALISATION MOT DE PASSE ========== */
    /* ========================================================== */
    public function testReinitialisationMotDePasseTokenInvalide()
    {
        $passwordController = new PasswordController();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrfToken = $this->generateCsrfToken();
        $_POST = [
            'csrf_token' => $csrfToken,
            'token' => 'invalid-token',
            'new_password' => 'NewPass1234'
        ];

        ob_start();
        $result = $passwordController->ChangePassword();
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertFalse($result);
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Token invalide ou expiré. Impossible de charger la page', $response['message']);
    }

        /* ========================================================== */
        /* ========== TESTS FORMAT EMAIL INVALIDE =================== */
        /* ========================================================== */
        public function testResetPasswordEmailFormatInvalide()
            {
                $passwordController = new PasswordController();

                // Simuler une soumission POST avec un format d'email incorrect
                $_SERVER['REQUEST_METHOD'] = 'POST';


                $csrfToken = bin2hex(random_bytes(32));
                $_SESSION['csrf_token'] = $csrfToken;

                // Formulaire incomplet (prénom manquant)
                $_POST = [
                    'csrf_token' => $csrfToken,
                    'mail_utilisateur' => 'invalid-email', // Email au format incorrect
                ];

                ob_start();
                $result = $passwordController->sendResetEmail();
                $jsonOutput = ob_get_clean();
                $responseData = json_decode($jsonOutput, true);

                $this->assertFalse($result);
                $this->assertEquals('error', $responseData['status']);
                $this->assertStringContainsString("Le format de l'adresse email est invalide.", $responseData['message']);
        }

        /* ========================================================== */
        /* =============== TESTS FORMULAIRE INCOMPLET =============== */
        /* ========================================================== */
        public function testResetPasswordFormulaireIncomplet()
        {
            $passwordController = new PasswordController();

            // Simuler une soumission POST sans email
            $_SERVER['REQUEST_METHOD'] = 'POST';


            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'mail_utilisateur' => '', // Email manquant
            ];

            ob_start();
            $result = $passwordController->sendResetEmail();
            $jsonOutput = ob_get_clean();
            $responseData = json_decode($jsonOutput, true);

            $this->assertFalse($result);
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString("Adresse email invalide", $responseData['message']);
        }



        /* ========================================================== */
        /* ========== TESTS EMAIL NON TROUVE ======================== */
        /* ========================================================== */
        public function testResetPasswordEmailNonTrouve()
        {
            $passwordController = new PasswordController();

            // Simuler une soumission POST avec un email non existant
            $_SERVER['REQUEST_METHOD'] = 'POST';


            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Formulaire incomplet (prénom manquant)
            $_POST = [
                'csrf_token' => $csrfToken,
                'mail_utilisateur' => 'nonexistent@example.com', // Email qui n'existe pas
            ];

            ob_start();
            $result = $passwordController->sendResetEmail();
            $jsonOutput = ob_get_clean();
            $responseData = json_decode($jsonOutput, true);

            $this->assertFalse($result);
            $this->assertEquals('error', $responseData['status']);
            $this->assertStringContainsString("Aucun compte valide associé à cet email.", $responseData['message']);
        }


} 