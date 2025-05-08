<?php
require_once __DIR__ . '/../../Model/B4/Lieu.php';
require_once __DIR__ . '/../../Model/B4/LieuxDetail.php';

class LieuDetailController extends Controller
{
    public function index()
    {
        $id_lieu = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id_lieu) {
            header('Location: sites');
            exit;
        }

        $lieu = LieuDetail::getLieuById($id_lieu);

        if (!$lieu) {
            header('Location: sites');
            exit;
        }

        // Traitement des actions POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['update_lieu'])) {
                $nom_lieu = $_POST['nom_lieu'] ?? '';
                if (!empty($nom_lieu)) {
                    LieuDetail::updateLieu($id_lieu, $nom_lieu);
                    header('Location: lieudetail?id=' . $id_lieu);
                    exit;
                }
            }

            if (isset($_POST['delete_lieu'])) {
                LieuDetail::softDeleteLieu($id_lieu);
                header('Location: lieu?id=' . $lieu['id_batiment']);
                exit;
            }

            if (isset($_POST['activate_lieu'])) {
                LieuDetail::reactivateLieu($id_lieu);
                header('Location: lieudetail?id=' . $id_lieu);
                exit;
            }
        }

        require_once __DIR__ . '/../../View/B4/Lieux_detail/index.php';
    }
}
