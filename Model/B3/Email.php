<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class Email
{
    private $mail;

    // Constructeur
    public function __construct($emailDestination, $sujet, $contenu)
    {
        $this->mail = new PHPMailer(true);

        $this->mail->isSMTP();
        $this->mail->Host       = $_ENV['MAIL_HOST'];
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $_ENV['MAIL_UTILISATEUR'];
        $this->mail->Password   = $_ENV['MAIL_MDP'];
        $this->mail->SMTPSecure = $_ENV['MAIL_CHIFFREMENT'];
        $this->mail->Port       = $_ENV['MAIL_PORT'];

        $this->mail->CharSet = 'UTF-8'; 

        // Expéditeur et destinataire
        $this->mail->setFrom($_ENV['MAIL_ADRESSE_EXPEDITEUR'], $_ENV['MAIL_NOM_EXPEDITEUR']);
        $this->mail->addAddress($emailDestination);

        // Contenu
        $this->mail->isHTML(true);
        $this->mail->Subject = $sujet;
        $this->mail->Body    = $contenu;
    }

    // Méthode pour envoyer l'email
    public function sendMail()
    {
        if ($this->mail)
        {
            try
            {
                $this->mail->send();
                return true;
            }
            catch (Exception $e) 
            {
                // On n'affiche l'erreur qu'en dev, pas en prod
                if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development')
                {
                    echo "Erreur mail : {$this->mail->ErrorInfo}";
                }

                return false;
            }
        }

        return false;
    }
}

