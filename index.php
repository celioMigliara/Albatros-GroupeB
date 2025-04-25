<?php
session_start();

// Pour test local uniquement — à retirer plus tard
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 1;
}

define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

// Autoloader
require_once __DIR__ . '/vendor/autoload.php';

// ----------------------------
// ROUTAGE AVEC ?action=...
// ----------------------------
if (isset($_GET['action'])) {

    switch ($_GET['action']) {

        /************** B1 : Demandes **************/
        case 'index':
        case 'show':
        case 'refuserDemande':
        case 'updateCommentaire':
        case 'annulerDemande':
        case 'updateDemande':
            require_once __DIR__ . '/Controller/B1/DemandesController.php';
            $controller = new DemandesController();

            switch ($_GET['action']) {
                case 'index':
                    $controller->index();
                    break;
                case 'show':
                    if (isset($_GET['id'])) {
                        $controller->show($_GET['id']);
                    } else {
                        echo "ID manquant pour l'action 'show'";
                    }
                    break;
                case 'refuserDemande':
                    $controller->refuserDemande();
                    break;
                case 'updateCommentaire':
                    $controller->updateCommentaire();
                    break;
                case 'annulerDemande':
                    $controller->annulerDemande();
                    break;
                case 'updateDemande':
                    $controller->updateDemande();
                    break;
            }
            break;

        /************** B1 : Tâches **************/
        case 'createTask':
        case 'storeTask':
        case 'editTask':
        case 'updateTask':
        case 'tasksForTechnicien':
            require_once __DIR__ . '/Controller/B1/TachesController.php';
            $taskController = new TachesController();

            switch ($_GET['action']) {
                case 'createTask':
                    $taskController->create();
                    break;
                case 'storeTask':
                    $taskController->store();
                    break;
                case 'editTask':
                    if (isset($_GET['id_tache'])) {
                        $taskController->edit($_GET['id_tache']);
                    } else {
                        echo "ID tâche manquant pour 'editTask'";
                    }
                    break;
                case 'updateTask':
                    $taskController->update();
                    break;
                case 'tasksForTechnicien':
                    $taskController->tasksForTechnicien();
                    break;
            }
            break;

        default:
            echo "Action inconnue : " . htmlspecialchars($_GET['action']);
            break;
    }
    exit;
}

// ----------------------------
// ROUTAGE PAR URI / URI REWRITE
// ----------------------------
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$uri = str_replace($scriptName, '', $uri);
$uri = trim($uri, '/');

if ($uri === 'index.php') {
    $uri = '';
}

switch ($uri) {

    /************** Accueil **************/
    case '':
        require 'View/B5/AccueilAdmin.php';
        break;

    /************** B5 : Administration **************/
    case 'admin':
        require 'View/B5/menuAdmin.php';
        break;

    case 'confirmationToken':
        require 'View/B5/confirmationInscription.php';
        break;

    case 'confirmationInscription':
    case 'inscriptions':
    case 'ListeInscriptions':
        require 'View/B5/ListeInscriptions.php';
        break;

    /************** B1 : Demandes **************/
    case 'ListeDemandes':
        require_once __DIR__ . '/Controller/B1/DemandesController.php';
        $controller = new DemandesController();
        $controller->index();
        break;

    
    /************** Routes dynamiques **************/
    default:
        // Route de type /utilisateur/4
        if (preg_match('#^utilisateur/(\d+)$#', $uri, $matches)) {
            $_GET['id'] = $matches[1];
            require 'View/B5/DetailUtilisateur.php';

        // Route de validation d'inscription
        } elseif (preg_match('#^validationInscription/(\d+)$#', $uri, $matches)) {
            $_GET['id'] = $matches[1];
            require 'Controller/B5/validerInscription.php';

        } else {
            http_response_code(404);
            echo "Page non trouvée : $uri";
        }
        break;
}
