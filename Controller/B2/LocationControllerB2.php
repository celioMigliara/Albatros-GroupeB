<?php
require_once(__DIR__ . "/../../Secure/B2/session_secureB2.php");
require_once(__DIR__ . '/../../Model/ModeleDBB2.php');

function fetchAll(PDO $pdo, string $sql): array {
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function getLocationJson(): string {
    $pdo = Database::getInstance()->getConnection(); // Utilise l'instance partagée
    $data = [
        'sites' => fetchAll($pdo, "SELECT id_site AS id, nom_site AS nom FROM site WHERE actif_site = 1 ORDER BY nom_site"),
        'batiments' => fetchAll($pdo, "SELECT id_batiment AS id, nom_batiment AS nom, id_site FROM batiment WHERE actif_batiment = 1 ORDER BY nom_batiment"),
        'lieux' => fetchAll($pdo, "SELECT id_lieu AS id, nom_lieu AS nom, id_batiment FROM lieu WHERE actif_lieu = 1 ORDER BY nom_lieu")
    ];
    return json_encode($data, JSON_UNESCAPED_UNICODE);
}


// Émission de la réponse uniquement si appelé directement via navigateur
//API serveur et cli si c est executé en smd donc intersesant pour les test 
//S execute pas si on fait des tests 
//Chemin du ficheir actuel 
//extrait nom fichier 
if (PHP_SAPI !== 'cli' && basename(__FILE__) === basename($_SERVER["SCRIPT_FILENAME"])) {
    header("Content-Type: application/json");
    echo getLocationJson();
}
