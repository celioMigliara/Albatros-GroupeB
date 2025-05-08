<?php

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
        // Connexion à la base de données
        $db = Database::getInstance()->getConnection();
    
        // Désactiver temporairement les vérifications des clés étrangères
        $db->exec("SET foreign_key_checks = 0;");
    
        // Vider explicitement chaque table une par une
        $db->exec("TRUNCATE TABLE `batiment`;");
        $db->exec("TRUNCATE TABLE `demande`;");
        $db->exec("TRUNCATE TABLE `est`;");
        $db->exec("TRUNCATE TABLE `historique`;");
        $db->exec("TRUNCATE TABLE `lieu`;");
        $db->exec("TRUNCATE TABLE `media`;");
        $db->exec("TRUNCATE TABLE `recurrence`;");
        $db->exec("TRUNCATE TABLE `role`;");
        $db->exec("TRUNCATE TABLE `site`;");
        $db->exec("TRUNCATE TABLE `statut`;");
        $db->exec("TRUNCATE TABLE `tache`;");
        $db->exec("TRUNCATE TABLE `travaille`;");
        $db->exec("TRUNCATE TABLE `unite`;");
        $db->exec("TRUNCATE TABLE `utilisateur`;");
    
        // Réactiver les vérifications des clés étrangères
        $db->exec("SET foreign_key_checks = 1;");
    }
}