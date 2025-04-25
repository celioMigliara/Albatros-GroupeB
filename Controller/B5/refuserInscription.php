<?php

//Permet de "calculer" dynamiquement l'URL de base du projet
//Http ou Https, permet de gérer le cas où l'application est sur un serveur local ou en ligne
//scriptPath recup le chemin jusqu'a la racine du projet


// On coupe tout après "/Controller" ou "/View" pour revenir à la racine du projet

require_once __DIR__ . '/../../BaseUrl/Config.php';
require_once __DIR__ . '/../../Model/B5/User.php';
require_once __DIR__ . '/../../Config/B5/mailer.php'; //  Fichier contenant la fonction PHPMailer

// 1. Vérifie la présence de l'ID utilisateur et du motif du refus
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['motif'])) {
    die("Données invalides.");
}

$id = (int) $_GET['id'];
$motif = trim($_GET['motif']);

// 2. Récupère les infos de l'utilisateur ciblé
$utilisateur = User::getUtilisateurById($id);
if (!$utilisateur) {
    die("Utilisateur introuvable.");
}

// 3. Envoi du mail de refus avec PHPMailer (via mailer.php)
$success = envoyerMailRefus(
    $utilisateur['mail_utilisateur'],
    $utilisateur['prenom_utilisateur'],
    $motif
);

// (Optionnel) Affiche une erreur si le mail n’a pas pu partir
if (!$success) {
    error_log("❌ Le mail de refus n'a pas pu être envoyé à {$utilisateur['mail_utilisateur']}");
}

// 4. Supprime l'utilisateur de la base de données
User::supprimerUtilisateur($id);

// 5. Redirige vers la liste avec confirmation visuelle
header("Location: " . getBaseUrl(). "/inscriptions?status=refus");
exit;
?>
