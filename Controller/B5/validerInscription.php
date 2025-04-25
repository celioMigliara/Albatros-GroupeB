<?php
// Inclusion du modèle et du système d'envoi de mail
require_once __DIR__ . '/../../Model/B5/User.php';
require_once __DIR__ . '/../../Config/B5/mailer.php';

// 1. Vérifie que l'ID est bien présent dans l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID utilisateur invalide.");
}

$id = (int)$_GET['id'];

// 2. Récupère les infos de l'utilisateur
$utilisateur = User::getUtilisateurById($id);
if (!$utilisateur) {
    die("Utilisateur introuvable.");
}

// 3. Génère un token sécurisé et une date d'expiration (valable 24h)
$token = bin2hex(random_bytes(32));
$dateExpiration = date('Y-m-d H:i:s', strtotime('+1 day'));

// 4. Sauvegarde le token dans la BDD
User::setToken($id, $token, $dateExpiration);

// 5. Envoie du mail via PHPMailer (fonction personnalisée)
$success = envoyerMailConfirmation(
    $utilisateur['mail_utilisateur'],
    $utilisateur['prenom_utilisateur'],
    $token
);

// 6. Gère les erreurs d’envoi
if (!$success) {
    die("Erreur : L'envoi du mail de confirmation a échoué.");
}

// 7. Redirection avec succès
header("Location: /helha/Albatros-GroupeB/utilisateur/$id?status=validation");?>
