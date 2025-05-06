<?php

require_once 'db_connect.php';
require_once 'User.php';

class UserProfile extends UserB3
{
    public function __construct($newUserId)
    {
        $this->setUserId($newUserId);
    }
    
    public function changeProfile($champsAChanger, $params)
    {
        if (empty($champsAChanger) || empty($params)) {
            return false;
        }

        $pdo = Database::getInstance()->getConnection();
        $sql = "UPDATE utilisateur SET " . implode(', ', $champsAChanger) . " WHERE Id_utilisateur = :id";

        $stmt = $pdo->prepare($sql);

        $params[':id'] = $this->getUserId();
        return $stmt->execute($params);
    }
    public function getUserData(): array {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$this->userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
