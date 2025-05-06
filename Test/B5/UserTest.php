<?php
define('PHPUNIT_RUNNING', true);

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../Model/B5/User.php';
require_once __DIR__ . '/../../Model/ModeleDBB2.php';

class UserTest extends TestCase
{
    /** ✅ Teste si la méthode retourne un entier */
    public function testCountUtilisateursEnAttente_retourneUnEntier()
    {
        $count = User::countUtilisateursEnAttente();
        $this->assertIsInt($count, "Doit retourner un entier.");
        $this->assertGreaterThanOrEqual(0, $count, "Doit être ≥ 0.");
    }

    /** ✅ Vérifie que getUtilisateursEnAttente retourne bien un tableau */
    public function testGetUtilisateursEnAttenteRetourneUnTableau()
    {
        $result = User::getUtilisateursEnAttente(5, 0);

        $this->assertIsArray($result, "La méthode doit retourner un tableau.");

        // Si des résultats, on vérifie la structure du premier utilisateur
        if (count($result) > 0) {
            $this->assertArrayHasKey('id_utilisateur', $result[0]);
            $this->assertArrayHasKey('nom_utilisateur', $result[0]);
            $this->assertArrayHasKey('prenom_utilisateur', $result[0]);
            $this->assertArrayHasKey('mail_utilisateur', $result[0]);
        }
    }

    /** ✅ Vérifie que getUtilisateurById retourne false si l'utilisateur n'existe pas */
    public function testGetUtilisateurByIdRetourneFalseSiIdInvalide()
    {
        $result = User::getUtilisateurById(-9999); // ID bidon
        $this->assertFalse($result, "Doit retourner false si l'utilisateur n'existe pas.");
    }

