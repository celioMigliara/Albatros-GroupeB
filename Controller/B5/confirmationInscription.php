<?php
require_once __DIR__ . '/../../Model/B5/User.php';

// 1. Vérifie que le token est présent dans l’URL
if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("❌ Token non fourni.");
}

$token = $_GET['token'];

// 2. Recherche de l’utilisateur correspondant à ce token et s’il est encore valide
$utilisateur = User::getUtilisateurByToken($token);

if (!$utilisateur) {
    die("❌ Token invalide ou utilisateur introuvable.");
}

// Vérifie que le token n’a pas expiré
$expiration = strtotime($utilisateur['date_exp_token_utilisateur']);
$maintenant = time();

if ($expiration < $maintenant) {
    die("❌ Le lien a expiré. Merci de contacter un administrateur.");
}

// 3. Mise à jour de l'utilisateur : valide + actif + suppression du token
User::confirmerInscription($utilisateur['id_utilisateur']);
require "View/B5/confirmation.php";
?>
