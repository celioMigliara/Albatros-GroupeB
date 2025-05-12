<?php

require_once 'Model/B3/UserCredentials.php';
require_once 'Model/B3/Technicien.php';
require_once 'Model/B3/Tache.php';
require_once 'Model/B3/Security.php';
require_once 'Model/B3/MessageErreur.php';
require_once 'Model/UserConnectionUtils.php';

class TaskController
{
    // On va définir une constante pour le nombre de tâches par page
    public const TaskBaseLimit = 10;

    public function index()
    {
        if (UserConnectionUtils::isAdminConnected())
        {
            // On les set pour la vue qui va les utiliser
            $techniciens = Technicien::getTechniciens();
            $statuts = Tache::getAllStatuts();

            // Générer le token CSRF pour la liste des taches
            $securityObj = new Security();
            $csrf_token = $securityObj->genererCSRFToken();
            require 'View/B3/ListeTachesParTechnicien.php';
        }
        else
        {
            http_response_code(403);
            
            $errorMsg = new MessageErreur("Chargement de la page impossible", "Il faut être connecté en tant qu'administrateur");
            require 'View/B3/PageErreur.php';
            exit();
        }
    }

    // Affiche la liste des tâches pour un technicien donné
    public function getTasksForTechnician()
    {
        // Définir un code HTTP 400 (Bad Request) par défaut
        http_response_code(400);

        // On renvoie du JSON par défaut (AJAX)
        header("Content-Type: application/json");

        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            // Vérifier si l'utilisateur est connecté
            if (!UserConnectionUtils::isAdminConnected()) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => "Veuillez vous connecter en tant qu'admin pour voir les tâches."]);
                return false;
            }

            // Recueillir les paramètres de la requête
            $technicienId = $_GET['technicien_id'] ?? null;
            
            // Vérifier que le technicien ID est présent
            if (empty($technicienId)) 
            {
                echo json_encode(['status' => 'error', 'message' => 'ID technicien manquant']);
                return false;
            }

            // Page de départ (offset)
            $start = $_GET['start'] ?? 0; 

            // Limiter à 10 tâches par page de base si la variable d'env n'est pas SET
            $baseLimit = $_ENV['GET_TASK_LIMIT'] ?? self::TaskBaseLimit;
            
            // La limite demandée par le client, si présente. 
            $clientLimit = $_GET['limit'] ?? $baseLimit; 

            // On restraint la limite de taches à trouver par la base limit
            $limit = min($clientLimit, $baseLimit);

            // Vérifier si le technicien existe réellement
            $technicien = new Technicien(intval($technicienId));
            if (!$technicien->exists()) {
                http_response_code(400); // Technicien non trouvé
                echo json_encode(['status' => 'error', 'message' => 'Technicien invalide ou inexistant.']);
                return false;
            }
            
            // On récupère les taches avec un certain offset et une limite. 
            // On récupère aussi le nombre total de tâches sans distinction pour le statut
            $tasks = $technicien->getTachesForTechnicien($start, $limit);
            $totalTasks = $technicien->getTotalTaches(); 

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

            echo json_encode(['status' => 'success', 'tasks' => $tasks, 'totalTasks' => $totalTasks]);
            return true;
        }

        // Si la méthode n'est pas GET, on renvoie une erreur
        echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
        return false;
    }

    // Fonction pour mettre à jour le l'ordre des tâches
    public function updateTasksOrder()
    {
        // Générer le token CSRF pour la liste des taches
        $securityObj = new Security();
        
        // On renvoie du JSON par défaut (AJAX)
        header("Content-Type: application/json");

        // Vérification du token CSRF
        if (!$securityObj->checkCSRFToken($_POST['csrf_token'] ?? '')) {
            
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => "Token CSRF invalide."
            ]);
            return false;
        }

        // Vérifier si l'utilisateur est connecté
        if (!UserConnectionUtils::isAdminConnected()) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => "Veuillez vous connecter en tant qu'admin pour voir les tâches."]);
            return false;
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
    
        $returnValue = true;
        foreach ($changes as $change) 
        {
            // Vérifier que chaque sous-tableau contient les clés 'id' et 'order'
            if (isset($change['id']) && isset($change['order'])) 
            {
                // On récupère les données
                $id = intval($change['id']);
                $order = intval($change['order']);
    
                // On met à jour l'ordre de la tache
                $tache = new Tache($id);
                $returnValue &= $tache->updateOrder($order);
            }
        }

        if ($returnValue)
        {
            http_response_code(200);

            echo json_encode([
                'status' => 'success',
                'message' => 'Modifications effectuées avec succès'
            ]);
            
            return true;
        }
        else
        {
            http_response_code(500);

            echo json_encode([
                'status' => 'error',
                'message' => "Erreur : la modification de l'ordre des tâches n'a pas pu être effectuée. Un problème est survenu lors de la mise à jour des données."
            ]);
            
            return false;
        }
    }  
}
