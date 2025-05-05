<?php
require_once(__DIR__ . '/../../Model/ModeleDBB2.php'); // à adapter à ton arborescence

// Vérifie si le formulaire est soumis
//if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des identifiants postés
   /* $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;*/

    $username = "Admin";
    $password = "password";

    if (empty($username) || empty($password)) {
        echo "Nom d'utilisateur ou mot de passe manquant.";
        exit;
    }
    $pdo = Database::getInstance()->getConnection(); // Utilise l'instance partagée

    // Prépare et exécute la requête pour récupérer l'utilisateur
    $stmt = $pdo->prepare("SELECT id_utilisateur, nom_utilisateur,mdp_utilisateur, id_role FROM utilisateur WHERE nom_utilisateur = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifie si l'utilisateur existe
    if(!$user){
        echo "Aucun utilisateurs trouvé";
        exit();
    }
    
    //$_SESSION['user_id'] = $user['id_utilisateur'];
    //$_SESSION['user_role'] = $user['id_role'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

    /*if (!$user || !password_verify($password, $user['mdp_utilisateur'])) {
        echo json_encode(['success' => false, 'message' => "Identifiants invalides."]);
        exit;
    }*/
    
    // Vérifie si l'utilisateur existe
    if ($user['nom_utilisateur'] != $username)
    {
        echo "Nom d'utilisateur inconnu.";
        exit;
    }

//}
?>
