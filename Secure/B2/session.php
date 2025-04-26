<?php

// Si on est dans un test PHPUnit, on ignore cette sécurité
if (defined('PHPUNIT_RUNNING')) {
    return;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.cookie_httponly', 1); // Empêche JS d'accéder aux cookies
    ini_set('session.cookie_secure', 1); // N'active "secure" que si on est en HTTPS
    ini_set('session.cookie_samesite', 'Strict'); // Bloque les requêtes cross-site
    session_start(); // Démarre la session
} else {
    // Si la session est déjà active, on évite de modifier les paramètres
    // Vous pouvez aussi choisir de ne pas faire de modifications supplémentaires ici
}


require_once("login.php");

// =======  RENFORCEMENT PAR AGENT UTILISATEUR / IP =======
//Exemple attaque XSS, Si essaye de se connecter depuis un autre appareil ou navigatuer ça bloque
// Pour éviter le vol de session
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';//Enregistre le navigateur actuel dans la session (enrigistre par exemple Mozilla/...) chaine envoyé automatiqument pas le navigateur.
} else if ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) { //Compare le user Agent actuel a celui enregisré dans la session au début
    //SI pas bon alors ça bloque et détruit la session
    session_destroy(); 
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Session invalide']);
    exit;
}

function generateCsrfToken() {

    $csrf_token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $csrf_token; // Stocke dans la session
    $_SESSION['csrf_token_time'] = time(); // Optionnel : stockage de la timestamp pour expiration
    return $csrf_token;
}

function validateCsrfToken($token) {

    // Vérifie que le jeton envoyé correspond à celui stocké dans la session
    if (isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] == $token) {
        // Optionnel : vérifier l'expiration
        if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time'] > 3600)) { // Expires après 1 heure
            return false;
        }
        return true;
    }
    return false;
}

// ======= VÉRIF ROLE ADMIN =======
if (isset($_SESSION['id_role'])) {
    if ($_SESSION['id_role'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Accès refusé !'], JSON_UNESCAPED_UNICODE);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Rôle utilisateur inconnu.']);
    exit;
}
?>