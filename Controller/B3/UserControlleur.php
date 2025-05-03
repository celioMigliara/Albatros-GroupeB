<?php

use PHPUnit\Framework\Constraint\IsTrue;

require_once 'AuthController.php';
require_once 'PasswordController.php';
require_once 'PrintController.php';
require_once 'ProfileController.php';
require_once 'TaskController.php';
require_once 'TechnicienController.php';

class UserControlleur
{
    // Pour le dev only : 
    public function accueil()
    {
        require 'Vue/AccueilConnexion.php';
    }
    // Pour maintenir les tests intacts
    // FOnction pour la création d'un utilisateur
    public function inscription()
    {
        return (new AuthController())->register();
    }

    // Fonction pour la connexion
    public function connexion()
    {
        return (new AuthController())->login();
    }

    // Fonction pour la déconnexion
    public function deconnexion()
    {
        return (new AuthController())->logout();
    }

    // Fonction pour la réinitialisation du mot de passe
    public function ResetPassword()
    {
        return (new PasswordController())->sendResetEmail();
    }

    // Fonction pour changer le mot de passe
    public function ChangePassword()
    {
        return (new PasswordController())->ChangePassword();
    }

    // Fonction pour modifier le mot de passe
    public function ModifierProfil()
    {
        return (new ProfileController())->updateProfile();
    }

    // Fonction pour recuperer tout les techniciens
    public function getTechniciensUser()
    {
        return (new TechnicienController())->getTechniciens();
    }
}
