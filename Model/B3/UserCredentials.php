<?php

require_once __DIR__ . '/../ModeleDBB2.php';
require_once 'Role.php';

class UserCredentials
{
    public static $LongueurMinimumPassword = 8;
    public static $LongueurMaximumPassword = 64;

    public $user_id = -1;
    private $nom;
    private $prenom;
    private $email;
    private $mot_de_passe;
    private $inscription_valide;
    private $actif;
    private $role;

    // Constructeur de la classe
    public function __construct($nom, $prenom, $email, $mot_de_passe, $role)
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->mot_de_passe = $mot_de_passe;
        $this->role = $role;
        $this->inscription_valide = false;
        $this->actif = false;
    }

    /* ========================== getter et setter pour le champ nom ========================== */
    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /* ========================== getter et setter pour le champ prenom ========================== */
    public function getPrenom()
    {
        return $this->prenom;
    }

    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    /* ========================== getter et setter pour le champ email ========================== */
    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    /* ========================== getter et setter pour le champ mot de passe ========================== */
    public function getMotDePasse()
    {
        return $this->mot_de_passe;
    }
    public function setMotDePasse($mot_de_passe)
    {
        $this->mot_de_passe = $mot_de_passe;
    }

    /* ========================== getter et setter pour le champ role ========================== */
    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }


    /* ========================== getter et setter pour le champ inscription_valide ========================== */
    public function getInscriptionValide()
    {
        return $this->inscription_valide;
    }

    public function setInscriptionValide($inscription_valide)
    {
        $this->inscription_valide = $inscription_valide;
    }

    /* ========================== getter et setter pour le champ actif ========================== */
    public function getActif()
    {
        return $this->actif;
    }
    public function setActif($actif)
    {
        $this->actif = $actif;
    }


    /* ========================== getter et setter pour le user_id ========================== */
    public function getUserId()
    {
        return $this->user_id;
    }
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    // Vérifie si l'utilisateur est connecté
    public static function isAdminConnected()
    {
        if (session_status() == PHP_SESSION_NONE) {
            // Configurer les paramètres du cookie de session
            session_set_cookie_params([
                'httponly' => true,
                'secure' => false, // à activer uniquement en HTTPS
                'samesite' => 'Strict'
            ]);

            // Démarrer la session
            session_start();
        }

        $roleId = $_SESSION['user']['role_id'] ?? null;
        if (empty($roleId))
        {
            return false;
        }

        return Role::isSameRole($roleId, Role::ADMINISTRATEUR);
    }

    // Vérifie si l'utilisateur est connecté
    public static function isUserConnected()
    {
        if (session_status() == PHP_SESSION_NONE) {
            // Configurer les paramètres du cookie de session
            session_set_cookie_params([
                'httponly' => true,
                'secure' => false, // à activer uniquement en HTTPS
                'samesite' => 'Strict'
            ]);

            // Démarrer la session
            session_start();
        }

        return isset($_SESSION['user']['id']);
    }

    // Vérifie si l'utilisateur est connecté
    public static function getConnectedUserId()
    {
        if (session_status() == PHP_SESSION_NONE) {
            // Configurer les paramètres du cookie de session
            session_set_cookie_params([
                'httponly' => true,
                'secure' => false, // à activer uniquement en HTTPS
                'samesite' => 'Strict'
            ]);

            // Démarrer la session
            session_start();
        }

        $ret = $_SESSION['user']['id'] ?? null;
        return $ret;
    }

    // Vérifie si l'utilisateur est validé
    public function isUserIdValid()
    {
        // L'userId est considéré valide à partir de 0
        return $this->user_id > -1;
    }

    // Vérifie  les informations de l'utilisateur
    public function verifyUserData()
    {
        // On vérifie le nom et le prénom
        $result = self::verifyNameFormat($this->nom)
            && self::verifyNameFormat($this->prenom);

        // Le format de retour en cas d'erreur est toujours le meme pour cette fonction
        // On retourne un tableau dont le premier élement est false
        // et dont le deuxième élement est un tableau associatif avec le json qui explique l'erreur
        if ($result == false) {
            return [
                false,
                [
                    'status' => 'error',
                    'message' => 'Le nom et le prénom ne peuvent contenir que des lettres, des espaces et des tirets'
                ]
            ];
        }

        // Ensuite on vérifie l'email
        $result = self::verifyEmailFormat($this->email);
        if ($result == false) {
            return [
                false,
                [
                    'status' => 'error',
                    'message' => "L'adresse e-mail n'est pas valide."
                ]
            ];
        }

        // Ensuite on vérifie le mot de passe
        $result = self::verifyStrongPassword($this->mot_de_passe);
        if ($result == false) {
            return [
                false,
                [
                    'status' => 'error',
                    'message' => "Le mot de passe n'est pas valide. Il doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre."
                ]
            ];
        }

        return [true, []];
    }

    // Verifie l'inscription de l'utilisateur
    public function verifyUserInscription()
    {
        [$returnValue, $jsonArray] = $this->verifyUserData();
        if ($returnValue === false) {
            return [false, $jsonArray];
        }

        // On vérifie que l'email n'est pas déjà utilisée
        $userId = self::getUserIdWithEmail($this->email);
        if ($userId !== false) {
            // Si l'email est déjà utilisé, renvoyer une erreur avec le message approprié
            return [
                false,
                [
                    'status' => 'error',
                    'message' => "L'adresse email est déjà utilisée. Veuillez changer d'adresse email pour votre inscription"
                ]
            ];
        }

        // Si l'email est unique, on peut continuer
        return [true, []];
    }

    // Méthode privée pour la connexion à la base de données
    private static function getConnection()
    {
        // Utiliser la méthode getInstance() du singleton de connexion
        $db = Database::getInstance(); // Récupère l'instance unique
        return $db->getConnection(); // Retourne la connexion PDO
    }

    // Méthode statique pour hacher le mot de passe
    public static function hashPassword($password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Méthode pour insérer un nouvel utilisateur dans la base de données
    public function insertUser()
    {
        // Récupération de la connexion à la base de données
        $pdo = $this->getConnection();

        // Vérification si la connexion est réussie et si toutes les informations nécessaires (nom, prénom, email, mot de passe) sont présentes
        // Si l'un de ces éléments est manquant, la fonction termine sans rien faire (return vide)
        if (!$pdo || !$this->nom || !$this->prenom || !$this->email || !$this->mot_de_passe) {
            return; // Retourne immédiatement si l'un des éléments est manquant
        }

        // Verifie si le role est valide
        if (!Role::isRoleValid($this->role)) {
            return false; // Retourne faux si le rôle n'est pas valide
        }

        // Préparation de la requête SQL d'insertion dans la table `utilisateur`
        $requete = $pdo->prepare("INSERT INTO utilisateur(nom_utilisateur, prenom_utilisateur, mail_utilisateur, mdp_utilisateur, valide_utilisateur, actif_utilisateur, Id_role) 
                                 VALUES (:nom, :prenom, :email, :mot_de_passe, :inscription_valide, :actif, :role)");

        // On hash le password
        $hashPass = self::hashPassword($this->mot_de_passe);

        // Liaison des paramètres avec les propriétés de l'objet
        // actif et inscription valide sont mis à false par défaut
        $requete->bindParam(':nom', $this->nom);
        $requete->bindParam(':prenom', $this->prenom);
        $requete->bindParam(':email', $this->email);
        $requete->bindParam(':mot_de_passe', $hashPass); // Hachage du mot de passe avant de le lier
        $requete->bindParam(':inscription_valide', $this->inscription_valide, PDO::PARAM_BOOL); // Spécifie que le paramètre est booléen
        $requete->bindParam(':actif', $this->actif, PDO::PARAM_BOOL); // Spécifie que le paramètre est booléen
        $requete->bindParam(':role', $this->role, PDO::PARAM_INT); // Spécifie que le paramètre est un entier (ID du rôle)

        // Exécution de la requête SQL d'insertion
        $result = $requete->execute();

        // Si l'exécution de la requête est réussie, on récupère l'ID du nouvel utilisateur inséré
        // Cela permet de savoir quel ID a été généré pour l'utilisateur et de le stocker dans $this->user_id
        if ($result) {
            $this->user_id = $pdo->lastInsertId();
        }

        // Retourne le résultat de la requête
        return $result;
    }

    // Méthode statique pour changer le profil de l'utilisateur
    public static function changeProfile($champsAChanger, $params)
    {
        if (empty($champsAChanger) || empty($params)) {
            return false;
        }

        $pdo = self::getConnection();
        $sql = "UPDATE utilisateur SET " . implode(', ', $champsAChanger) . " WHERE Id_utilisateur = :id";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // Méthode statique pour vérifier la connexion de l'utilisateur
    public static function verifyConnection($email, $password)
    {
        // Préparer la requête pour récupérer le mot de passe de l'utilisateur actif
        $pdo = self::getConnection();
        $requete = $pdo->prepare("SELECT mdp_utilisateur FROM utilisateur WHERE mail_utilisateur = :email AND valide_utilisateur = 1 AND actif_utilisateur = 1");
        $requete->bindParam(':email', $email);
        $requete->execute();

        // Récupérer le mot de passe hashé depuis la base de données
        $passwordActuel = $requete->fetchColumn();

        // Si aucun utilisateur actif n'est trouvé, renvoyer false
        if ($passwordActuel === false) {
            return false;
        }

        // Vérifier que le mot de passe fourni correspond au hash stocké
        return password_verify($password, $passwordActuel);
    }

    // Méthode statique pour vérifier le format du nom
    public static function verifyNameFormat($name): bool
    {
        // Le nom peut contenir seulement des caractères alphabétiques (y compris accentués) et des tirets
        // Le nom doit avoir au moins 2 caractères
        return !empty($name) && strlen($name) > 1 && preg_match('/^[a-zA-ZÀ-ÿ\-]+$/u', $name);
    }

    // Méthode statique pour vérifier le format de l'email
    public static function verifyEmailFormat($email): bool
    {
        // Utilisation de FILTER_VALIDATE_EMAIL pour valider le format de l'email de manière standard
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Permettre les caractères autorisés dans la partie locale (avant @)
            return preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email);
        }

        return false;
    }

    // Méthode statique pour vérifier la force du mot de passe
    public static function verifyStrongPassword($password): bool
    {
        // On vérifie que la taille reste dans les limites choisies
        // Et que le mot de passe possède au moins 1 miniscule/majuscule/chiffre
        $length = strlen($password);
        return
            $length >= self::$LongueurMinimumPassword &&
            $length <= self::$LongueurMaximumPassword
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/[0-9]/', $password);
    }

    // Méthode statique pour mettre à jour le mot de passe d'un utilisateur par son ID
    public static function updateUserPasswordById($userId, $newPassword)
    {
        // Connexion à la base de données
        $pdo = Database::getInstance()->getConnection();

        // Requête SQL pour mettre à jour le mot de passe de l'utilisateur
        $sql = "UPDATE utilisateur SET mdp_utilisateur = :password WHERE Id_utilisateur = :userId";

        // Préparation de la requête SQL
        $stmt = $pdo->prepare($sql);

        // Hachage du mot de passe
        $hashedPassword = self::hashPassword($newPassword);

        // Lier les paramètres à la requête SQL
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);  // Lien du mot de passe haché
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);             // Lien de l'ID de l'utilisateur

        // Exécution de la requête et retour de son résultat (soit true soit false)
        return $stmt->execute();
    }

    // Méthode statique pour récupérer les informations d'un utilisateur par son email
    public static function getUserData($email)
    {
        // Connexion à la base de données
        $pdo = self::getConnection();

        // Requête SQL pour récupérer les informations de l'utilisateur
        $requete = $pdo->prepare("SELECT 
                Id_utilisateur, 
                nom_utilisateur, 
                prenom_utilisateur, 
                mail_utilisateur, 
                Id_role
                FROM utilisateur 
                WHERE mail_utilisateur = :email AND valide_utilisateur = 1 AND actif_utilisateur = 1");

        // Lier le paramètre de l'email à la requête SQL
        $requete->bindParam(':email', $email);

        // Exécution de la requête SQL
        $requete->execute();

        // Récupérer et retourner le résultat sous forme de tableau associatif
        // ou false en cas d'erreur (à ne pas oublier de vérifier comme retour)
        return $requete->fetch(PDO::FETCH_ASSOC);
    }

    // Méthode statique pour récupérer l'ID d'un utilisateur par son email
    public static function getUserIdWithEmail($email)
    {
        // Connexion à la base de données
        $pdo = self::getConnection();

        // Vérifier si l'email existe dans la base de données
        $stmt = $pdo->prepare("SELECT Id_utilisateur FROM utilisateur WHERE mail_utilisateur = :email AND valide_utilisateur = 1 AND actif_utilisateur = 1");

        // Liaison du paramètre
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        // Exécution du statement
        $stmt->execute();

        // Retour du résultat
        return $stmt->fetchColumn();
    }

    // Méthode statique pour desactiver un utilisateur par son email
    public static function desactiverUser($email)
    {
        $db = Database::getInstance();
        $connexion = $db->getConnection();

        $query = "UPDATE utilisateur SET actif_utilisateur = 0 WHERE mail_utilisateur = :email";
        $stmt = $connexion->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        return $stmt->execute();
    }
}
