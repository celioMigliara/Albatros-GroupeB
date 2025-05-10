<?php
require_once __DIR__ . '/../../Model/B4/Batiment.php';
require_once __DIR__ . '/../../Model/B4/Site.php';

class BatimentsController
{
    public function index()
    {
        $id_site = $_GET['id'] ?? null;
        $filter = $_GET['filter'] ?? 'active';

        $batiments = [];
        if ($id_site) {
            $site = Site::getSiteById($id_site);
            if (!$site) {
                header('Location: batiments');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['update_site'])) {
                    $nom = $_POST['site_name'] ?? '';
                    if (!empty($nom)) {
                        Site::updateSite($id_site, $nom);
                        header("Location: batiments?id=$id_site");
                        exit;
                    }
                }

                if (isset($_POST['delete_site'])) {
                    Site::softDeleteSite($id_site);
                    header("Location: batiments?id=$id_site");
                    exit;
                }

                if (isset($_POST['activate_site'])) {
                    Site::reacticateSite($id_site);
                    header("Location: batiments?id=$id_site");
                    exit;
                }

                if (isset($_POST['add_batiment'])) {
                    $nom = $_POST['batiment_name'] ?? '';
                    $id_site = $_POST['id_site'] ?? null;
                    if (!empty($nom) && !empty($id_site)) {
                        (new Batiment())->addBatiment($nom, $id_site, true);
                        header('Location: batiments?id=' . $id_site);
                        exit;
                    }
                }
            }

            if ($filter === 'active') {
                $batiments = Batiment::getActiveBatimentBySite($id_site);
            } else {
                $batiments = Batiment::getAllBatimentBySite($id_site);
            }
        } else {
            if ($filter === 'active') {
                // uniquement ceux dont le bâtiment ET le site sont actifs
                $batiments = Batiment::getActiveBatimentsWithSite();
            } else {                        // 'all' (ou toute autre valeur)
                $batiments = Batiment::getAllBatimentsWithSite();
            }
            $id_site = null;
            $site = null;
        }
        require_once __DIR__ . '/../../View/B4/Batiments/index.php';
        //$this->render('batiments/index', compact('batiments', 'id_site', 'site', 'filter'));
    }
}
