<?php
require_once(__DIR__ . '/../../Model/ModeleDBB2.php');

// Connexion à la base
$db = Database::getInstance()->getConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer tous les sites
if (isset($_GET['get_sites'])) {
    $stmt = $db->prepare("SELECT id_site, nom_site FROM site where actif_site != 0");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Récupérer les bâtiments pour un site donné
if (isset($_GET['site_id'])) {
    $site_id = $_GET['site_id'];
    $stmt = $db->prepare("SELECT id_batiment, nom_batiment FROM batiment WHERE id_site = ? AND actif_batiment != 0");
    $stmt->execute([$site_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Récupérer les lieux pour un bâtiment donné
if (isset($_GET['batiment_id'])) {
    $bat_id = $_GET['batiment_id'];
    $stmt = $db->prepare("SELECT id_lieu, nom_lieu FROM lieu WHERE id_batiment = ? AND actif_lieu != 0");
    $stmt->execute([$bat_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
?>
