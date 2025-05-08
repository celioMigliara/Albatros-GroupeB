<?php

require_once 'Model/B3/Token.php';
require_once 'Model/B3/Email.php';
require_once 'Model/B3/UserCredentials.php';
require_once 'Model/B3/Security.php';

class PasswordController
{
    // Fonction pour afficher la page de réinitialisation du mot de passe
    // et envoyer l'email de réinitialisation
    public function sendResetEmail()
    {
        // Object Security pour les sessions sécurisées et pour le CSRF Token
        $securityObj = new Security();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Définir un code HTTP 400 (Bad Request) par défaut
            http_response_code(400);

            // On renvoie du JSON par défaut (AJAX)
            header("Content-Type: application/json");

            // Vérification du token CSRF
            if (!$securityObj->checkCSRFToken($_POST['csrf_token'] ?? ''))
            {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Token CSRF invalide."
                ]);
                return false;
            }

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
            $defaultResetLink = "localhost/albatrosB3";
            
            // Si on est en prod, on utilise le mail prod, sinon on utilise l'email de dev locale (localhost). On a de l'erreur checking pour éviter tout bug
            $reset_link = (($_ENV['APP_ENV'] ?? '') === 'prod') ? ($_ENV['MAIL_URL_PROD_RESET_MDP'] ?? $defaultResetLink) : ($_ENV['MAIL_URL_LOCALE_RESET_MDP'] ?? $defaultResetLink);  
            $reset_link .= "/motdepasse/changer?token=" . $token->GetToken();

            // Envoyer l'email
            $subject = "Réinitialisation de votre mot de passe - Albatros";
            $message = "
            <p>Bonjour,</p>

            <p>Vous avez demandé la réinitialisation de votre mot de passe pour accéder à la plateforme <strong>Albatros</strong>.</p>

            <p>Pour définir un nouveau mot de passe, veuillez cliquer sur le lien ci-dessous :</p>

            <p><a href=\"$reset_link\">Réinitialiser mon mot de passe</a></p>

            <p>Ce lien est valable seulement une heure. Si vous n'avez pas demandé cette réinitialisation, vous pouvez ignorer cet email en toute sécurité.</p>

            <p>Cordialement,<br>L'équipe Albatros</p>
        ";

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
            $debugMailToken = filter_var($_ENV['DEBUG_MAIL_TOKEN'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if ($debugMailToken)
            {
                $retourJson['debug'] = 'Debug: voici le lien : ' . $reset_link;
            }

            echo json_encode($retourJson);
            return $mailEnvoye;
        } else {

            // Génére le token CSRF
            $csrf_token = $securityObj->genererCSRFToken();
            
            // Affiche la page si la méthode n'est pas POST (en cas de simple visite de la page)
            require 'View/B3/ResetPassword.php';
            return true;
        }
    }

    // Fonction pour changer le mot de passe
    public function ChangePassword()
    {
        // Sécurité et token CSRF
        $securityObj = new Security();

        // Définir un code HTTP 400 (Bad Request) par défaut
        http_response_code(400);

        // Vérifier si la requête est en POST
        if ($_SERVER["REQUEST_METHOD"] === "POST") 
        {
            // On renvoie du JSON par défaut (AJAX)
            header("Content-Type: application/json");

            // Vérification du token CSRF
            if (!$securityObj->checkCSRFToken($_POST['csrf_token'] ?? ''))
            {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Token CSRF invalide."
                ]);
                return false;
            }

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
                    'redirect' => BASE_URL . '/connexion'
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
                // On setup le message d'erreur pour la vue
                $errorMsg = new MessageErreur("Chargement de la page impossible", "Token invalide ou expiré");
                require 'View/B3/PageErreur.php';
                return false;
            }

            // Définir un code HTTP 200 (succès)
            http_response_code(200);

            // Génération du token csrf 
            $csrf_token = $securityObj->genererCSRFToken();
            require 'View/B3/ChangerPassword.php';
            return true;
        }
    }
}