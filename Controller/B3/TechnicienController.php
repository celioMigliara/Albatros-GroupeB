<?php

require_once 'Model/B3/UserCredentials.php';
require_once 'Model/B3/Technicien.php';
require_once 'Model/UserConnectionUtils.php';

class TechnicienController
{
    // Fonction pour récupérer les techniciens
    public function getTechniciens()
    {
        // Définir un code HTTP 400 (Bad Request) par défaut
        http_response_code(400);

        // On renvoie du JSON par défaut (AJAX)
        header("Content-Type: application/json");

        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            // Vérifier si l'utilisateur est connecté en tant qu'admin
            if (!UserConnectionUtils::isAdminConnected()) {
                // Code de reponse HTTP 403 (forbidden)
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => "Veuillez vous connecter en tant qu'admin pour voir les techniciens."]);
                return false;
            }

            // Appeler la méthode pour récupérer les techniciens
            $techniciens = Technicien::getTechniciens(); // Utilise la méthode getTechniciens

            // Vérifier si des techniciens ont été trouvés
            $techniciensValides = !empty($techniciens);
            if ($techniciensValides) {
                // Retourner les techniciens en format JSON
                http_response_code(200);
                echo json_encode(['status' => 'success', 'technicians' => $techniciens]);
            } else {
                echo json_encode(['status' => 'warning', 'message' => 'Aucun technicien trouvé.']);
            }
            return $techniciensValides;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
            return false;
        }
    }
}
