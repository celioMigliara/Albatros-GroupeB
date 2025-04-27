<?php
session_start();

// Sécurisation minimale de la session pour éviter fixation d'identité
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id();
    $_SESSION['initiated'] = true;
}

// ----- [À retirer plus tard : simulation utilisateur connecté en local]
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_prenom'] = "Ilhan";
    $_SESSION['nom'] = "Ilhan";
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 2; // 1 = Admin, 2 = Technicien
}

// Définition d'une constante BASE_URL
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

// Autoload
require_once __DIR__ . '/vendor/autoload.php';

// ===================================
// ROUTAGE PAR ?action=...
// ===================================
if (isset($_GET['action'])) {
    $action = htmlspecialchars($_GET['action']); // sécurisation
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
    exit;
}

// ===================================
// ROUTAGE PAR URL (sans ?action=...)
// ===================================
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$uri = str_replace($scriptDir, '', $uri);
$uri = trim($uri, '/');

// Correction spéciale pour les pages d'accueil
if (str_contains($uri, 'AccueilAdmin')) {
    $uri = 'AccueilAdmin';
}
if (str_contains($uri, 'AccueilTechnicien')) {
    $uri = 'AccueilTechnicien';
}
if ($uri === 'index.php' || $uri === '') {
    if (!empty($_SESSION['user_role'])) {
        switch ($_SESSION['user_role']) {
            case 1: require 'View/B5/AccueilAdmin.php'; break;
            case 2: require 'View/B5/AccueilTechnicien.php'; break;
            default: error('Accès non autorisé.');
        }
    } else {
        error('Session invalide.');
    }
    exit;
}

// Routes spécifiques
switch ($uri) {

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

    case 'demande':
        require 'View/B2/demande_intervention.php';
        break;

    case 'recurrence':
        require_once 'Controller/B2/controllerMaintenance.php';
        accueil();
        break;

    // Routes dynamiques
    default:
        routeDynamique($uri);
        break;
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
 * Routes dynamiques
 */
function routeDynamique(string $uri) {
    if (preg_match('#^utilisateur/(\d+)$#', $uri, $matches)) {
        $_GET['id'] = (int)$matches[1];
        require 'View/B5/DetailUtilisateur.php';
    } elseif (preg_match('#^validationInscription/(\d+)$#', $uri, $matches)) {
        $_GET['id'] = (int)$matches[1];
        require 'Controller/B5/validerInscription.php';
    } else {
        http_response_code(404);
        echo '404 - Page non trouvée';
        exit;
    }
}
