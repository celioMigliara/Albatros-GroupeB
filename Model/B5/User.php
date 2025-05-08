<?php
require_once __DIR__ . '/../ModeleDBB2.php';

/**
 * Classe User - Représente un utilisateur de l'application
 */
class User
{
    public $user_id;
    public $nom;
    private $prenom;
    private $email;
    private $mots_de_passe;
    private $inscription_valide;
    private $actif;
    private $role;

    /**
     * Constructeur principal pour créer un nouvel utilisateur
     */
    public function __construct($nom, $prenom, $email, $mots_de_passe, $role)
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->mots_de_passe = $mots_de_passe;
        $this->inscription_valide = false;
        $this->actif = false;
        $this->role = $role;
    }

    /**
     * Connexion à la base de données via le Singleton
     * @return PDO
     */
    private function getConnection()
    {
        $db = Database::getInstance();
        return $db->getConnection();
    }

    /**
     * Insère un nouvel utilisateur dans la base de données
     */
    public function insert()
    {
        $cnx = $this->getConnection();
        $requete = $cnx->prepare("INSERT INTO utilisateur(nom_utilisateur, prenom_utilisateur, mail_utilisateur, mdp_utilisateur, valide_utilisateur, actif_utilisateur, Id_role) 
                                 VALUES (:nom, :prenom, :email, :mots_de_passe, :inscription_valide, :actif, :role)");
        $requete->bindParam(':nom', $this->nom);
        $requete->bindParam(':prenom', $this->prenom);
        $requete->bindParam(':email', $this->email);
        $requete->bindParam(':mots_de_passe', $this->mots_de_passe);
        $requete->bindParam(':inscription_valide', $this->inscription_valide, PDO::PARAM_BOOL);
        $requete->bindParam(':actif', $this->actif, PDO::PARAM_BOOL);
        $requete->bindParam(':role', $this->role, PDO::PARAM_INT);
        $requete->execute();
        $this->user_id = $cnx->lastInsertId();
    }

    /**
     * Compte tous les utilisateurs en attente de validation
     */
    public static function countUtilisateursEnAttente()
    {
        $pdo = Database::getInstance()->getConnection();

        $query = "SELECT COUNT(*) as total FROM utilisateur WHERE valide_utilisateur = 0 AND actif_utilisateur = 0";
        $stmt = $pdo->prepare($query);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Récupère les utilisateurs en attente avec pagination
     */
    public static function getUtilisateursEnAttente($limit = 10, $offset = 0)
    {
        $pdo = Database::getInstance()->getConnection();

        $query = "SELECT * FROM utilisateur 
                  WHERE valide_utilisateur = 0 AND actif_utilisateur = 0 
                  LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les infos complètes d’un utilisateur via son ID
     */
    public static function getUtilisateurById($id)
    {
        $pdo = Database::getInstance()->getConnection();

        $query = "SELECT u.*, r.nom_role 
                  FROM utilisateur u
                  JOIN role r ON u.id_role = r.id_role
                  WHERE u.id_utilisateur = :id";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère la liste des bâtiments associés à un utilisateur
     */
    public static function getBatimentsAssignes($id)
    {
        $pdo = Database::getInstance()->getConnection();

        $query = "SELECT b.nom_batiment 
                  FROM travaille t
                  JOIN batiment b ON t.id_batiment = b.id_batiment
                  WHERE t.id_utilisateur = :id";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Récupère le nom d'un rôle via son ID
     */
    public static function getNomRole($idRole)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = "SELECT nom_role FROM role WHERE id_role = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $idRole, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['nom_role'] : 'Rôle inconnu';
    }

    /**
     * Retourne les IDs des utilisateurs en attente (pour navigation)
     */
    public static function getIdsUtilisateursEnAttente() {
        $pdo = Database::getInstance()->getConnection();

        $sql = "SELECT id_utilisateur FROM utilisateur WHERE valide_utilisateur = 0 AND actif_utilisateur = 0 ORDER BY id_utilisateur ASC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Enregistre le token + expiration pour un utilisateur
     */
    public static function setToken($id, $token, $expiration)
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = "UPDATE utilisateur 
                SET token_utilisateur = :token, date_exp_token_utilisateur = :expiration,
                  valide_utilisateur = 1
                WHERE id_utilisateur = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expiration', $expiration);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Récupère un utilisateur à partir de son token
     */
    public static function getUtilisateurByToken($token)
    {
        $pdo = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM utilisateur WHERE token_utilisateur = :token";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Confirme l'inscription (active le compte + supprime le token)
     */
    public static function confirmerInscription($id)
    {
        $pdo = Database::getInstance()->getConnection();
        $sql = "UPDATE utilisateur 
                SET valide_utilisateur = 1,
                    actif_utilisateur = 1,
                    token_utilisateur = NULL,
                    date_exp_token_utilisateur = NULL
                WHERE id_utilisateur = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    /**
     * Supprime un utilisateur + les entrées liées à lui
     */
    public static function supprimerUtilisateur($id) {
        $pdo = Database::getInstance()->getConnection();

        try {
            $sql1 = "DELETE FROM travaille WHERE id_utilisateur = :id";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt1->execute();

            $sql2 = "DELETE FROM utilisateur WHERE id_utilisateur = :id";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt2->execute();

        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de l'utilisateur : " . $e->getMessage());
            return false;
        }
    }

    
    
}
?>
