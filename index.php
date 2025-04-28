<?php
session_start();

// Sécurisation de session
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id();
    $_SESSION['initiated'] = true;
}

// Simulation locale d'utilisateur connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_prenom'] = "Ilhan";
    $_SESSION['nom'] = "Ilhan";
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 1; // 1 = Admin, 2 = Technicien
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

// Gestion de la page d'accueil
if ($segments[0] === '' || $segments[0] === 'index.php') {
    if (!empty($_SESSION['user_role'])) {
        switch ($_SESSION['user_role']) {
            case 1:
                require 'View/B5/AccueilAdmin.php';
                break;
            case 2:
                require 'View/B5/AccueilTechnicien.php';
                break;
            default:
                error('Accès non autorisé.');
        }
    } else {
        error('Session invalide.');
    }
    exit;
}

// Gestion de routage manuel
switch ($segments[0]) {

    case 'AccueilAdmin':
        require 'View/B5/AccueilAdmin.php';
        break;

    case 'AccueilTechnicien':
        require 'View/B5/AccueilTechnicien.php';
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

    default:
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