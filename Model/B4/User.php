<?php
namespace Model\B4;
use PDO;

// Inclure le singleton Database global
require_once __DIR__ . '/../ModeleDBB2.php';

class User
{
    /** Récupère la connexion PDO depuis le singleton global */
    private function getConnection(): \PDO
    {
        return \Database::getInstance()->getConnection();
    }

    // ====================
    // Pagination & Totaux
    // ====================

    /** Nombre total d’utilisateurs */
    public static function countAll(): int
    {
        $pdo  = (new self)->getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM utilisateur");
        return (int) $stmt->fetchColumn();
    }

    /** Alias historique */
    public static function getTotalUtilisateurs(): int
    {
        return self::countAll();
    }

    /** Page d’utilisateurs simple (pour index) */
    public static function getPage(int $limit, int $offset): array
    {
        $pdo  = (new self)->getConnection();
        $stmt = $pdo->prepare("
            SELECT
                id_utilisateur     AS user_id,
                nom_utilisateur    AS nom,
                prenom_utilisateur AS prenom,
                mail_utilisateur   AS email,
                id_role            AS role
            FROM utilisateur
            ORDER BY nom_utilisateur
            LIMIT :lim OFFSET :off
        ");
        $stmt->bindValue(':lim',  $limit,  \PDO::PARAM_INT);
        $stmt->bindValue(':off',  $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ====================
    // Listes avancées
    // ====================

    /** Récupérer tous les utilisateurs avec leurs rôles et bâtiments */
    public static function getAllUtilisateurs(int $limit, int $offset): array
    {
        $pdo  = (new self)->getConnection();
        $stmt = $pdo->prepare("
            SELECT
                u.id_utilisateur      AS user_id,
                u.nom_utilisateur     AS nom,
                u.prenom_utilisateur  AS prenom,
                u.mail_utilisateur    AS email,
                r.nom_role            AS role,
                GROUP_CONCAT(b.nom_batiment SEPARATOR ', ') AS batiments,
                u.valide_utilisateur  AS valide,
                u.actif_utilisateur   AS actif
            FROM utilisateur u
            LEFT JOIN role r ON u.id_role = r.id_role
            LEFT JOIN travaille t ON u.id_utilisateur = t.id_utilisateur
            LEFT JOIN batiment b ON t.id_batiment = b.id_batiment
            GROUP BY u.id_utilisateur
            ORDER BY u.actif_utilisateur DESC, u.nom_utilisateur ASC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit',  $limit,  \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
 * Récupère tous les utilisateurs répétés par bâtiment
 */
public static function getAllUtilisateursParBatiment(int $limit, int $offset): array
{
    $pdo = (new self)->getConnection();
    $stmt = $pdo->prepare("
        SELECT
            u.id_utilisateur      AS user_id,
            u.nom_utilisateur     AS nom,
            u.prenom_utilisateur  AS prenom,
            u.mail_utilisateur    AS email,
            r.nom_role            AS role,
            u.actif_utilisateur   AS actif,
            b.nom_batiment        AS batiment
        FROM utilisateur u
        LEFT JOIN role      r ON u.id_role         = r.id_role
        INNER JOIN travaille t ON u.id_utilisateur = t.id_utilisateur
        INNER JOIN batiment  b ON t.id_batiment     = b.id_batiment
        ORDER BY b.nom_batiment ASC, u.nom_utilisateur ASC, u.actif_utilisateur DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit',  $limit,  \PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}



    // ====================
    // CRUD
    // ====================

    /** Récupérer un utilisateur par ID */
    public static function getById(int $id): ?array
    {
        $pdo  = (new self)->getConnection();
        $stmt = $pdo->prepare("
            SELECT
                id_utilisateur      AS user_id,
                nom_utilisateur     AS nom,
                prenom_utilisateur  AS prenom,
                mail_utilisateur    AS email,
                id_role             AS role,
                valide_utilisateur  AS valide,
                actif_utilisateur   AS actif
            FROM utilisateur
            WHERE id_utilisateur = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Alias historique */
    public static function getUtilisateurById(int $id): ?array
    {
        return self::getById($id);
    }

    /** Ajouter un nouvel utilisateur */
    public static function addUtilisateur(
        string $nom,
        string $prenom,
        string $email,
        string $motDePasse,
        int    $idRole
    ): bool {
        $pdo    = (new self)->getConnection();
        $hashed = password_hash($motDePasse, PASSWORD_DEFAULT);
        $stmt   = $pdo->prepare("
            INSERT INTO utilisateur
                (nom_utilisateur, prenom_utilisateur, mail_utilisateur, mdp_utilisateur, valide_utilisateur, actif_utilisateur, id_role)
            VALUES
                (:nom, :prenom, :email, :mdp, FALSE, TRUE, :idRole)
        ");
        return $stmt->execute([
            'nom'    => $nom,
            'prenom' => $prenom,
            'email'  => $email,
            'mdp'    => $hashed,
            'idRole' => $idRole,
        ]);
    }

    /** Désactiver un utilisateur */
    public static function desactiverUtilisateur(int $id): bool
    {
        $pdo  = (new self)->getConnection();
        $stmt = $pdo->prepare("
            UPDATE utilisateur
            SET actif_utilisateur = FALSE
            WHERE id_utilisateur = :id
        ");
        return $stmt->execute(['id' => $id]);
    }

    /** Réactiver un utilisateur */
    public static function activerUtilisateur(int $id): bool
    {
        $pdo  = (new self)->getConnection();
        $stmt = $pdo->prepare("
            UPDATE utilisateur
            SET actif_utilisateur = TRUE
            WHERE id_utilisateur = :id
        ");
        return $stmt->execute(['id' => $id]);
    }

    /** Mettre à jour un utilisateur (sans bâtiments) */
    public static function update(
        int    $id,
        string $nom,
        string $prenom,
        string $email,
        int    $role
    ): void {
        $pdo  = (new self)->getConnection();
        $stmt = $pdo->prepare("
            UPDATE utilisateur
            SET nom_utilisateur    = :nom,
                prenom_utilisateur = :prenom,
                mail_utilisateur   = :email,
                id_role            = :role
            WHERE id_utilisateur   = :id
        ");
        $stmt->execute([
            'id'     => $id,
            'nom'    => $nom,
            'prenom' => $prenom,
            'email'  => $email,
            'role'   => $role,
        ]);
    }

    /** Mettre à jour un utilisateur et ses bâtiments */
    public static function updateUtilisateur(
        int    $id,
        string $nom,
        string $prenom,
        string $email,
        int    $idRole,
        array  $batiments
    ): bool {
        $pdo = (new self)->getConnection();

        // 1) Mettre à jour les infos utilisateur
        $pdo->prepare("
            UPDATE utilisateur
            SET nom_utilisateur    = :nom,
                prenom_utilisateur = :prenom,
                mail_utilisateur   = :email,
                id_role            = :idRole
            WHERE id_utilisateur   = :id
        ")->execute([
            'id'     => $id,
            'nom'    => $nom,
            'prenom' => $prenom,
            'email'  => $email,
            'idRole' => $idRole,
        ]);

        // 2) Supprimer les anciens liens bâtiments
        $pdo->prepare("DELETE FROM travaille WHERE id_utilisateur = :id")
            ->execute(['id' => $id]);

        // 3) Réinsérer uniquement les bâtiments valides
        if (!empty($batiments)) {
            $placeholders = implode(',', array_fill(0, count($batiments), '?'));
            $check        = $pdo->prepare("SELECT id_batiment FROM batiment WHERE id_batiment IN ($placeholders)");
            $check->execute($batiments);
            $valid = $check->fetchAll(\PDO::FETCH_COLUMN);

            if ($valid) {
                $insert = $pdo->prepare("INSERT INTO travaille (id_utilisateur, id_batiment) VALUES (:id, :bid)");
                foreach ($valid as $bid) {
                    $insert->execute(['id' => $id, 'bid' => $bid]);
                }
            }
        }

        return true;
    }

    // ====================
    // Autres utilitaires
    // ====================

    /** Récupérer tous les rôles */
    public static function getAllRoles(): array
    {
        $pdo  = (new self)->getConnection();
        $stmt = $pdo->query("SELECT * FROM role");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** Récupérer les bâtiments d’un utilisateur */
    public static function getBatimentsByUtilisateur(int $idUtilisateur): array
    {
        $pdo  = (new self)->getConnection();
        $stmt = $pdo->prepare("SELECT id_batiment FROM travaille WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $idUtilisateur]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    // ====================
    // Vérifications
    // ====================

    /** Vérifier si un email existe (hors un ID donné) */
    public static function emailExists(string $email, int $excludeId = null): bool
    {
        $pdo = (new self)->getConnection();
        $sql = "SELECT COUNT(*) FROM utilisateur WHERE mail_utilisateur = :email";
        if ($excludeId !== null) {
            $sql .= " AND id_utilisateur != :id";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        if ($excludeId !== null) {
            $stmt->bindValue(':id', $excludeId, \PDO::PARAM_INT);
        }
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    /**
 * Compte le nombre d'administrateurs actifs
 */
public static function countActiveAdmins(): int
{
    $pdo  = (new self)->getConnection();
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM utilisateur 
        WHERE id_role = 1 
          AND actif_utilisateur = 1
    ");
    return (int) $stmt->fetchColumn();
}

}
