<?php

// Inclusion du modèle User (accès aux méthodes liées à la table utilisateur)
require_once __DIR__ . '/../Model/B5/User.php';

/**
 * Contrôleur AdminInscriptionController
 * Responsable de la gestion des inscriptions côté administrateur
 */
class AdminInscriptionController
{
    /**
     * Méthode pour afficher le nombre d'inscriptions en attente
     * Appelle le modèle User pour récupérer le nombre de comptes
     * dont la validation est encore en attente (valide = 0, actif = 0)
     * Puis appelle la vue correspondante pour affichage
     */
    public function afficherNombreInscriptions()
    {
        // Récupère le nombre d'inscriptions à valider via le modèle
        $nombre = User::countUtilisateursEnAttente();

        // Affiche la vue en lui passant la variable $nombre
        require __DIR__ . '/../View/NbInscriptions.php';
    }
}
