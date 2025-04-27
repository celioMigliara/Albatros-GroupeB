<?php

require_once __DIR__ . '/../../Model/B3/UserCredentials.php';
require_once __DIR__ . '/../../Model/B3/Technicien.php';
require_once __DIR__ . '/../../Model/B3/Tache.php';
require_once __DIR__ . '/UserControlleur.php';
class TaskController
{
    // On va définir une constante pour le nombre de tâches par page
    public const TaskBaseLimit = 10;


    public function index()
    {
        if (UserCredentials::isAdminConnected())
        {
            // On les set pour la vue qui va les utiliser
            $techniciens = Technicien::getTechniciens();
            $statuts = Tache::getAllStatuts();
            $csrf_token = genererCSRFToken();
            require 'Vue/ListeTachesParTechnicien.php';
        }
        else
        {
            header("Location: ./");
        }
    }

    // Affiche la liste des tâches pour un technicien donné
    public function getTasksForTechnician()
    {
        // Définir un code HTTP 400 (Bad Request) par défaut
        http_response_code(400);

        // Démarrage de session ABSOLUMENT EN PREMIER
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            // Vérifier si l'utilisateur est connecté
            if (!UserCredentials::isAdminConnected()) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => "Veuillez vous connecter en tant qu'admin pour voir les tâches."]);
                return false;
            }

            // recueillir les paramètres de la requête
            $technicienId = $_GET['technicien_id'] ?? null;
            $start = $_GET['start'] ?? 0; // Index de départ
            $limit = $_GET['limit'] ?? self::TaskBaseLimit; // Limiter à 10 tâches par page
            $withMedia = $_GET['withMedia'] ?? 0; // Est-ce qu'on veut des médias ?
            $withStatut = $_GET['withStatut'] ?? null; 
            
            // Le statut 0 correspond à un filtre vide. Donc on peut le mettre à null
            if ($withStatut == 0)
            {
                $withStatut = null;
            }

            // Vérifier si le technicien_id est présent dans la requête
            if (empty($technicienId)) 
            {
                echo json_encode(['status' => 'error', 'message' => 'ID technicien manquant']);
                return false;
            }

            // Vérifier si le technicien existe réellement
            $technicien = new Technicien(intval($technicienId));
            if (!$technicien->exists()) {
                http_response_code(404); // Technicien non trouvé
                echo json_encode(['status' => 'error', 'message' => 'Technicien invalide ou inexistant.']);
                return false;
            }
            
            // Vérifier si le technicien est actif
            $technicien = new Technicien(intval($technicienId));
            $tasks = $technicien->getTachesForTechnicien($start, $limit, $withMedia, $withStatut);
            $totalTasks = $technicien->getTotalTaches($withMedia, $withStatut); // Nombre total de tâches

            // Vérifier si des tâches ont été trouvées
            foreach ($tasks as &$task) 
            {
                // on recupère l'id de la tâche et de la demande
                $taskId = $task['Id_tache'];
                $demandeId = $task['Id_demande'] ?? null;

                // On va créer une instance de la classe Tache pour chaque tâche
                $tache = new Tache($taskId);

                // On va récupérer les médias et le statut de la tâche
                if (!empty($taskId))
                {
                    $task['medias'] = $tache->getMediasByTacheId();
                    $task['statut'] = $tache->getTaskStatutByTacheId();
                }

                // On va récupérer les médias et le statut de la tâche
                if (!empty($demandeId)) 
                {
                    // on va set l'id de la demande
                    $tache->setDemandeId($demandeId);
                    $taskData = $tache->getTasksDataByDemandeId();

                    if (is_array($taskData)) 
                    {
                        foreach ($taskData as $key => $value) 
                        {
                            $task[$key] = $value;
                        }
                    }
                }
            }

            // Code de succès (200)
            http_response_code(200);

            //$totalTasks = count($tasks);
            echo json_encode(['status' => 'success', 'tasks' => $tasks, 'totalTasks' => $totalTasks]);
            return true;
        }

        // Si la méthode n'est pas GET, on renvoie une erreur 405 (Method Not Allowed)
        echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
        return false;
    }

    // Fonction pour mettre à jour le l'ordre des tâches
    public function updateTasksOrder()
    {

        // Démarrage de session ABSOLUMENT EN PREMIER
        if (session_status() === PHP_SESSION_NONE) {
            // Configurer les paramètres du cookie de session
            session_set_cookie_params([
                'httponly' => true,
                'secure' => false, // à activer uniquement en HTTPS
                'samesite' => 'Strict'
            ]);
            // Démarrer la session
            session_start();
        }
        
        // Génération du token AVANT TOUTE CHOSE
        $csrf_token = genererCSRFToken();
                

        // Vérification du token CSRF
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            // Vérifier si l'utilisateur est connecté
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Token CSRF invalide."
                ]);
                return false;
            }

        }
    
        // Définir un code HTTP 400 (Bad Request) par défaut
        http_response_code(400);

        // recueillir les paramètres de la requête
        $changes = $_POST['changes'] ?? null;
        if (empty($changes) || !is_array($changes))
        {
            echo json_encode([
                'status' => 'error',
                'message' => 'Format de requete invalide'
            ]);
            return false;
        }
    
        // Vérifier si le tableau de changements n'est pas vide
        foreach ($changes as $change) 
        {
            // Vérifier que chaque sous-tableau contient les clés 'id' et 'order'
            if (isset($change['id']) && isset($change['order'])) 
            {
                $id = intval($change['id']);
                $order = intval($change['order']);
    
                $tache = new Tache($id);
                $tache->updateOrder($order);
            }
        }

        // repondre un code de succès (200)
        http_response_code(200);

        echo json_encode([
            'status' => 'success',
            'message' => 'Modifications effectuées avec succès'
        ]);
        
        return true;
    }
    
}
