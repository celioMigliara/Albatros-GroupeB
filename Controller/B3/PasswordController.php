<?php

require_once __DIR__ . '/../../Model/B3/Token.php';
require_once __DIR__ . '/../../Model/B3/Email.php';
require_once __DIR__ . '/../../Model/B3/UserCredentials.php';
class PasswordController
{
    // Fonction pour afficher la page de réinitialisation du mot de passe
    // et envoyer l'email de réinitialisation
    public function sendResetEmail()
    {
        // Démarrage de session ABSOLUMENT EN PREMIER
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Définir un code HTTP 400 (Bad Request) par défaut
            http_response_code(400);

            // On renvoie du JSON par défaut (AJAX)
            header("Content-Type: application/json");

            // On vérifie que l'email a bien été fournie
            $email = $_POST['mail_utilisateur'] ?? null;
            if (empty($email)) {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Adresse email invalide.'
                ]);
                return false;
            }

            // Vérifier le format de l'email
            if (!UserCredentials::verifyEmailFormat($email)) {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Le format de l\'adresse email est invalide.'
                ]);
                return false;
            }

            // Vérifier si l'email existe dans la base de données
            $userId = UserCredentials::getUserIdWithEmail($email);

            // Si l'utilisateur n'existe pas, on ne renvoie pas d'info
            if ($userId === false) {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Aucun compte valide associé à cet email.'
                ]);
                return false;
            }

            // Sauvegarder le token en base de données
            $token = new Token();
            if (!$token->SetUserToken($userId)) {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Erreur: Utilisateur invalide'
                ]);

                return false;
            }

            // Créer le lien de réinitialisation
            // Le lien du site Web Albatros : https://www.albatros-asbl.be/
            $reset_link = "localhost/albatrosB3/index.php?action=changemdp&token=" . $token->GetToken();

            // Envoyer l'email
            $subject = "Réinitialisation de votre mot de passe";
            $message = "Bonjour,<br><br>Cliquez sur ce lien pour réinitialiser votre mot de passe :<br>" . $reset_link;

            // On utilise la classe Email pour envoyer l'email
            $emailObject = new Email($email, $subject, $message);

            // On vérifie que l'email a bien été envoyé
            $mailEnvoye = $emailObject->sendMail();
            if ($mailEnvoye)
            {
                // Définir un code HTTP 200 (Succès)
                http_response_code(200);

                // Réponse JSON avec le message de succès
                $retourJson = 
                [
                    'status' => 'success',
                    'message' => 'Un email de réinitialisation a été envoyé.'
                ];
            }
            else 
            {
                // Réponse JSON avec le message d'erreur
                $retourJson = 
                [
                    'status' => 'error',
                    'message' => 'L\'envoi de l\'email a échoué.'
                ];
            }

            // Si jamais on veut debug le token pour le reset de mdp
            $debugMailToken = true;
            if ($debugMailToken)
            {
                $retourJson['debug'] = 'Debug: voici le lien : ' . $reset_link;
            }

            echo json_encode($retourJson);
            return $mailEnvoye;
        } else {
            // Affiche la page si la méthode n'est pas POST (en cas de simple visite de la page)
            require 'Vue/ResetPassword.php';
            return true;
        }
    }

    // Fonction pour changer le mot de passe
    public function ChangePassword()
    {
        // Démarrage de session ABSOLUMENT EN PREMIER
        if (session_status() === PHP_SESSION_NONE) {
            // Configurer les paramètres du cookie de session
            session_set_cookie_params([
                'httponly' => true,
                'secure' => false, // à activer uniquement en HTTPS
                'samesite' => 'Strict'
            ]);

            // Démarrer la session
            session_start();
        }

        // Génération du token AVANT TOUTE CHOSE
        $csrf_token = genererCSRFToken();


        // Définir un code HTTP 400 (Bad Request) par défaut
        http_response_code(400);

        // Vérifier si la requête est en POST
        if ($_SERVER["REQUEST_METHOD"] === "POST") 
        {

            // Vérification du token CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Token CSRF invalide."
                ]);
                return false;
            }

            // On renvoie du JSON par défaut (AJAX)
            header("Content-Type: application/json");

            // On vérifie qu'on a bien le nouveau mot de passe
            $newPassword = $_POST['new_password'] ?? null;
            if (empty($newPassword)) {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Mot de passe manquant.'
                ]);
                return false;
            }

            // On vérifie qu'on a bien le mot de passe de confirmation
            $tokenValue = $_POST['token'] ?? null;

            $token = new Token($tokenValue);
            // On vérifie qu'on a bien un token valide. UserId est null si le result est false
            $userId = $token->isTokenValid();
            if ($userId === false)
            {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Token invalide ou expiré. Impossible de charger la page'
                    ]);
                return false;
            }

            // Vérifier la robustesse du mot de passe
            if (!UserCredentials::verifyStrongPassword($newPassword)) {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre."
                ]);
                return false;
            }

            // Mise à jour du mot de passe dans la base de données
            if (UserCredentials::updateUserPasswordById($userId, $newPassword)) {
                // Définir un code HTTP 200 (Succès) par défaut
                http_response_code(200);

                // Reset le token après modification
                Token::ResetUserToken($userId);

                // Réponse JSON avec le message de succès
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Votre mot de passe a été changé avec succès.',
                    'redirect' => 'index.php?action=connexion'
                ]);
                return true;
            } else {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Une erreur est survenue, veuillez réessayer.'
                ]);
                return false;
            }
        } 
        else if ($_SERVER["REQUEST_METHOD"] === "GET") 
        {
            // Affiche la page si la méthode n'est pas POST (en cas de simple visite de la page)
            $tokenValue = $_GET['token'] ?? null;

            $token = new Token($tokenValue);

            // On vérifie qu'on a bien un token valide
            $userId = $token->isTokenValid();
            if ($userId === false)
            {
                echo "Token invalide ou expiré. Impossible de charger la page";
                return false;
            }

            // Définir un code HTTP 200 (succès)
            http_response_code(200);

            require 'Vue/ChangerPassword.php';
            return true;
        }
    }
}