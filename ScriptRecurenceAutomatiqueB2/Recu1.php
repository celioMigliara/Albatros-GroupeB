<?php
// recurrence_cron.php

// Charge les classes nécessaires
require_once __DIR__ . '/../Model/ModeleDBB2.php';
require_once __DIR__ . '/../ScriptRecurenceAutomatiqueB2/RecurenceAutomatique.php';



try {
    // Connexion à la base de données
    $db = Database::getInstance();
    $connexion = $db->getConnection();


    // Lancement de la génération automatique
    $service = new RecurrenceService($connexion); //Instance de RecurrenceService avec la co et la date du jour.
    $logs = $service->genererDemandes();//Appelle genereDemandes qui lit toutes les récus et crée la demande si besoin.

    // Préparation des logs (pour affichage ou écriture fichier)
    $isCLI = php_sapi_name() === 'cli'; //cli en invite de commande donc cli = true, si nav alors sli = false
    $EOL = $isCLI ? PHP_EOL : '<br>'; //CLI retourne ligne terminal et false alors ligne html
    $output = '';
    foreach ($logs as $log) {
        $output .= '[' . date('Y-m-d H:i:s') . '] ' . $log . $EOL;
    }

    // Affichage si CLI ou navigateur
    echo $output;


    //Throwable = englobe toutes les exceptions et erreurs
} catch (Throwable $e) {
    $errorMsg = '[ERROR ' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . PHP_EOL;//Message d'erreur
    echo $errorMsg;
}
