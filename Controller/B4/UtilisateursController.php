<?php
// 1) Charger le modèle User
require_once __DIR__ . '/../../Model/B4/User.php';
use Model\B4\User;

// 2) Charger le modèle Batiment (classe globale)
require_once __DIR__ . '/../../Model/B4/Batiment.php';
// Note : PAS de "use Batiment;" ici, on l’appellera en global

class UtilisateursController
{
    public function index()
    {
        // 1) Pagination
        $limit  = 6;
        $total  = User::countAll();
        $pages  = (int) ceil($total / $limit);
        $page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $limit;

        // on transmet aussi $tri en vue
    $tri = $_GET['tri'] ?? 'nom';

    if ($tri === 'batiment') {
        $utilisateurs = User::getAllUtilisateursParBatiment($limit, $offset);
    } else {
        $utilisateurs = User::getAllUtilisateurs($limit, $offset);
    }

    require __DIR__ . '/../../View/B4/Utilisateurs/index.php';
    }

    public function modifier()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$id) {
            header('Location: ' . BASE_URL . '/utilisateurs');
            exit;
        }

        // POST → mise à jour
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $prenom    = trim($_POST['prenom']);
            $nom       = trim($_POST['nom']);
            $email     = trim($_POST['email']);
            $role      = (int) $_POST['role'];
            $batiments = $_POST['batiments'] ?? [];

            if (User::emailExists($email, $id)) {
                $error = 'Cet email est déjà utilisé.';
            } else {
                User::updateUtilisateur($id, $nom, $prenom, $email, $role, $batiments);
                header('Location: ' . BASE_URL . '/utilisateurs');
                exit;
            }
        }

        // Chargement des données pour la vue
        $utilisateur       = User::getById($id);
        $assignedBatiments = User::getBatimentsByUtilisateur($id);

        // <<< Appel en global namespace >>>
        $allBatiments      = \Batiment::getAllBatiments();

        require __DIR__ . '/../../View/B4/Utilisateurs/modifier.php';
    }

    public function desactiver()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($id) {
            // Récupère la cible
            $target = User::getById($id);

            // Si c'est un admin unique, on bloque
            if ($target['role'] === 1 && User::countActiveAdmins() <= 1) {
                header('Location: ' . BASE_URL . '/utilisateurs?error=last_admin');
                exit;
            }

            // Sinon désactivation normale
            User::desactiverUtilisateur($id);
        }

        header('Location: ' . BASE_URL . '/utilisateurs');
        exit;
    }



    public function activer()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($id) {
            User::activerUtilisateur($id);
        }
        header('Location: ' . BASE_URL . '/utilisateurs');
        exit;
    }
}
