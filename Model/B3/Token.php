<?php
require_once 'db_connect.php';

class Token
{
    // Attributs de la classe    
    private $tokenValue = null;
    private $tokenExpirationDate = null;
    public const tokenSize = 32;

    // Constructeur de la classe
    public function __construct($newTokenValue = null, $newTokenExpirationDate = null)
    {
        if (!$newTokenValue)
        {
            $newTokenValue = bin2hex(random_bytes(self::tokenSize));
        }
        if (!$newTokenExpirationDate)
        {
            $newTokenExpirationDate = date("Y-m-d H:i:s", strtotime("+1 hour"));
        }

        $this->tokenValue = $newTokenValue;
        $this->tokenExpirationDate = $newTokenExpirationDate;
    }

    // Fonction pour get un token
    public function GetToken()
    {
        return $this->tokenValue;
    }

    // Fonction pour get la date d'expiration du token
    public function isTokenValid()
    {
        if (empty($this->tokenValue))
        {
            return false;
        }

        // Vérifier la validité du token
        $userId = $this->GetUserIdWithToken();
        if ($userId === false) 
        {
            return false;
        }

        return $userId;
    }

    // Fonction pour get l'id de l'utilisateur avec le token
    public function GetUserIdWithToken()
    {
        $sql = "SELECT Id_utilisateur FROM utilisateur WHERE token_utilisateur = :token AND token_utilisateur IS NOT NULL AND NOW() < date_exp_token_utilisateur AND valide_utilisateur = 1 AND actif_utilisateur = 1";
        
        // Préparation de la requête
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare($sql);

        // Lier les paramètres avec les valeurs correspondantes
        $stmt->bindParam(':token', $this->tokenValue, PDO::PARAM_STR);
        $stmt->execute();

        // Récupérer la première colonne (Id_utilisateur) ou false si il n'y a rien
        return $stmt->fetchColumn();
    }

    // Fonction pour set le token de l'utilisateur
    public function SetUserToken($userId)
    {
        $sql = "UPDATE utilisateur 
                SET token_utilisateur = :newToken, 
                date_exp_token_utilisateur = :newDateExp
                WHERE Id_utilisateur = :userId";

        // Préparation de la requête
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare($sql);

        // Lier les paramètres avec les valeurs correspondantes
        $stmt->bindParam(':newToken', $this->tokenValue, PDO::PARAM_STR);
        $stmt->bindParam(':newDateExp', $this->tokenExpirationDate, PDO::PARAM_STR);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

        // Exécuter la requête et retourner le résultat
        return $stmt->execute();
    }

    // Fonction pour supprimer le token de l'utilisateur
    public static function ResetUserToken($userId)
    {
        $sql = "UPDATE utilisateur SET token_utilisateur = NULL, 
        date_exp_token_utilisateur = NULL 
        WHERE Id_utilisateur = :userId;";

        // Préparation de la requête
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare($sql);

        // Lier les paramètres avec les valeurs correspondantes
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

        // Exécuter la requête
        $stmt->execute();
    }
}
