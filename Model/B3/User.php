<?php

abstract class UserB3
{
    // L'id du user en DB
    protected $userId = 0;

    // Setter pour le userId
    public function setUserId($newUserId)
    {
        $this->userId = $newUserId;
    }

    // Vérifie si l'userId de l'objet est valide. 
    // en DB, les keys commencent (dans notre cas) à 1
    public function isUserValid()
    {
        return $this->userId > 0;
    }

    // Getter pour le userId
    public function getUserId()
    {
        return $this->userId;
    }
}
