<?php
session_start();


// Sécurisation de session
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id();
    $_SESSION['initiated'] = true;
}

// Définir BASE_URL
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

// Autoload
require_once __DIR__ . '/vendor/autoload.php';

// ===================================
// ROUTAGE
// ===================================

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$uri = str_replace($scriptDir, '', $uri);
$uri = trim($uri, '/');


// Découper en segments
$segments = explode('/', $uri);

// Les require pour les B3
require_once 'Controller/B3/UserControlleur.php';
require_once 'Controller/B3/AuthController.php';
require_once 'Controller/B3/PasswordController.php';
require_once 'Controller/B3/PrintController.php';
require_once 'Controller/B3/ProfileController.php';
require_once 'Controller/B3/TaskController.php';
require_once 'Controller/B3/TechnicienController.php';
require_once 'Model/B3/Role.php';
require_once 'Model/UserConnectionUtils.php';


// Mise à true quand la page demandée n'est pas trouvée
$pageNotFound = false;

// Gestion de routage manuel
switch ($segments[0]) 
{
    case '':
        case 'index.php':
            $userRole = UserConnectionUtils::getConnectedUserRole();
            if (empty($userRole))
            {
                header('Location: ' . BASE_URL . '/connexion');
                exit;
            }
            else
            {
                switch ($userRole) {
                    case Role::ADMINISTRATEUR:
                        header('Location: ' . BASE_URL . '/AccueilAdmin');
                        exit;
                    case Role::TECHNICIEN:
                        header('Location: ' . BASE_URL . '/AccueilTechnicien');
                        exit;
                    default:
                        error('Accès non autorisé.');
                }
            }

            break;
        
            case 'AccueilAdmin':
                if (!empty($_SESSION['user']['role_id']) && $_SESSION['user']['role_id'] == 1) {
                    require 'View/B5/AccueilAdmin.php';
                } else {
                    header('Location: ' . BASE_URL . '/connexion');
                    exit;
                }
                break;
            
            case 'AccueilTechnicien':
                if (!empty($_SESSION['user']['role_id']) && in_array($_SESSION['user']['role_id'], [2, 3])) {
                    require 'View/B5/AccueilTechnicien.php';
                } else {
                    header('Location: ' . BASE_URL . '/connexion');
                    exit;
                }
                break;
            

    case 'admin':
        require 'View/B5/menuAdmin.php';
        break;

    case 'confirmationToken':
        require 'View/B5/confirmationInscription.php';
        break;

    case 'inscriptions':
    case 'ListeInscriptions':
        require 'View/B5/ListeInscriptions.php';
        break;

    case 'ListeDemandes':
        require_once __DIR__ . '/Controller/B1/DemandesController.php';
        (new DemandesController())->index();
        break;

    

    case 'recurrence':
        require_once 'Controller/B2/controllerMaintenance.php';
        accueil();
        break;

    case 'maintenance':
        require_once 'Controller/B2/controllerMaintenance.php';
        if (isset($segments[1]) && $segments[1] === 'ajouter') {
            ajouterMaintenance();
        } elseif (isset($segments[1], $segments[2]) && $segments[1] === 'modifier') {
            $_GET['id'] = (int)$segments[2];
            modifierMaintenance();
        } else {
            accueil();
        }
        break;
       
        case 'updateDemande':
            require_once __DIR__ . '/Controller/B1/DemandesController.php';
            $DemandesController = new DemandesController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $DemandesController->updateDemande();
            }
            break;
    case 'utilisateur':
        if (isset($segments[1])) {
            $_GET['id'] = (int)$segments[1];
            require 'View/B5/DetailUtilisateur.php';
        } else {
            error('ID utilisateur manquant.');
        }
        break;

    case 'validationInscription':
        if (isset($segments[1])) {
            $_GET['id'] = (int)$segments[1];
            require 'Controller/B5/validerInscription.php';
        } else {
            error('ID d\'inscription manquant.');
        }
        break;

    case 'listedemande':
    if (isset($segments[1])) {
        $_GET['id'] = (int)$segments[1];
        require_once __DIR__ . '/Controller/B1/DemandesController.php';
        (new DemandesController())->show((int)$_GET['id']);
    } 
    break;

    case 'creerTache':
        require_once __DIR__ . '/Controller/B1/TachesController.php';
        $taskController = new TachesController();
        if (isset($segments[1])) {
            $_GET['id'] = (int)$segments[1]; // on passe l'id dans $_GET pour compatibilité
            $taskController->create();
        } else {
            error('ID de la demande manquant pour createTask.');
        }
        break;

        case 'creerTacheStore':
            require_once __DIR__ . '/Controller/B1/TachesController.php';
            $taskController = new TachesController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $taskController->store();
            } else {
                error('Méthode non autorisée pour creerTacheStore.');
            }
            break;

            case 'taches':
                require_once 'Controller/B1/TachesController.php';
                $tachesController = new TachesController();
            
                if (isset($segments[1]) && $segments[1] === 'creer' && isset($segments[2])) {
                    $_GET['id'] = (int)$segments[2];
                    $tachesController->create();
                } elseif (isset($segments[1]) && $segments[1] === 'modifier' && isset($segments[2])) {
                    $_GET['id_tache'] = (int)$segments[2];
                    $tachesController->edit($_GET['id_tache']);
                } else {
                    error("Action taches inconnue");
                }
                break;
                case 'updateTask':
                    require_once __DIR__ . '/Controller/B1/TachesController.php';
                    $taskController = new TachesController();
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $taskController->update();
                    } else {
                        error('Méthode non autorisée pour updateTask.');
                    }
                    break;

                    case 'refuserDemande':
                        require_once __DIR__ . '/Controller/B1/DemandesController.php';
                        $DemandesController = new DemandesController();
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $DemandesController->refuserDemande();
                        } 
                        break;

                        case 'updateCommentaire':
                            require_once __DIR__ . '/Controller/B1/DemandesController.php';
                            $DemandesController = new DemandesController();
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                $DemandesController->updateCommentaire();
                            } 
                            break;
                            case 'tasksForTechnicien':
                                require_once __DIR__ . '/Controller/B1/TachesController.php';
                                $taskController = new TachesController();
                                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                                    $taskController->tasksForTechnicien();
                                }
                                break;
                              
                                case 'demande':
                                    require 'View/B2/demande_intervention.php';
                                    break;
                        
    case 'action': // Cas spécial pour les anciennes actions ?action=...
        if (isset($_GET['action'])) {
            routeAction(htmlspecialchars($_GET['action']));
        } else {
            error('Action manquante.');
        }
        break;

    // B3 Use Cases
    // Authentification
    case 'inscription':
        (new AuthController())->register();
        break;

    case 'connexion':
        (new AuthController())->login();
        break;

    case 'deconnexion':
        (new AuthController())->logout();
        break;

    // Mot de passe
    case 'motdepasse':
        if (!isset($segments[1])) 
        {
            $pageNotFound = true;
            break;
        }

        switch ($segments[1]) {
            case 'reset': 
                (new PasswordController())->sendResetEmail();
                break;
            case 'changer':
                (new PasswordController())->ChangePassword();
                break;
            default:
            $pageNotFound = true;
            break;
        }
        break;

    // Profil
    case 'profil':
        if (!isset($segments[1])) {
            // Appel à updateProfile en GET pour afficher ModifierProfil.php
            (new ProfileController())->updateProfile(); 
        } elseif ($segments[1] === 'modifier') {
            (new ProfileController())->updateProfile(); // Même méthode gère POST
        } else {
            $pageNotFound = true;
        }
        break;

    // Dans le projet B3, c'était le endpoint 'taches'
    // mais il est déjà utilisé au dessus
    case 'tasks':
        (new TaskController())->getTasksForTechnician();
        break;

    // Feuille de route
    case 'feuillederoute':
        if (!isset($segments[1])) {
            // Si aucun segment secondaire, afficher l'accueil de la feuille de route
            (new PrintController())->index();
        } else {
            switch ($segments[1]) {
                case 'imprimer':
                    if (isset($segments[2])) {
                        $_GET['tech_id'] = (int)$segments[2]; // injection manuelle de l'ID
                    }
                    (new PrintController())->print();
                    break;
                case 'ordre':
                    if (isset($segments[2]) && $segments[2] === 'update') {
                        (new TaskController())->updateTasksOrder();
                    } else {
                        $pageNotFound = true;
                    }
                    break;
                case 'liste':
                    if (isset($segments[2])) {
                        switch ($segments[2]) {
                            case 'taches':
                                (new TaskController())->index();
                                break;
                            case 'impression':
                                (new PrintController())->index();
                                break;
                            case 'techniciens':
                                (new TechnicienController())->getTechniciens();
                                break;
                            default:
                                $pageNotFound = true;
                                break;
                        }
                    } else {
                        $pageNotFound = true;
                    }
                    break;
                default:
                    $pageNotFound = true;
                    break;
            }
        }
        break;
        
    default:
    $pageNotFound = true;
    break;
}

