<?php
require_once __DIR__ . '/../../Model/B4/Batiment.php';
require_once __DIR__ . '/../../Model/B4/Site.php';
require_once __DIR__ . '/../../Model/B4/Lieu.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class SitesController
{
    public function index()
    {
        $filter = $_GET['filter'] ?? 'active';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_site'])) {
            $nom = $_POST['site_name'] ?? '';
            if (!empty($nom)) {
                (new Site())->addSite($nom, true);
                header('Location: sites');
                exit;
            }
        }

        if ($filter === 'active') {
            $sites = (new Site())->getActiveSites();
        } else {
            $sites = Site::getAllSites();
        }

        require_once __DIR__ . '/../../View/B4/Sites/index.php';
    }

    public function import(){
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
            $file = $_FILES['excel_file'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                die('Erreur de téléchargement du fichier');
            }
    
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (strtolower($ext) !== 'xlsx') {
                die('Format de fichier non supporté');
            }
            $targetPath = __DIR__ . '/../../Assets/B4/import_temp.xlsx';
            move_uploaded_file($_FILES['excel_file']['tmp_name'], $targetPath);
    
            
            $spreadsheet = IOFactory::load($targetPath);
            $sheet       = $spreadsheet->getActiveSheet();
            $data        = $sheet->toArray();
    
            $inserted = 0;
    
            for ($i = 4; $i < count($data); $i++) {
                [$_, $siteName, $batimentName, $lieuName] = $data[$i];
    
                if (!$siteName || !$batimentName || !$lieuName) {
                    continue;
                }
    
                $siteId     = Site::addSite(trim($siteName), true);
                $batimentId = Batiment::addBatiment(trim($batimentName), $siteId, true);
                Lieu::addLieu(trim($lieuName), true, $batimentId);
    
                $inserted++;
            }
    
            header('Location: ../sites?filter=all');
            exit;
        } else {
            http_response_code(400);
            echo 'Requête invalide.';
        }
            
    }

}

