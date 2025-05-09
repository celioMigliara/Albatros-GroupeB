<?php

require_once 'Model/B3/UserCredentials.php';
require_once 'Model/B3/Role.php';
require_once 'Model/B3/Batiment.php';
require_once 'Model/B3/Security.php';

class AuthController
{
    // Fonction pour gerer l'inscription
    public function register()
    {
        $securityObj = new Security();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            header('Content-Type: application/json');

            // Vérification du token CSRF
            if (!$securityObj->checkCSRFToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Token CSRF invalide."
                ]);
                return false;
            }

            // Définir un code HTTP 400 (Bad Request) par défaut
            http_response_code(400);
            
            // On recupere les valeur du formulaire a l'aide des variable POST
            $nom    = $_POST['nom_utilisateur'] ?? null;
            $prenom = $_POST['prenom_utilisateur'] ?? null;
            $email  = $_POST['mail_utilisateur'] ?? null;
            $role   = $_POST['role_utilisateur'] ?? null;
            $pass   = $_POST['mdp_utilisateur'] ?? null;
            $batiments = $_POST['batiments_utilisateur'] ?? [];
            $confirmEmail = $_POST['confirmer_mail'] ?? null;
            $confirmPassword = $_POST['confirmer_mots_de_passe'] ?? null;


            // On verifie si le formulaire est complet
            if (empty($nom) || empty($prenom) || empty($email) || empty($role) || empty($pass) || empty($confirmEmail) || empty($confirmPassword)) {
                echo json_encode(['status' => 'error', 'message' => 'Le formulaire d\'inscription n\'est pas complet.']);
                return false;
            }


            // On vérifie si l'email confirmation et le mot de passe confirmation correspondent
            if ($email !== $confirmEmail) {
                echo json_encode(['status' => 'error', 'message' => 'Les emails ne correspondent pas.']);
                return false;
            }
            
            if ($pass !== $confirmPassword) {
                echo json_encode(['status' => 'error', 'message' => 'Les mots de passe ne correspondent pas.']);
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
                $allBatiments = BatimentB3::getBatiments();
                $batiments = $allBatiments ? array_column($allBatiments, 'Id_batiment') : [];

                if (empty($batiments)) {
                    echo json_encode(['status' => 'error', 'message' => "Aucun bâtiment disponible"]);
                    return false;
                }
            }
            
            // Créer l'objet ici avec les bons bâtiments
            $BatimentObj = new BatimentB3($batiments);
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
                'message' => 'Votre demande d\'inscription a bien été envoyée. Elle sera examinée par un administrateur.',
                'redirect' => BASE_URL . '/connexion'
            ]);
            return true;
        } else {

            // Cela ne fait pas sens d'accéder à la page d'inscription si on est déjà connecté
            if (UserConnectionUtils::isUserConnected())
            {
                // Code 400 (Bad request)
                http_response_code(400);

                // On setup le message d'erreur pour la vue
                $errorMsg = new MessageErreur("Chargement de la page impossible", "Veuillez vous déconnecter pour accéder à la page d'inscription");
                require 'View/B3/PageErreur.php';
                return false;
            }

            // On génére les batiments pour la page Register
            $batiments = BatimentB3::getBatiments();

            // Et le token CSRF aussi pour le formulaire
            $csrf_token = $securityObj->genererCSRFToken();
            
            // Si le formulaire n'est pas soumis, affiche le formulaire
            require 'View/B3/Register.php';
            return true;
        }
    }

    // Fonction pour gerer la connexion
    public function login()
    {
        $securityObj = new Security();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            // On renvoie du JSON par défaut (AJAX)
            header("Content-Type: application/json");

            // Vérification du token CSRF
            if (!$securityObj->checkCSRFToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Token CSRF invalide."
                ]);
                return false;
            }
            
            // Définir un code HTTP 400 (Bad Request) par défaut
            http_response_code(400);

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

            $result = UserCredentials::verifyPassword($email, $mots_de_passe);
            if ($result === true) {

                // Vérifie si l'utilisateur est déjà connecté
                if (UserConnectionUtils::isUserConnected())
                {
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

                $isRoleAdmin = Role::isSameRole($infoUser['Id_role'], Role::ADMINISTRATEUR);
                $isAdminForPageAccueil = $isRoleAdmin ? "/AccueilAdmin" : "/AccueilTechnicien";

                // On définit un code HTTP 200 pour le succès
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Vous êtes connectés',
                    'redirect' => BASE_URL . $isAdminForPageAccueil
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
            // Cela ne fait pas sens d'accéder à la page de connexion si on est déjà connecté
            if (UserConnectionUtils::isUserConnected())
            {
                // Code 400 (Bad request)
                http_response_code(400);

                // On setup le message d'erreur pour la vue
                $errorMsg = new MessageErreur("Chargement de la page impossible", "Veuillez vous déconnecter pour accéder à la page de connexion");
                require 'View/B3/PageErreur.php';
                return false;
            }

            // Génération du token CSRF pour le formulaire du login
            $csrf_token = $securityObj->genererCSRFToken();
            
            // Affichage du formulaire de connexion si la méthode n'est pas POST
            require 'View/B3/Login.php';
            return true;
        }
    }

    // Fonction pour gerer la deconnexion
    public function logout()
    {
        $securityObj = new Security();

        $_SESSION = [];

        session_destroy();

        // Redirection facultative pour les tests
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }
}