if ($pageNotFound)
{
    http_response_code(404);
    echo "404 - Page non trouvée : " . htmlspecialchars($uri);
    exit;
}

// ===================================
// Fonctions utilitaires
// ===================================

/**
 * Gestion simple d'erreur affichable
 */
function error(string $message) {
    http_response_code(400);
    echo htmlspecialchars($message);
    exit;
}

/**
 * Routage des actions (ancienne logique par ?action=...)
 */
function routeAction(string $action) {
    switch ($action) {
        // ---- Demandes
        case 'index':
        case 'show':
        case 'refuserDemande':
        case 'updateCommentaire':
        case 'annulerDemande':
        case 'updateDemande':
            require_once __DIR__ . '/Controller/B1/DemandesController.php';
            $controller = new DemandesController();
            match ($action) {
                'index' => $controller->index(),
                'show' => isset($_GET['id']) ? $controller->show((int)$_GET['id']) : error('ID manquant pour show'),
                'refuserDemande' => $controller->refuserDemande(),
                'updateCommentaire' => $controller->updateCommentaire(),
                'annulerDemande' => $controller->annulerDemande(),
                'updateDemande' => $controller->updateDemande(),
            };
            break;

        // ---- Tâches
        case 'createTask':
        case 'storeTask':
        case 'editTask':
        case 'updateTask':
        case 'tasksForTechnicien':
            require_once __DIR__ . '/Controller/B1/TachesController.php';
            $taskController = new TachesController();
            match ($action) {
                'createTask' => $taskController->create(),
                'storeTask' => $taskController->store(),
                'editTask' => isset($_GET['id_tache']) ? $taskController->edit((int)$_GET['id_tache']) : error('ID manquant pour editTask'),
                'updateTask' => $taskController->update(),
                'tasksForTechnicien' => $taskController->tasksForTechnicien(),
            };
            break;

        // ---- Maintenance (B2)
        case 'maintenance_ajouter':
            require_once 'Controller/B2/controllerMaintenance.php';
            ajouterMaintenance();
            break;

        case 'maintenance_modifier':
            require_once 'Controller/B2/controllerMaintenance.php';
            modifierMaintenance();
            break;

           

        default:
            error("Action inconnue : $action");
    }
    
}
?>