<?php

class Router
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = trim($url, '/');
    }

    public function run(): void
    {
        // Page d'accueil selon rôle
        if ($this->url === '') {
            if (isset($_SESSION['user_role'])) {
                if ($_SESSION['user_role'] == 1) {
                    require 'View/B5/AccueilAdmin.php';
                } elseif ($_SESSION['user_role'] == 2) {
                    require 'View/B5/AccueilTechnicien.php';
                } else {
                    echo "Accès non autorisé.";
                }
            } else {
                echo "Non connecté.";
            }
            return;
        }

        $segments = explode('/', $this->url);

        $page = $segments[0] ?? null;
        $action = $segments[1] ?? null;
        $param = $segments[2] ?? null; //Aptionnelle pour les routes dynamiques

        switch ($page) {
            /************** Accueils **************/
            case 'AccueilAdmin':
                require 'View/B5/AccueilAdmin.php';
                break;
            case 'AccueilTechnicien':
                require 'View/B5/AccueilTechnicien.php';
                break;

            /************** Administration **************/
            case 'admin':
                require 'View/B5/menuAdmin.php';
                break;

            case 'confirmationToken':
                require 'View/B5/confirmationInscription.php';
                break;

            case 'inscriptions':
            case 'confirmationInscription':
            case 'ListeInscriptions':
                require 'View/B5/ListeInscriptions.php';
                break;

            /************** B1 : Demandes **************/
            case 'ListeDemandes':
                require_once 'Controller/B1/DemandesController.php';
                $controller = new DemandesController();
                $controller->index();
                break;

            case 'demandes':
                require_once 'Controller/B1/DemandesController.php';
                $controller = new DemandesController();
                $this->handleDemandeAction($controller, $action, $param);
                break;

            /************** B1 : Tâches **************/
            case 'taches':
                require_once 'Controller/B1/TachesController.php';
                $controller = new TachesController();
                $this->handleTacheAction($controller, $action, $param);
                break;

            /************** B2 : Intervention **************/
            case 'demande':
                require 'View/B2/demande_intervention.php';
                break;

            case 'recurrence':
                require_once 'Controller/B2/controllerMaintenance.php';
                accueil();
                break;

            case 'maintenance_ajouter':
                require_once 'Controller/B2/controllerMaintenance.php';
                ajouterMaintenance();
                break;

                case 'maintenance_modifier':
                    require_once 'Controller/B2/controllerMaintenance.php';
                    if ($action) {
                        $_GET['id'] = $action; 
                        modifierMaintenance();
                    } else {
                        echo "ID non spécifié !";
                    }
                    break;

            /************** Routes dynamiques **************/
            default:
                if (preg_match('#^utilisateur/(\d+)$#', $this->url, $matches)) {
                    $_GET['id'] = $matches[1];
                    require 'View/B5/DetailUtilisateur.php';
                } elseif (preg_match('#^validationInscription/(\d+)$#', $this->url, $matches)) {
                    $_GET['id'] = $matches[1];
                    require 'Controller/B5/validerInscription.php';
                } else {
                    http_response_code(404);
                    echo "404 - Page non trouvée.";
                }
                break;
        }
    }

    private function handleDemandeAction($controller, ?string $action, ?string $param): void
    {
        switch ($action) {
            case 'index':
                $controller->index();
                break;
            case 'show':
                if ($param) {
                    $controller->show($param);
                } else {
                    echo "ID manquant pour 'show'";
                }
                break;
            case 'refuserDemande':
                $controller->refuserDemande();
                break;
            case 'updateCommentaire':
                $controller->updateCommentaire();
                break;
            case 'annulerDemande':
                $controller->annulerDemande();
                break;
            case 'updateDemande':
                $controller->updateDemande();
                break;
            default:
                echo "Action demande inconnue.";
        }
    }

    private function handleTacheAction($controller, ?string $action, ?string $param): void
    {
        switch ($action) {
            case 'create':
                $controller->create();
                break;
            case 'store':
                $controller->store();
                break;
            case 'edit':
                if ($param) {
                    $controller->edit($param);
                } else {
                    echo "ID tâche manquant pour 'edit'";
                }
                break;
            case 'update':
                $controller->update();
                break;
            case 'tasksForTechnicien':
                $controller->tasksForTechnicien();
                break;
            default:
                echo "Action tâche inconnue.";
        }
    }
}