    /** ✅ Vérifie que getUtilisateurById retourne les données correctement si l'ID est bon */
    public function testGetUtilisateurByIdRetourneDonneesSiValide()
    {
        $utilisateurValideId = 6; // 💡 Mets ici un vrai ID qui existe dans ta DB

        $result = User::getUtilisateurById($utilisateurValideId);

        $this->assertIsArray($result, "La méthode doit retourner un tableau associatif.");
        $this->assertArrayHasKey('id_utilisateur', $result);
        $this->assertArrayHasKey('nom_utilisateur', $result);
        $this->assertArrayHasKey('prenom_utilisateur', $result);
        $this->assertArrayHasKey('mail_utilisateur', $result);
        $this->assertArrayHasKey('nom_role', $result);
    }

/** ✅ Vérifie que getBatimentsAssignes retourne un tableau de noms de bâtiments */
public function testGetBatimentsAssignesRetourneTableau()
{
    $userId = 1; // ⚠️ Mets ici un ID d'utilisateur qui a au moins un bâtiment assigné dans la table `travaille`

    $result = User::getBatimentsAssignes($userId);

    $this->assertIsArray($result, "La méthode doit retourner un tableau.");

    // S'il y a des bâtiments, on vérifie que chaque élément est une string
    foreach ($result as $batiment) {
        $this->assertIsString($batiment, "Chaque bâtiment doit être une chaîne de caractères.");
    }
}

/** ✅ Vérifie que getBatimentsAssignes retourne un tableau vide pour un utilisateur sans bâtiment */
public function testGetBatimentsAssignesRetourneVideSiAucunLien()
{
    $userIdSansBatiment = 9999; // ⚠️ ID inexistant ou un user sans assignation dans `travaille`

    $result = User::getBatimentsAssignes($userIdSansBatiment);

    $this->assertIsArray($result, "Doit retourner un tableau même vide.");
    $this->assertCount(0, $result, "Doit retourner un tableau vide si aucun bâtiment.");
}

public function testGetNomRoleRetourneNomCorrect()
{
    // Rôle existant : 1 = Administrateur
    $nom = User::getNomRole(3);
    $this->assertEquals("Admin", $nom);
}

public function testGetNomRoleRetourneRoleInconnuSiInexistant()
{
    // ID inexistant : 99
    $nom = User::getNomRole(99);
    $this->assertEquals("Rôle inconnu", $nom);
}

public function testGetIdsUtilisateursEnAttenteRetourneTableau()
{
    // Act : on récupère les ID des utilisateurs en attente
    $result = User::getIdsUtilisateursEnAttente();

    // Assert : doit retourner un tableau
    $this->assertIsArray($result, "La méthode doit retourner un tableau.");

    foreach ($result as $utilisateur) {
        // Chaque élément doit être un tableau contenant 'id_utilisateur'
        $this->assertIsArray($utilisateur, "Chaque ligne doit être un tableau.");
        $this->assertArrayHasKey('id_utilisateur', $utilisateur, "Chaque ligne doit avoir la clé 'id_utilisateur'.");
        $this->assertIsNumeric($utilisateur['id_utilisateur'], "L'id_utilisateur doit être numérique.");
    }
}


public function testSetTokenMetAJourTokenEtExpiration()
{
    // Arrange : créer des données factices
    $id = 6; // Assure-toi qu’un utilisateur avec cet ID existe en BDD
    $token = bin2hex(random_bytes(16));
    $expiration = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // Act : appel de la méthode à tester
    User::setToken($id, $token, $expiration);

    // Assert : on récupère les données de l'utilisateur pour vérifier
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare("SELECT token_utilisateur, date_exp_token_utilisateur FROM utilisateur WHERE id_utilisateur = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $this->assertEquals($token, $result['token_utilisateur'], "Le token doit être mis à jour.");
    $this->assertEquals($expiration, $result['date_exp_token_utilisateur'], "La date d'expiration doit être mise à jour.");
}


public function testGetUtilisateurByTokenRetourneUtilisateurSiTokenValide()
{
    // Arrange : on attribue un token temporaire à un utilisateur existant
    $id = 6; // à adapter selon ton utilisateur
    $token = bin2hex(random_bytes(16));
    $expiration = date('Y-m-d H:i:s', strtotime('+1 day'));

    User::setToken($id, $token, $expiration);

    // Act : on récupère par le token
    $result = User::getUtilisateurByToken($token);

    // Assert
    $this->assertIsArray($result, "La méthode doit retourner un tableau.");
    $this->assertEquals($id, $result['id_utilisateur'], "L'ID utilisateur doit correspondre.");
    $this->assertEquals($token, $result['token_utilisateur'], "Le token retourné doit être celui qu'on a mis.");
}

public function testGetUtilisateurByTokenRetourneFalseSiTokenInexistant()
{
    // Token bidon
    $fakeToken = "tokennonexistent123456";

    // Act
    $result = User::getUtilisateurByToken($fakeToken);

    // Assert
    $this->assertFalse($result, "La méthode doit retourner false si le token n'existe pas.");
}

public function testConfirmerInscriptionMetAJourLUtilisateur()
{
    // Arrange : on choisit un ID valide et on simule un token
    $id = 6; // À adapter si besoin
    $token = bin2hex(random_bytes(16));
    $expiration = date('Y-m-d H:i:s', strtotime('+1 day'));

    // On assigne un token à l'utilisateur pour commencer
    User::setToken($id, $token, $expiration);

    // Act : on confirme l'inscription
    User::confirmerInscription($id);

    // Assert : on récupère l'utilisateur et on vérifie les colonnes
    $utilisateur = User::getUtilisateurById($id);

    $this->assertEquals(1, $utilisateur['valide_utilisateur'], "L'utilisateur doit être marqué comme validé.");
    $this->assertEquals(1, $utilisateur['actif_utilisateur'], "L'utilisateur doit être actif.");
    $this->assertNull($utilisateur['token_utilisateur'], "Le token doit être nul après validation.");
    $this->assertNull($utilisateur['date_exp_token_utilisateur'], "La date d'expiration du token doit être nulle.");
}


public function testSupprimerUtilisateurSupprimeLesDonneesAssociees()
{
    $pdo = Database::getInstance()->getConnection();

    // 🧱 1. Création d’un faux utilisateur
    $pdo->exec("
        INSERT INTO utilisateur (nom_utilisateur, prenom_utilisateur, mail_utilisateur, mdp_utilisateur, valide_utilisateur, actif_utilisateur, id_role)
        VALUES ('Test', 'Delete', 'delete@test.com', 'fakepass', 0, 0, 3)
    ");
    $userId = $pdo->lastInsertId();

    // 🧱 2. Ajout d'une liaison dans `travaille` (associé au bâtiment ID = 2)
    $pdo->exec("INSERT INTO travaille (id_utilisateur, id_batiment) VALUES ($userId, 3)");

    // 🧪 3. Appel de la méthode à tester
    $result = User::supprimerUtilisateur($userId);

    // ✅ 4. Vérification que l’utilisateur a été supprimé
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = :id");
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch();

    // ✅ 5. Vérification que le lien dans travaille a été supprimé
    $stmt2 = $pdo->prepare("SELECT * FROM travaille WHERE id_utilisateur = :id");
    $stmt2->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt2->execute();
    $travail = $stmt2->fetch();

    // ✅ 6. Assertions
    $this->assertTrue($result, "La méthode doit retourner true en cas de succès.");
    $this->assertFalse($user, "L'utilisateur doit être supprimé de la table 'utilisateur'.");
    $this->assertFalse($travail, "Les entrées associées dans 'travaille' doivent être supprimées.");
}

}
