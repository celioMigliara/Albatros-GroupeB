<?php
require_once __DIR__ . '/../ModeleDBB2.php';
class Media {
    /**
     * Ajoute un média à une tâche
     */
    public static function addMediaToTask($taskId, $mediaPath) {
        $pdo = Database::getInstance()->getConnection(); 
        $stmt = $pdo->prepare("
            INSERT INTO media (nom_media, url_media, id_tache)
            VALUES (:nom_media, :url_media, :id_tache)
        ");
        $stmt->execute([
            ':nom_media' => $mediaPath, // Nom du fichier média
            ':url_media' => $mediaPath, // URL du fichier média
            ':id_tache' => $taskId,
        ]);
    }

    /**
     * Récupère tous les médias associés à une tâche
     */
    public static function getMediaByTaskId($taskId) {
        $pdo = Database::getInstance()->getConnection(); 
        $stmt = $pdo->prepare("
            SELECT * FROM media WHERE id_tache = :id_tache
        ");
        $stmt->execute([':id_tache' => $taskId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Pour maintenir la compatibilité avec le code existant
    public static function getImagesByTaskId($taskId) {
        return self::getMediaByTaskId($taskId);
    }
    
    /**
     * Ajoute une image à une tâche (maintenu pour rétrocompatibilité)
     */
    public static function addImageToTask($taskId, $imagePath) {
        return self::addMediaToTask($taskId, $imagePath);
    }

    public static function addMediaToDemande($demandeId, $mediaPath) {
        $pdo = Database::getInstance()->getConnection(); 
        $stmt = $pdo->prepare("
            INSERT INTO media (url_media, id_demande) 
            VALUES (:url_media, :id_demande)
        ");
        
        return $stmt->execute([
            ':url_media' => $mediaPath,
            ':id_demande' => $demandeId
        ]);
    }
}