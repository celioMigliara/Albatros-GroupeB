<?php
require_once __DIR__ . '/../../Model/B4/Lieu.php';
require_once __DIR__ . '/../../Model/B4/Batiment.php';
require_once __DIR__ . '/../../Model/B4/Site.php';

class LieuxController
{
    public function index()
    {
        $id_batiment = $_GET['id'] ?? null;
        $filter = $_GET['filter'] ?? 'active';

        $lieux = [];

        if ($id_batiment) {
            $batiment = Batiment::getbatimentById($id_batiment);
            $id_site = $batiment['id_site'] ?? null;
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['add_lieu'])) {
                    $nom_lieu = $_POST['lieu_name'] ?? '';
                    if (!empty($nom_lieu)) {
                        (new Lieu())->addLieu($nom_lieu, true, $id_batiment);
                        header('Location: lieux?id=' . $id_batiment);
                        exit;
                    }
                }
    
                if (isset($_POST['update_batiment'])) {
                    $nom_batiment = $_POST['batiment_name'] ?? '';
                    if (!empty($nom_batiment)) {
                        (new Batiment())->updateBatiment($id_batiment, $nom_batiment);
                        header('Location: lieux?id=' . $id_batiment);
                        exit;
                    }
                }
    
                if (isset($_POST['delete_batiment'])) {
                    (new Batiment())->softDeleteBatiment($id_batiment);
                    header('Location: batiments?id=' . $batiment['id_site']);
                    exit;
                }
    
                if (isset($_POST['activate_batiment'])) {
                    (new Batiment())->reactivateBatiment($id_batiment);
                    header('Location: lieux?id=' . $id_batiment);
                    exit;
                }

            }
            
            if ($filter === 'active') {
                $lieux = (new Lieu())->getActiveLieuByBatiment($id_batiment);
            } else {
                $lieux = (new Lieu())->getAllLieuByBatiment($id_batiment);
            }
        } else {
            if ($filter === 'active') {
                $lieux = (new Lieu())->getActiveLieuxWithBatiment();
            } else {
                $lieux = (new Lieu())->getAllLieuxWithBatiment();
            }
            $id_batiment = null;
            $batiment = null;
        }

        
        require_once __DIR__ . '/../../View/B4/Lieux/index.php';
        //$this->render('lieu/index', compact('batiment', 'lieux', 'id_batiment', 'id_site', 'filter'));
    }

    public function detail()
    {
        $id_lieu = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id_lieu) {
            header('Location: sites');
            exit;
        }

        $lieu = Lieu::getLieuById($id_lieu);
        $id_batiment = $lieu['id_batiment'] ?? null;
        $everythingActive = $lieu['actif_lieu'] && $lieu['actif_batiment'] && $lieu['actif_site'];
        $batimentAndSiteActive = $lieu['actif_batiment'] && $lieu['actif_site'];

        if (!$lieu) {
            header('Location: sites');
            exit;
        }

        // Traitement des actions POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['update_lieu'])) {
                $nom_lieu = $_POST['nom_lieu'] ?? '';
                if (!empty($nom_lieu)) {
                    Lieu::updateLieu($id_lieu, $nom_lieu);
                    header('Location: ../lieux?id=' . $id_lieu);
                    exit;
                }
            }

            if (isset($_POST['delete_lieu'])) {
                Lieu::softDeleteLieu($id_lieu);
                header('Location: ../lieux?id=' . $lieu['id_batiment']);
                exit;
            }

            if (isset($_POST['activate_lieu'])) {
                Lieu::reactivateLieu($id_lieu);
                Batiment::reactivateBatiment($id_batiment);
                Site::reactivateSite($lieu['id_site']);
                header('Location: detail?id=' . $id_lieu);
                exit;
            }
        }

        require_once __DIR__ . '/../../View/B4/Lieux_detail/index.php';
    }

}