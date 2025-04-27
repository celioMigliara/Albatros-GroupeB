<?php

    require_once(__DIR__ . '/../../model/B2/demande.php');
    require_once(__DIR__ .'/../../Secure/B2/session.php');
    
function accueil()
{
    try {

        //Instance de connexion
        $pdo = Database::getInstance()->getConnection();

        
       // Requête SQL pour récupérer les données
       $query = "
       SELECT r.id_recurrence, r.sujet_reccurrence, r.date_anniv_recurrence, s.nom_site, b.nom_batiment,s.id_site
       FROM recurrence r
       JOIN lieu l ON r.id_lieu = l.id_lieu
       JOIN batiment b ON l.id_batiment = b.id_batiment
       JOIN site s ON b.id_site = s.id_site
       ";
       
       // Utiliser la connexion à la base de données à travers le modèle
       $stmt = $pdo->prepare($query);
       $stmt->execute();

       // Récupérer tous les résultats
       $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
       require __DIR__ . '/../../View/B2/Liste_maintenance.php';
        } catch (PDOException $e) {
        $msgError = 'Echec de la connexion : ' .  $e->getMessage();
    }
}

function ajouterMaintenance() {


    $pdo = Database::getInstance()->getConnection();

    $recurrenceModel = new RecurrenceModel($pdo);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            http_response_code(403);
            echo "Token CSRF invalide.";
            exit;
        }

        // Récupération des données
        $sujet = $_POST["titre"] ?? "";
        $description = $_POST["desc_maint"] ?? "";
        $date_anniversaire = $_POST["date_anniversaire"] ?? "";
        $frequence = $_POST["frequence"] ?? 0;
        $unite_freq = $_POST["periode"] ?? "";
        $id_lieu = $_POST["lieu"] ?? "";
        $valeur_rappel = $_POST["delai"] ?? null;
        $unite_rappel = $_POST["periode_delai"] ?? null;

        try {
            $result = $recurrenceModel->ajouterRecurrence(
                $sujet, $description, $date_anniversaire,
                $frequence, $valeur_rappel, $id_lieu,
                $unite_freq, $unite_rappel
            );

            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    showPopup(" . json_encode($result["message"]) . ", " . ($result["success"] ? "false" : "true") . ");
                });
            </script>";

        } catch (Exception $e) {
            echo "<p style='color: red;'>Erreur : " . $e->getMessage() . "</p>";
        }

    } 
    require "View/B2/formulaire_ajout.php";
    
}

function modifierMaintenance() {

    $pdo = Database::getInstance()->getConnection();

        
        $recurrenceModel = new RecurrenceModel($pdo);
        
        if (!isset($_GET['id'])) {
            die("ID non spécifié !");
        }
        
        $id_maintenance = $_GET['id'];
        $maintenance = $recurrenceModel->getById($id_maintenance);
        
        if (!$maintenance) {
            die("Maintenance introuvable !");
        }
    
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_mainte'])) {
            // Récupération des valeurs du formulaire
            $sujet = $_POST['titre'];
            $description = $_POST['desc_maint'];
            $dateAnniv = $_POST['date_anniversaire'];
            $frequence = $_POST['frequence'] ?? 0;
            $rappel = $_POST['delai'];
            $Lieu = $_POST['lieu'];
            $uniteFrequence = $_POST['periode'];
            $uniteRappel = $_POST['periode_delai'];

            if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                echo "Token CSRF invalide.";
                exit;
            }
        
            // Appel de la méthode update() du modèle
            $result = $recurrenceModel->update(
                $id_maintenance,
                $sujet, 
                $description, 
                $dateAnniv, 
                $frequence,
                $rappel, 
                $Lieu, 
                $uniteFrequence, 
                $uniteRappel
            );
    
            if ($result["success"]) {
                $_SESSION['popup_message'] = $result["message"]; // on passe le message en session
                $_SESSION['popup_success'] = true;
                header('Location: ' . BASE_URL . '/recurrence');
                exit;
            } else {
                $_SESSION['popup_message'] = $result["message"];
                $_SESSION['popup_success'] = false;
                header('Location: ' . BASE_URL . '/recurrence');
                exit;
            }

        }
    
        // Maintenant qu'on a toutes les infos, on charge la vue
        require "View/B2/formulaire_mis-a-jour.php";
}
?>