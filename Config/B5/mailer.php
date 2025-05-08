<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Charger les variables d'environnement .env
require_once __DIR__ . '/../../vendor/autoload.php';

// On charge le fichier .env une seule fois
if (!isset($_ENV['MAIL_HOST'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

/**
 * 📩 Envoie un mail de confirmation d'inscription
 */
function envoyerMailConfirmation($destinataire, $prenom, $token) {
    $mail = new PHPMailer(true);

    try {
        // Configuration SMTP à partir des variables d'environnement
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_UTILISATEUR'];
        $mail->Password   = $_ENV['MAIL_MDP'];
        $mail->SMTPSecure = $_ENV['MAIL_CHIFFREMENT'];
        $mail->Port       = $_ENV['MAIL_PORT'];

        // Expéditeur
        $mail->setFrom($_ENV['MAIL_ADRESSE_EXPEDITEUR'], $_ENV['MAIL_NOM_EXPEDITEUR']);
        $mail->addAddress($destinataire, $prenom);

        // Contenu HTML
        $mail->isHTML(true);
        $mail->Subject = 'Confirmation de votre inscription - Institut Albatros';

        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $lien = "$scheme://$host$basePath/confirmationInscription?token=$token";
        
        $mail->Body = "
            <h3>Bonjour $prenom 👋</h3>
            <p>Votre inscription a ete validee par un administrateur.</p>
            <p>Merci de <strong>confirmer votre compte</strong> en cliquant sur le lien suivant :</p>
            <a href='$lien' style='color: #2ecc71;'>Confirmer mon inscription</a><br><br>
            <small>Ce lien expirera dans 24 heures.</small>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur PHPMailer (confirmation) : " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * ❌ Envoie un mail de refus d'inscription avec le motif
 */
function envoyerMailRefus($destinataire, $prenom, $motif) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_UTILISATEUR'];
        $mail->Password   = $_ENV['MAIL_MDP'];
        $mail->SMTPSecure = $_ENV['MAIL_CHIFFREMENT'];
        $mail->Port       = $_ENV['MAIL_PORT'];

        $mail->setFrom($_ENV['MAIL_ADRESSE_EXPEDITEUR'], $_ENV['MAIL_NOM_EXPEDITEUR']);
        $mail->addAddress($destinataire, $prenom);

        $mail->isHTML(true);
        $mail->Subject = "Refus de votre inscription - Institut Albatros";
        $mail->Body = "
            <h3>Bonjour $prenom,</h3>
            <p>Nous sommes au regret de vous informer que votre demande d'inscription a ete refusee.</p>
            <p><strong>Motif :</strong><br><em>$motif</em></p>
            <br><small>Merci pour votre comprehension.</small>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur PHPMailer (refus) : " . $mail->ErrorInfo);
        return false;
    }
}
