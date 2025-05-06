<?php

// Ne rien faire si exécuté via ligne de commande (CLI)
if (php_sapi_name() === 'cli') {
    return;
}

require_once __DIR__ . '/../../Model/UserConnectionUtils.php';

// Si on est dans un test PHPUnit, on ignore cette sécurité
if (defined('PHPUNIT_RUNNING')) {
    return;
}

// Redirection si l'utilisateur n'est pas connecté
if (!UserConnectionUtils::isUserConnected()) {
    header('Location: ' . BASE_URL . "/connexion");
    exit;
}

// ======= CONFIGURATION SÉCURITÉ DES COOKIES DE SESSION =======
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// ======= GÉNÉRATION DU TOKEN CSRF S'IL MANQUE OU EXPIRE =======
$expiration = 3600;

if (
    !isset($_SESSION['csrf_token']) ||
    !isset($_SESSION['csrf_token_expire']) ||
    time() > $_SESSION['csrf_token_expire']
) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_expire'] = time() + $expiration;
}

// ======= VÉRIFICATION DU RÔLE DE L'UTILISATEUR =======
$rolesValides = ['1', '2', '3'];
if (!isset($_SESSION['user']['role_id']) || !in_array($_SESSION['user']['role_id'], $rolesValides)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès interdit : vous n\'avez pas les droits nécessaires.']);
    exit;
}

// ======= RENFORCEMENT PAR AGENT UTILISATEUR / IP =======
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
} elseif ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
    session_destroy();
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Session invalide']);
    exit;
}
