<?php

require 'vendor/autoload.php';


use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../Model/B3/db_connect.php';

class BaseTestClass extends TestCase
{
    protected function insererRoles()
    {
        $db = Database::getInstance()->getConnection();
    
        // Supprimer les utilisateurs d'abord, pour éviter la violation de la contrainte
        $db->exec("DELETE FROM utilisateur");
    
        // Ensuite, supprimer les rôles
        $db->exec("DELETE FROM role");
    
        // Réinsérer les rôles
        $db->exec("INSERT INTO `role` (`Id_role`, `nom_role`) VALUES 
            (1, 'Administrateur'), 
            (2, 'Technicien'), 
            (3, 'Utilisateur'), 
            (4, 'Systeme')");
    }

    protected function viderToutesLesTables()
    {
        $db = Database::getInstance()->getConnection();
        // Désactiver temporairement les contraintes de clé étrangère
        $db->exec("SET FOREIGN_KEY_CHECKS = 0");
        // Vider les tables enfants puis parents, et réinitialiser les auto-incréments
        $tables = [
            'travaille',
            'tache',
            'demande',
            'lieu',
            'batiment',
            'site',
            'utilisateur',
            'role'
        ];
        foreach ($tables as $table) {
            $db->exec("TRUNCATE TABLE `$table`");
            $db->exec("ALTER TABLE `$table` AUTO_INCREMENT = 1");
        }
        // Réactiver les contraintes de clé étrangère
        $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    }
}