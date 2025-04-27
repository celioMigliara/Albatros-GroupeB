<?php

require_once __DIR__ . '/../../Model/B3/UserCredentials.php';
require_once __DIR__ . '/../../Model/B3/Role.php';
require_once __DIR__ . '/../../Model/B3/Batiment.php';

class AuthController
{
    // Fonction pour gerer l'inscription
    public function register()
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

        // Vérification de la méthode de la requête
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Vérification du token CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                // Réponse JSON avec le message d'erreur
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Token CSRF invalide."
                ]);
                return false;
            }

            // Définir un code HTTP 400 (Bad Request) par défaut
            http_response_code(400);
            header('Content-Type: application/json');

            // On recupere les valeur du formulaire a l'aide des variable POST
            $nom    = $_POST['nom_utilisateur'] ?? null;
            $prenom = $_POST['prenom_utilisateur'] ?? null;
            $email  = $_POST['mail_utilisateur'] ?? null;
            $role   = $_POST['role_utilisateur'] ?? null;
            $pass   = $_POST['mdp_utilisateur'] ?? null;
            $batiments = $_POST['batiments_utilisateur'] ?? [];

            // On verifie si le formulaire est complet
            if (empty($nom) || empty($prenom) || empty($email) || empty($role) || empty($pass)) {
                echo json_encode(['status' => 'error', 'message' => 'Le formulaire d\'inscription n\'est pas complet.']);
                return false;
            }

            // Validation renforcée, on ne peut choisir que ces 2 roles pour l'inscription
            if (!in_array($role, [Role::TECHNICIEN, Role::UTILISATEUR])) {
                echo json_encode(['status' => 'error', 'message' => "Rôle invalide"]);
                return false;
            }

            // Le techicien a par défaut tous les batiments. Si on recoit des batiments pour 
            // l'inscription d'un technicien, c'est probablement du à un bug sur le front end 
            // de la page d'inscription qui donne l'opportunité à un technicien de choisir des batiments
            if ($role == Role::TECHNICIEN && !empty($batiments)) {
                echo json_encode(['status' => 'error', 'message' => "Les techniciens ne doivent pas sélectionner de bâtiments"]);
                return false;
            }

            // Les utilisateurs devraient avoir choisi leur batiments
            if ($role == Role::UTILISATEUR && empty($batiments)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => "Veuillez sélectionner au moins un bâtiment."
                ]);
                return false;
            }

            // On assigne tous les batiments aux techniciens
            if ($role == Role::TECHNICIEN) {
                $allBatiments = Batiment::getBatiments();
                $batiments = $allBatiments ? array_column($allBatiments, 'Id_batiment') : [];

                if (empty($batiments)) {
                    echo json_encode(['status' => 'error', 'message' => "Aucun bâtiment disponible"]);
                    return false;
                }
            }
            
            // Créer l'objet ici avec les bons bâtiments
            $BatimentObj = new Batiment($batiments);
            if (!$BatimentObj->validateBatiments()) {
                echo json_encode([
                    'status' => 'error',
                    'message' => "Bâtiment non existant ou invalide."
                ]);
                return false;
            }

            // On crée un objet UserCredentials pour gérer l'inscription
            $userCredentials = new UserCredentials($nom, $prenom, $email, $pass, $role);

            // On vérifie l'inscription de l'utilisateur
            [$returnValue, $jsonArray] = $userCredentials->verifyUserInscription();
            if (!$returnValue) {
                echo json_encode($jsonArray);
                return false;
            }

            // si l'inscription est valide, on insère l'utilisateur
            if (!$userCredentials->insertUser()) {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Echec de l\'inscription.'
                ]);
                return false;
            }

            // On associe les bâtiments à l'utilisateur
            $userId = $userCredentials->getUserId(); // Récupère l'ID de l'utilisateur
            $BatimentObj->setUserId($userId);

            // Au lieu de boucler ici, appelle directement la fonction qui insère tous les bâtiments pour cet utilisateur
            if (!$BatimentObj->insertBatimentsUtilisateur()) {
                echo json_encode([
                    'status' => 'error',
                    'message' => "Erreur lors de l'association des bâtiments à l'utilisateur."
                ]);
                return false;
            }

            // Définir un code HTTP 200 pour le succès
            http_response_code(200);

            // Réponse JSON avec le message de succès
            echo json_encode([
                'status' => 'success',
                'message' => 'Votre demande d\'inscription a bien été envoyée. Elle sera examinée par un administrateur.'
            ]);
            return true;
        } else {
            // Si la méthode n'est pas POST, on affiche le formulaire d'inscription
            // et on re-recupère les bâtiments
            $batiments = Batiment::getBatiments();

            // Si le formulaire n'est pas soumis, affiche le formulaire
            require 'Vue/Register.php';
            return true;
        }
    }

    // Fonction pour gerer la connexion
    public function login()
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


        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            // Vérification du token CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Token CSRF invalide."
                ]);
                return false;
            }
            
            // Définir un code HTTP 400 (Bad Request) par défaut
            http_response_code(400);

            // On renvoie du JSON par défaut (AJAX)
            header("Content-Type: application/json");

            // Recupere les valeur du formulaire a l'aide des variable POST
            $email = $_POST['mail_utilisateur'] ?? null;
            $mots_de_passe = $_POST['mdp_utilisateur'] ?? null;

            // On verifie si le formulaire est complet
            if (empty($email) || empty($mots_de_passe)) {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Formulaire incomplet'
                ]);
                return false;
            }

            // On crée un objet UserCredentials pour gérer la connexion
            $result = UserCredentials::verifyConnection($email, $mots_de_passe);
            if ($result === true) {
                
                // Démarrage de session ABSOLUMENT EN PREMIER
                if (session_status() === PHP_SESSION_NONE) {
                    // Configurer les paramètres du cookie de session
                    session_set_cookie_params([
                        'httponly' => true,
                        'secure' => false, // à activer uniquement en HTTPS
                        'samesite' => 'Strict'
                    ]);

                    // Démarrer la session avec les paramètres de cookie pour une securite renforcee
                    session_start();
                }

                // Vérifie si l'utilisateur est déjà connecté
                if (isset($_SESSION['user'])) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => "Vous êtes déjà connecté"
                    ]);
                    return false;
                }

                $infoUser = UserCredentials::getUserData($email);
                if ($infoUser === false) {
                    // Réponse JSON avec le message d'erreur
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Erreur lors de la récupération des données utilisateur'
                    ]);
                    return false;
                }

                // Stockage des informations utilisateur en session
                $_SESSION['user'] =
                    [
                        'id' => $infoUser['Id_utilisateur'],
                        'nom' => $infoUser['nom_utilisateur'],
                        'prenom' => $infoUser['prenom_utilisateur'],
                        'email' => $infoUser['mail_utilisateur'],
                        'role_id' => $infoUser['Id_role']
                    ];

                // On définit un code HTTP 200 pour le succès
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Vous êtes connectés',
                    // Temporaire -- Dans le vrai projet ce sera différent
                    'redirect' => 'index.php?action=AccueilConnexion'
                ]);
                return true;
            } else {
                // Réponse JSON avec le message d'erreur
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Veuillez vérifier vos informations de connexion'
                ]);
                return false;
            }
        } else // En cas de requete non POST (par exemple via du GET avec l'URL)
        {
            // Affichage du formulaire de connexion si la méthode n'est pas POST
            
            require "View/B3/Login.php";

            return true;
        }
    }

    // Fonction pour gerer la deconnexion
    public function logout()
    {
        // Supprimer la condition sur la méthode HTTP
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

        $_SESSION = [];

        session_destroy();

        // Redirection facultative pour les tests
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            header("Location: ./");
            exit;
        }
    }
}