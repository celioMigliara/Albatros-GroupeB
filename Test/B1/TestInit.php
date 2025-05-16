<?php

if (!defined('PHPUNIT_RUNNING')) {
    define('PHPUNIT_RUNNING', true);
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Model/ModeleDBB2.php';

function initTestDatabase()
{
    echo "initTestDatabase() appelée\n";

    $db = Database::getInstance()->getConnection();

    $db->exec("SET FOREIGN_KEY_CHECKS=0");

    $tables = ['historique', 'est', 'media', 'tache', 'demande', 'utilisateur', 'site', 'batiment', 'lieu', 'statut', 'role'];
    foreach ($tables as $table) {
        $db->exec("TRUNCATE TABLE $table");
    }

    $db->exec("SET FOREIGN_KEY_CHECKS=1");

    // Sites et structures
    $db->exec("INSERT INTO site (id_site, nom_site, actif_site) VALUES (1, 'Site Test', 1)");
    $db->exec("INSERT INTO batiment (id_batiment, nom_batiment, actif_batiment, id_site) VALUES (1, 'Bat A', 1, 1)");
    $db->exec("INSERT INTO lieu (id_lieu, nom_lieu, actif_lieu, id_batiment) VALUES (1, 'Salle 1', 1, 1)");

    // Rôles
    $db->exec("INSERT INTO role (id_role, nom_role) VALUES (1, 'Administrateur'), (2, 'Technicien')");

    // Utilisateurs
    $db->exec("INSERT INTO utilisateur (id_utilisateur, nom_utilisateur, prenom_utilisateur, mail_utilisateur, mdp_utilisateur, actif_utilisateur, id_role)
               VALUES 
               (1, 'Admin', 'Test', 'admin@test.com', 'adminpass', 1, 1),
               (2, 'Tech', 'Test', 'tech@test.com', 'techpass', 1, 2)");

    // Statuts
    $db->exec("INSERT INTO statut (id_statut, nom_statut) VALUES (1, 'Initial'), (2, 'Mis à jour')");

    // Demande
    $db->exec("INSERT INTO demande (id_demande, num_ticket_dmd, sujet_dmd, id_utilisateur, id_lieu)
               VALUES (1, 'DMD001', 'Demande test', 2, 1)");

    // ⚠️ Ajouté : entrée dans la table `est`
    $db->exec("INSERT INTO est (id_demande, id_statut, date_modif_dmd) VALUES (1, 1, NOW())");

    // Tâches
    $db->exec("INSERT INTO tache (
        id_tache, sujet_tache, description_tache, date_creation_tache,
        date_planif_tache, date_fin_tache, commentaire_technicien_tache,
        ordre_tache, id_utilisateur, id_demande
    ) VALUES
        (1, 'Tâche 1', 'Description 1', NOW(), '2025-05-09', NULL, 'RAS', 1, 2, 1),
        (2, 'Tâche 2', 'Description 2', NOW(), '2025-05-10', NULL, 'RAS', 2, 2, 1)");

    // Historique
    $db->exec("INSERT INTO historique (id_tache, id_statut, date_modif)
               VALUES (1, 1, NOW()), (2, 1, NOW())");

    // Média (exemple vide)
    $db->exec("INSERT INTO media (id_media, nom_media, url_media, id_demande, id_tache)
               VALUES (1, 'test.jpg', '/uploads/test.jpg', 1, 1)");
}
