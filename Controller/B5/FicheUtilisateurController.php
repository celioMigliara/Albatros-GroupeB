<?php
require_once __DIR__ . '/../../Model/B5/User.php';

class FicheUtilisateurController
{
    /**
     * Affiche les détails d’un utilisateur en attente
     * @param int $id ID de l'utilisateur
     */
    public function afficherFiche($id)
    {
        // Récupération des données de l’utilisateur
        $utilisateur = User::getUtilisateurById($id);

        // Si utilisateur introuvable, redirection
        if (!$utilisateur) {
            echo "Utilisateur introuvable.";
            return;
        }

        // Si c’est un utilisateur standard, on récupère les bâtiments assignés
        $batiments = [];
        if (strtolower($utilisateur['nom_role']) === 'utilisateur') {
            $batiments = User::getBatimentsAssignes($id);
        }

        // Inclusion de la vue (avec données dispo)
        require __DIR__ . '/../View/DetailUtilisateur.php';
    }
}
