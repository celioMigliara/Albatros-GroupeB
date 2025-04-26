<?php
// Si on est dans un test PHPUnit, on ignore cette sécurité
if (defined('PHPUNIT_RUNNING')) {
    return;
}

// ======= CONFIGURATION SÉCURITÉ DES COOKIES DE SESSION =======
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.cookie_httponly', 1); // Empêche JS d'accéder aux cookies
    ini_set('session.cookie_secure', 1); // N'active "secure" que si on est en HTTPS
    ini_set('session.cookie_samesite', 'Strict'); // Bloque les requêtes cross-site
    session_start(); // Démarre la session
} else {
    // Si la session est déjà active, on évite de modifier les paramètres
    // Vous pouvez aussi choisir de ne pas faire de modifications supplémentaires ici
}
// ======= GÉNÉRATION DU TOKEN CSRF S'IL MANQUE OU EXPIRE =======
$expiration = 3600;

// Génère un nouveau token s'il est manquant ou expiré
if (
    !isset($_SESSION['csrf_token']) ||
    !isset($_SESSION['csrf_token_expire']) ||
    time() > $_SESSION['csrf_token_expire']
) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_expire'] = time() + $expiration;
}

// ======= VÉRIFICATION DE CONNEXION UTILISATEUR =======
/*if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../login.php'); // Redirige vers la page de connexion si l'utilisateur n'est pas connecté
    exit;
}*/

// ======= VÉRIFICATION DU RÔLE DE L'UTILISATEUR =======
/*$rolesValides = ['1', '2', '3']; // Rôles autorisés
if (!isset($_SESSION['id_role']) || !in_array($_SESSION['id_role'], $rolesValides)) {
    http_response_code(403); // Code de réponse interdit
    echo json_encode(['success' => false, 'message' => 'Accès interdit : vous n\'avez pas les droits nécessaires.']);
    exit;
}

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../login.php'); // Redirige vers la page de connexion si l'utilisateur n'est pas connecté
    exit;
}*/

// ======= RENFORCEMENT PAR AGENT UTILISATEUR / IP =======
// Exemple attaque XSS, Si essaye de se connecter depuis un autre appareil ou navigateur ça bloque
// Pour éviter le vol de session
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? ''; // Enregistre le navigateur actuel dans la session
} elseif ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) { // Compare le user Agent actuel à celui enregisté
    session_destroy();
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Session invalide']);
    exit;
}

$_SESSION['user_id'] = 1; // L'ID de l'utilisateur fictif
$_SESSION['user_role'] =1; // Rôle de l'utilisateur fictif
?>
