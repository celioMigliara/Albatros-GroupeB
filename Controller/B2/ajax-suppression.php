<?php
require(__DIR__ . '/../../Model/B2/demande.php');
require_once(__DIR__ .'/../../Secure/B2/session.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $id = intval($_POST["id"]);
    
    // Vérifie le token CSRF
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Token CSRF invalide.']);
        exit;
    }

    try{
      // mets true si tu veux utiliser la base de test
      $recurrenceModel = new RecurrenceModel(Database::getInstance()->getConnection());
    
        $result = $recurrenceModel->delete($id);
    
        echo json_encode([
            'success' => $result["success"],
            'message' => $result["message"]
        ]);
    }catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
    }
}