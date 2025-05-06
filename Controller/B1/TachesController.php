<?php
require_once __DIR__ . '/../../Model/B1/Taches.php';
require_once __DIR__ . '/../../Model/B1/Localite/Site.php';
require_once __DIR__ . '/../../Model/B1/Localite/Batiment.php';
require_once __DIR__ . '/../../Model/B1/Localite/Statut.php';
require_once __DIR__ . '/../../Model/B1/Localite/Lieu.php';
require_once __DIR__ . '/../../Model/B1/Utilisateur.php';
require_once __DIR__ . '/../../Model/B1/Media.php';
require_once __DIR__ . '/../../Model/B1/Demande.php';  // Nécessaire pour updateStatut/recalcDemandStatus

date_default_timezone_set('Europe/Paris');

class TachesController {
    public function create() {
        // Récupérer les données nécessaires pour le formulaire
        $id_demande = $_GET['id'] ?? 0;
        $demande = DemandeB1::getById($id_demande);
        $statuts = Statut::getAll();
        $sites = Site::getAll();
        $batiments = Batiment::getAll();
        $lieux = Lieu::getAll();
        $techniciens = Utilisateur::getTechniciens(); // Récupérer les techniciens

        // Récupérer l'ID de la demande si fourni
        $idDemande = isset($_GET['id']) ? intval($_GET['id']) : null;

        // Récupérer les informations de la demande liée
        $demande = DemandeB1::getById($idDemande);
        if (!$demande) {
            die("Demande introuvable.");
        }

        

        // Charger la vue de création de tâche
        require_once __DIR__ . '/../../View/B1/taches/taches.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                var_dump($_POST);
                var_dump($_FILES);
                
            }

            // Récupérer les données du formulaire
            $data = [
                'nom_tache'  => $_POST['nom_tache'] ?? null,
                'technicien' => !empty($_POST['technicien']) ? $_POST['technicien'] : null,
                'date'       => $_POST['date'] ?? null,
                'statut'     => $_POST['statut'] ?? null,
                'site'       => $_POST['site'] ?? null,
                'batiment'   => $_POST['batiment'] ?? null,
                'lieu'       => $_POST['lieu'] ?? null,
                'description'=> $_POST['description'] ?? null,
                'id_demande' => $_POST['id_demande'] ?? null,
            ];

      

            // Ajouter l'heure actuelle à la date fournie par l'utilisateur
            if (!empty($data['date'])) {
                $data['date'] .= ' ' . date('H:i:s');
            }

            // Vérifier que les champs obligatoires sont remplis
            if (empty($data['nom_tache']) || empty($data['date']) || empty($data['id_demande'])) {
                die("Les champs obligatoires ne sont pas remplis.");
            }

            // Gérer l'upload du média
            $mediaPath = null;
            if (!empty($_FILES['image']['name'])) {
                $uploadDir = __DIR__ . '/../../Public/Uploads/';
                $mediaName = basename($_FILES['image']['name']);
                $targetFile = $uploadDir . $mediaName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $mediaPath = $mediaName;
                } else {
                    die("Erreur lors de l'upload du média.");
                }
            }

            // Insérer la tâche
            $taskCreated = Taches::createTask($data);

            if ($taskCreated) {
                // Récupérer l'ID de la tâche créée
                $taskId = Taches::getTaskIdByUniqueData($data);

                if (!$taskId) {
                    die("Erreur : impossible de récupérer l'ID de la tâche créée.");
                }

                // Si un média a été uploadé, insérer dans la table `media`
                if ($mediaPath) {
                    Media::addMediaToTask($taskId, $mediaPath);
                }

                 // Recalculer le statut de la demande
                 DemandeB1::recalcDemandStatus($data['id_demande']);

                // Rediriger vers la page show.php avec l'ID de la demande
                header('Location: ' . BASE_URL . '/listedemande/' . $data['id_demande']);
                exit;
            } else {
                die("Erreur lors de la création de la tâche.");
                }
            }
        }

    public function edit($idTache) {
        if (!$idTache) {
            die("ID de la tâche manquant ou invalide.");
        }

        // Récupérer les données de la tâche
        $tache = Taches::getTacheById($idTache);
        if (!$tache) {
            die("Tâche introuvable.");
        }

        // Récupérer les données nécessaires pour le formulaire
        $id_demande = $_GET['id'] ?? 0;
        $demande = DemandeB1::getById($id_demande);
        $statuts = Statut::getAll();
        $sites = Site::getAll();
        $batiments = Batiment::getAll();
        $lieux = Lieu::getAll();
        $techniciens = Utilisateur::getTechniciens();
        $images = Media::getMediaByTaskId($idTache); // Récupérer les médias associés à la tâche

        // Charger la vue avec les données
        require_once __DIR__ . '/../../View/B1/taches/modifierTache.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_tache' => $_POST['id_tache'],
                'nom_tache' => $_POST['nom_tache'],
                'technicien' => $_POST['technicien'] ?? null,
                'date_planif_tache' => $_POST['date_planif_tache'],
                'statut' => $_POST['statut'],
                'id_demande' => $_POST['id_demande'],
                'description' => $_POST['description'] ?? null,
                'site' => $_POST['site'] ?? null,
                'batiment' => $_POST['batiment'] ?? null,
                'lieu' => $_POST['lieu'] ?? null,
                'commentaire_technicien' => $_POST['commentaire_technicien'] ?? null, // Nouveau champ
            ];
    
            // Gérer l'upload de nouveaux médias si présent
            if (!empty($_FILES['media']['name'])) {
                $uploadDir = __DIR__ . '/../../Public/Uploads/';
                
                // Créer le répertoire s'il n'existe pas
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $mediaName = time() . '_' . basename($_FILES['media']['name']);
                $targetFile = $uploadDir . $mediaName;
    
                if (move_uploaded_file($_FILES['media']['tmp_name'], $targetFile)) {
                    // Ajouter le média à la base de données
                    Media::addMediaToTask($data['id_tache'], $mediaName);
                }
            }
    
            $success = Taches::updateTask($data);
            if ($success) {
                if ($_SESSION['user']['role_id'] == 2) { // Technicien
                    header('Location: ' . BASE_URL . '/tasksForTechnicien'); 
                } else { // Admin
                    header('Location: ' . BASE_URL . '/listedemande/' . $data['id_demande']);
                }
                exit;
            } else {
                die("Erreur lors de la mise à jour de la tâche.");
            }
        }
    }

    public function tasksForTechnicien() {
        // Vérifier la connexion
        if (!isset($_SESSION['user']['id'])) {
            die("Utilisateur non connecté.");
        }
        $technicienId = $_SESSION['user']['id'];

     

        // Récupérer toutes les tâches assignées à ce technicien
        $tasks = Taches::getTasksByTechnicien($technicienId);

        // Afficher ces tâches via une vue dédiée
        require_once __DIR__ . '/../../View/B1/taches/tachesTechnicien.php';
    }
}
