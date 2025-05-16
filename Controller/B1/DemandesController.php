<?php
require_once __DIR__ . '/../../Model/B1/Demande.php';
require_once __DIR__ . '/../../Model/B1/Taches.php';
require_once __DIR__ . '/../../Model/B1/Localite/Statut.php';
require_once __DIR__ . '/../../Model/B1/Localite/Site.php';
require_once __DIR__ . '/../../Model/B1/Localite/Batiment.php';
require_once __DIR__ . '/../../Model/B1/Utilisateur.php';
require_once __DIR__ . '/../../Model/B1/Localite/Lieu.php';
require_once __DIR__ . '/../../Model/B1/Media.php';

class DemandesController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }



        $userId = $_SESSION['user']['id'];
        $userRole = $_SESSION['user']['role_id'];


        // Récupérer les données nécessaires pour les filtres
        $statuts = Statut::getAll();
        $sites = Site::getAll();
        $batiments = Batiment::getAll();

        // Initialisation par défaut de $filters
        $filters = [
            'keywords' => $_POST['keywords'] ?? null,
            'statut' => $_POST['statut'] ?? null,
            'site' => $_POST['site'] ?? null,
            'batiment' => $_POST['batiment'] ?? null,
            'date_debut' => $_POST['date_debut'] ?? null,
            'date_fin' => $_POST['date_fin'] ?? null,
            'tri' => $_POST['tri'] ?? "desc", // Définit "desc" comme valeur par défaut
        ];

        // Vérifie si des filtres pertinents ou "tri" sont présents dans la requête POST
        if (!empty($_POST['keywords']) || !empty($_POST['statut']) || !empty($_POST['site']) || !empty($_POST['batiment']) || !empty($_POST['date_debut']) || !empty($_POST['date_fin'])) {
            // Récupère les filtres depuis la requête POST
            $filters = [
                'keywords' => $_POST['keywords'] ?? null,
                'statut' => $_POST['statut'] ?? null,
                'site' => $_POST['site'] ?? null,
                'batiment' => $_POST['batiment'] ?? null,
                'date_debut' => $_POST['date_debut'] ?? null,
                'date_fin' => $_POST['date_fin'] ?? null,
                'tri' => $_POST['tri'] ?? "desc",
            ];
        }

        // Pagination
        $demandesParPage = 10; // Nombre de demandes par page
        $pageActuelle = isset($_POST['page']) ? (int) $_POST['page'] : 1;
        $offset = ($pageActuelle - 1) * $demandesParPage;

        // Récupérer les demandes en fonction du rôle
        if ($userRole == 1) {
            // Administrateur : afficher toutes les demandes filtrées et paginées
            $demandes = DemandeB1::getFilteredWithPagination($filters, $offset, $demandesParPage);
            $totalDemandes = DemandeB1::getTotalFiltered($filters);
        } else {
            // Utilisateur simple : afficher uniquement ses demandes et celles de son bâtiment
            $demandes = DemandeB1::getByUserAndBuilding($userId, $filters, $offset, $demandesParPage);
            $totalDemandes = DemandeB1::getTotalByUserAndBuilding($userId, $filters);
        }

        // Calcul du nombre total de pages
        $totalPages = ceil($totalDemandes / $demandesParPage);

        // Charger la vue
        require_once __DIR__ . '/../../View/B1/demandes/index.php';
    }

    public static function show($id)
    {
        if (empty($id)) {
            die("ID de la demande manquant.");
        }

        $id = intval($id);

        // Vérifier si l'utilisateur a le droit d'accéder à cette demande
        $userId = $_SESSION['user']['id'];
        $userRole = $_SESSION['user']['role_id'];


        if ($userRole != 1) { // Si l'utilisateur n'est pas admin
            $demande = DemandeB1::getById($id);
            if (!$demande || $demande['id_utilisateur'] != $userId) {
                die("Accès non autorisé.");
            }
        }

        // Récupérer les données nécessaires pour les menus déroulants
        $sites = Site::getAll();
        $batiments = Batiment::getAll();
        $lieux = Lieu::getAll();

        // Récupérer la demande et les tâches associées
        $demande = DemandeB1::getById($id);
        $taches = Taches::getTachesFromDemande($id);
        $images = DemandeB1::getImagesByDemandeId($id);

        if (!$demande) {
            die("Demande introuvable.");
        }

        // Inclure la vue et passer les données
        require __DIR__ . '/../../View/B1/demandes/show.php';
    }


    public function updateCommentaire()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['commentaire_admin'])) {
            $id = intval($_POST['id']);
            $commentaire = $_POST['commentaire_admin'];

            DemandeB1::updateCommentaireAdmin($id, $commentaire);

            // Rediriger vers la page de la demande
            header('Location: listedemande/' . $id);
        } else {
            echo "Requête invalide.";
        }
    }


    public function refuserDemande()
    {
        error_log("Début de la méthode refuserDemande");

        // Récupérer l'ID de la demande
        $idDemande = $_GET['id'] ?? $_POST['id'] ?? null;
        error_log("ID demande récupéré : " . $idDemande);

        if (!$idDemande) {
            die("ID de la demande manquant.");
        }

        // Mettre à jour le statut
        $nouveauStatut = 6; // ID du statut "Annulée"
        $success = DemandeB1::updateStatut($idDemande, $nouveauStatut);
        error_log("Mise à jour statut : " . ($success ? "succès" : "échec"));

        if ($success) {
            try {
                // Récupérer les informations de la demande
                $demande = DemandeB1::getByIdforMail($idDemande);
                if (!$demande) {
                    error_log("Demande non trouvée pour l'ID: " . $idDemande);
                    header('Location: ListeDemandes');
                    exit;
                }
                error_log("Demande récupérée : " . json_encode($demande));

                // Récupérer les informations utilisateur
                if (empty($demande['id_utilisateur'])) {
                    error_log("ID utilisateur manquant dans la demande");
                    header('Location: ListeDemandes');
                    exit;
                }

                $utilisateur = Utilisateur::getById($demande['id_utilisateur']);

                if (!$utilisateur) {
                    error_log("Utilisateur non trouvé pour l'ID: " . $demande['id_utilisateur']);
                    header('Location: ListeDemandes');
                    exit;
                }
                error_log("Utilisateur récupéré : " . json_encode($utilisateur));

                // Vérifier que l'email existe
                if (empty($utilisateur['mail_utilisateur'])) {
                    header('Location: ListeDemandes');
                    exit;
                }

                // Préparer l'email
                $email = $utilisateur['mail_utilisateur'];
                $sujet = "Votre demande a été refusée";
                $message = "Bonjour " . $utilisateur['prenom_utilisateur'] . " " . $utilisateur['nom_utilisateur'] . ",\n\n";
                $message .= "Votre demande intitulée \"" . $demande['sujet_dmd'] . "\" a été refusée.\n\n";
                $message .= "Description : " . $demande['description_dmd'] . "\n\n";

                // Ajouter le commentaire admin s'il existe
                if (!empty($demande['commentaire_admin_dmd'])) {
                    $message .= "Note de l'administrateur : \n" . $demande['commentaire_admin_dmd'] . "\n\n";
                }

                $message .= "Cordialement,\nL'équipe de gestion.";

                error_log("Email préparé pour : " . $email);

                // Envoyer l'email
                $sentResult = $this->envoyerEmailRefus($email, $sujet, $message);
                error_log("Résultat de l'envoi : " . ($sentResult ? "succès" : "échec"));

            } catch (Exception $e) {
                error_log("Exception lors du traitement : " . $e->getMessage());
            }

            // Rediriger vers la liste des demandes
            header('Location: ListeDemandes');
            exit;
        } else {
            die("Erreur lors du refus de la demande.");
        }
    }

    public function updateDemande()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id']);
            $data = [
                'nom_site' => $_POST['nom_site'] ?? null,
                'nom_batiment' => $_POST['nom_batiment'] ?? null,
                'nom_lieu' => $_POST['nom_lieu'] ?? null,
                'description_dmd' => $_POST['description_dmd'] ?? null,
            ];

            // Si le formulaire ne vient que pour ajouter un média, ne pas mettre à jour les autres champs
            $isMediaUploadOnly = !empty($_FILES['media']['name']) &&
                empty($data['nom_site']) &&
                empty($data['nom_batiment']) &&
                empty($data['nom_lieu']) &&
                empty($data['description_dmd']);

            // Mettre à jour la demande seulement si ce n'est pas un simple upload de média
            if (!$isMediaUploadOnly) {
                DemandeB1::updateDemande($id, $data);
            }

            // Gérer l'upload du média
            if (!empty($_FILES['media']['name'])) {
                $uploadDir = __DIR__ . '/../../Public/Uploads/';


                // Créer le répertoire s'il n'existe pas
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Générer un nom de fichier unique
                $mediaName = time() . '_' . basename($_FILES['media']['name']);
                $targetFile = $uploadDir . $mediaName;

                // Vérifier le type de fichier
                $allowedTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/webp',
                    'video/mp4',
                    'video/webm',
                    'audio/mpeg',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'text/plain'
                ];

                $fileType = $_FILES['media']['type'];

                if (in_array($fileType, $allowedTypes) && move_uploaded_file($_FILES['media']['tmp_name'], $targetFile)) {
                    // Ajouter le média à la base de données
                    Media::addMediaToDemande($id, $mediaName);
                } else {
                    // Gérer l'erreur d'upload
                    error_log("Erreur d'upload: type non autorisé ou échec du déplacement");
                }
            }
            // Rediriger vers la page de la demande
            header('Location: ' . BASE_URL . '/listedemande/' . $id);
            exit;
        } else {
            die("Requête invalide.");
        }
    }

    private function envoyerEmailRefus($email, $sujet, $message)
    {
        $headers = "From: no-reply@projetalbatros.com\r\n";
        $headers .= "Reply-To: no-reply@projetalbatros.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // mail() retourne un boolean indiquant si l'email a été accepté pour livraison
        return mail($email, $sujet, $message, $headers);
    }

    public function annulerDemande()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idDemande = isset($_GET['id']) ? intval($_GET['id']) : 0;

            if (!$idDemande) {
                die("ID de la demande manquant.");
            }

            // Vérifier que l'utilisateur est le propriétaire de la demande
            $demande = DemandeB1::getById($idDemande);
            if (!$demande || $demande['id_utilisateur'] != $_SESSION['user']['id']) {
                die("Vous n'avez pas l'autorisation d'annuler cette demande.");
            }

            // Vérifier que le statut est bien "Nouvelle"
            if (strtolower($demande['nom_statut']) !== 'nouvelle') {
                die("Cette demande ne peut plus être annulée.");
            }

            // Statut 6 = Annulée
            $success = DemandeB1::updateStatut($idDemande, 6);

            if ($success) {
                $_SESSION['popup_annulation'] = true; //  stocke le flag
                header('Location: ' . BASE_URL . '/ListeDemandes'); // redirection vers index route
                exit;
            } else {
                die("Erreur lors de l'annulation de la demande.");
            }
        }
    }

}