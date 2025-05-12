<?php

require_once 'Model/B3/FeuilleDeRoute.php';
require_once 'Model/B3/Technicien.php';
require_once 'Model/UserConnectionUtils.php';

class PrintController
{
    public function index()
    {    
        // Vérifie si l'utilisateur est connecté en tant qu'administrateur
        if (UserConnectionUtils::isAdminConnected())
        {
            require './View/B3/ListeImpression.php';
        }
        else
        {
            http_response_code(403);
            // On setup le message d'erreur pour la vue
            $errorMsg = new MessageErreur("Chargement de la page impossible", "Veuillez vous identifier en tant qu'administrateur");
            require './View/B3/PageErreur.php';
        }
    }

    // Fonction pour imprimer la feuille de route
    public function print()
    {
        // Vérifie si l'utilisateur est connecté en tant qu'administrateur
        if (!UserConnectionUtils::isAdminConnected())
        {
            http_response_code(403);
            // On setup le message d'erreur pour la vue
            $errorMsg = new MessageErreur("Chargement de la page impossible", "Veuillez vous identifier en tant qu'administrateur");
            require './View/B3/PageErreur.php';
            return false;
        }

        // Définir un code HTTP 400 (Bad Request) par défaut
        http_response_code(400);

        // Vérifie si l'ID du technicien est présent dans la requête
        $techId = $_GET['tech_id'] ?? null;
        if (empty($techId))
        {
            // On setup le message d'erreur pour la vue
            $errorMsg = new MessageErreur("Chargement de la page impossible", "Veuillez ajouter le paramètre tech_id en spécifiant l'id du technicien pour lequel il faut imprimer la feuille de route");
            require './View/B3/PageErreur.php';
            return false;
        }

        // Declare les variables pour la pagination
        $debutTask = $_GET['debutTask'] ?? 1; // On commence à la task 1
        $nombreDeTask = $_GET['nombreTask'] ?? 0; // 0 veut dire tout

        // Vérifie si le technicien existe et a des tâches assignées
        $technicien = new Technicien(intval($techId));
        
        // Vérifie si le technicien est valide
        $tasks = $technicien->getTachesEnCours();
        if (empty($tasks))
        {
            header("Content-Type: application/json");
            echo json_encode(["status" => "warning", "message" => "Le technicien est invalide ou n'a aucune tâche assignée."]);
            return false;
        }

        // On ajoute ici le lieu, le batiment, le site et le numéro de ticket de la demande 
        foreach ($tasks as &$task)
        {
            // On recupère l'id de la tâche et de la demande
            $taskId = $task['Id_tache'] ?? null;
            $demandeId = $task['Id_demande'] ?? null;

            // Si un des deux champs est absent, alors on skip cette tache qui est invalide
            if (empty($taskId) || empty($demandeId))
            {
                continue;
            }

            // On va créer une instance de la classe Tache pour chaque tâche
            $tache = new Tache($taskId);
            $tache->setDemandeId($demandeId);

            $taskData = $tache->getTasksDataByDemandeId();

            // Si on a bien reçu un array
            if (is_array($taskData)) 
            {
                // Alors on boucle dessus pour rajouter les infos du lieu dans chaque tache
                foreach ($taskData as $key => $value) 
                {
                    $task[$key] = $value;
                }
            }
        }

        // retourne le code HTTP 200 (OK) si tout est bon
        http_response_code(200);

        // recupère le nom et le prénom du technicien
        $techNomEtPrenom = $technicien->getTechnicienName();
        
        // set le nom et le prénom par défaut si non trouvé
        $nom = $techNomEtPrenom['nom_utilisateur'] ?? "nom absent";
        $prenom = $techNomEtPrenom['prenom_utilisateur'] ?? "prenom absent";
        
        // Vérifie si le technicien a des tâches assignées
        FeuilleDeRoute::generatePDF($tasks, $nom, $prenom, $debutTask, $nombreDeTask);
        return true;
    }
}