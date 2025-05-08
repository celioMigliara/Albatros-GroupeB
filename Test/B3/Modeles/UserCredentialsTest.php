<?php

define('PHPUNIT_RUNNING', true);

require_once __DIR__ . '/../../../Test/B3/BaseTestClass.php';
require_once __DIR__ . '/../../../Model/B3/UserCredentials.php';

// Test unitaire pour la classe UserCredentials
class UserCredentialsTest extends BaseTestClass
{
    /*==============================================*/
    /*================= TESTS NOM ==================*/
    /*==============================================*/

    public function testNomCorrect()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertEquals('Dupont', $user->getNom());
    }

    public function testNomInvalide()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont1', 'Jean', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyNameFormat($user->getNom()));
    }

    public function testNomAvecChiffre()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont123', 'Jean', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyNameFormat($user->getNom()));
    }

    public function testNomAvecCaractereSpecial()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont@!', 'Jean', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyNameFormat($user->getNom()));
    }

    public function testNomVide()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('', 'Jean', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyNameFormat($user->getNom()));
    }

    public function testNomAvecEspaces()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('    ', 'Jean', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyNameFormat($user->getNom()));
    }

    public function testNomAvecTiret()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont-Durand', 'Jean', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertTrue($user->verifyNameFormat($user->getNom()));
    }

    public function testNomAvecLettresAccentuees()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Élise', 'Jean', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertTrue($user->verifyNameFormat($user->getNom()));
    }

    public function testNomTropCourt()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('D', 'Jean', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyNameFormat($user->getNom()));
    }

    public function testNomLong()
    {
        $this->viderToutesLesTables();
        $longNom = str_repeat('A', 255); // Génère un nom de 255 caractères
        $user = new UserCredentials($longNom, 'Jean', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertTrue($user->verifyNameFormat($user->getNom()));
    }

    public function testNomAvecApostrophe()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials("O'Connor", 'Jean', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyNameFormat($user->getNom()));
    }

    public function testNomAvecAccentEtAutresCaracteres()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dup^ént', 'Jean', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyNameFormat($user->getNom()));
    }

    /*==============================================*/
    /*================= TESTS PRENOM ===============*/
    /*==============================================*/
    public function testPrenomCorrect()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertEquals('Jean', $user->getPrenom());
    }

    public function testPrenomInvalide()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean-123', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyNameFormat($user->getPrenom()));
    }

    public function testPrenomAvecChiffre()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean123', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyNameFormat($user->getPrenom()));
    }

    public function testPrenomAvecCaractereSpecial()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean@!', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyNameFormat($user->getPrenom()));
    }

    public function testPrenomVide()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', '', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyNameFormat($user->getPrenom()));
    }

    public function testPrenomAvecEspaces()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', '    ', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyNameFormat($user->getPrenom()));
    }

    public function testPrenomAvecTiret()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean-Claude', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertTrue($user->verifyNameFormat($user->getPrenom()));
    }

    public function testPrenomAvecLettresAccentuees()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Élise', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertTrue($user->verifyNameFormat($user->getPrenom()));
    }

    public function testPrenomTropCourt()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'J', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyNameFormat($user->getPrenom()));
    }

    public function testPrenomLong()
    {
        $this->viderToutesLesTables();
        $longPrenom = str_repeat('A', 255); // Génère un prénom de 255 caractères
        $user = new UserCredentials('Dupont', $longPrenom, 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertTrue($user->verifyNameFormat($user->getPrenom()));
    }

    public function testPrenomAvecApostrophe()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', "O'Connor", 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyNameFormat($user->getPrenom()));
    }

    public function testPrenomAvecAccentEtAutresCaracteres()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean^Étienne', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyNameFormat($user->getPrenom()));
    }


    /*==============================================*/
    /*================= TESTS EMAIL ===============*/
    /*==============================================*/
    public function testEmailCorrect()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertEquals('jean@example.com', $user->getEmail());
    }

    public function testEmailDejaUtilise()
    {
        $this->viderToutesLesTables();
        $this->insererRoles();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'MotsDePasse1258.', Role::TECHNICIEN);
        $user->insertUser();
        $this->assertFalse($user->getUserIdWithEmail($user->getEmail()));
    }

    public function testEmailInvalide()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'emailinvalide@', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyEmailFormat($user->getEmail()));
    }

    public function testEmailVide()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', '', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyEmailFormat($user->getEmail())); // Vérifier que l'email vide est rejeté
    }

    public function testEmailAvecCaracteresSpeciaux()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean&test@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyEmailFormat($user->getEmail())); // Vérifier que l'email avec des caractères spéciaux est rejeté
    }

    public function testEmailDomaineIncorrect()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@examplecom', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyEmailFormat($user->getEmail())); // Vérifier que l'email avec un domaine incorrect est rejeté
    }

    public function testEmailLong()
    {
        $this->viderToutesLesTables();
        $longEmail = str_repeat('a', 64) . '@example.com'; // Email avec un long nom local
        $user = new UserCredentials('Dupont', 'Jean', $longEmail, 'pass123', Role::TECHNICIEN);
        $this->assertTrue($user->verifyEmailFormat($user->getEmail())); // Vérifier que l'email très long est accepté
    }

    public function testEmailAvecAccents()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'élise@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyEmailFormat($user->getEmail())); // Vérifier que l'email avec des accents est rejeté
    }


    /*==============================================*/
    /*================= TESTS MDP ==================*/
    /*==============================================*/
    public function testMotDePasseCorrect()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertEquals('pass123', $user->getMotDePasse());
    }

    public function testMotDePasseTropCourt()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'short', Role::TECHNICIEN);
        $this->assertFalse($user->verifyStrongPassword($user->getMotDePasse()));
    }

    public function testMotDePasseSansMajuscule()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'password123', Role::TECHNICIEN);
        $this->assertFalse($user->verifyStrongPassword($user->getMotDePasse()));
    }

    public function testMotDePasseValide()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass1234.', Role::TECHNICIEN);  // Mot de passe valide
        $this->assertTrue($user->verifyStrongPassword($user->getMotDePasse()));  // Vérifie que la validation est réussie
    }

    public function testMotDePasseTropLong()
    {
        $this->viderToutesLesTables();
        $longPassword = str_repeat('A', 65);  // Mot de passe avec 65 caractères
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', $longPassword, Role::TECHNICIEN);
        $this->assertFalse($user->verifyStrongPassword($user->getMotDePasse()));  // Le mot de passe doit être rejeté
    }

    public function testMotDePasseSansChiffre()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Password', Role::TECHNICIEN);  // Pas de chiffre
        $this->assertFalse($user->verifyStrongPassword($user->getMotDePasse()));  // Le mot de passe doit être rejeté
    }

    public function testMotDePasseUniquementChiffres()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', '12345678', Role::TECHNICIEN);  // Mot de passe avec uniquement des chiffres
        $this->assertFalse($user->verifyStrongPassword($user->getMotDePasse()));  // Le mot de passe doit être rejeté
    }

    public function testMotDePasseAvecCaracteresSpeciauxValides()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass_1234', Role::TECHNICIEN);  // Mot de passe valide avec un underscore
        $this->assertTrue($user->verifyStrongPassword($user->getMotDePasse()));  // Le mot de passe doit être accepté
    }

    public function testMotDePasseSansMajusculeEtSansChiffre()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'password', Role::TECHNICIEN);  // Pas de majuscule ni de chiffre
        $this->assertFalse($user->verifyStrongPassword($user->getMotDePasse()));  // Le mot de passe doit être rejeté
    }

    public function testMotDePasseUniquementMajuscules()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'PASSWORD123', Role::TECHNICIEN);  // Mot de passe avec uniquement des majuscules
        $this->assertFalse($user->verifyStrongPassword($user->getMotDePasse()));  // Le mot de passe doit être accepté
    }

    public function testMotDePasseAvecCaracteresSpeciaux()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass@#1234', Role::TECHNICIEN);  // Mot de passe avec des caractères spéciaux interdits
        $this->assertTrue($user->verifyStrongPassword($user->getMotDePasse()));  // Le mot de passe doit être rejeté
    }

    public function testMotDePasseValideAvecTousLesCritères()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass_1234', Role::TECHNICIEN);  // Mot de passe valide avec majuscule, minuscule, chiffre, et caractère spécial
        $this->assertTrue($user->verifyStrongPassword($user->getMotDePasse()));  // Le mot de passe doit être accepté
    }

    /* ============================================== */
    /* ================ TESTS ROLE ================== */
    /* ============================================== */
    public function testRoleTechnicien()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass1234', Role::TECHNICIEN);
        $this->assertEquals(Role::TECHNICIEN, $user->getRole());
    }

    public function testRoleUtilisateurs()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Martin', 'Sophie', 'sophie@example.com', 'Pass1234', Role::UTILISATEUR);
        $this->assertEquals(Role::UTILISATEUR, $user->getRole());  // Vérifie que le rôle utilisateur est correctement assigné
    }

    public function testRoleInvalide()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass1234', 999);  // Rôle invalide
        // Cette méthode pourrait être mise à jour pour renvoyer un message d'erreur
        $this->assertFalse(Role::IsRoleValid($user->getRole()));  // Vérifie que l'insertion échoue si le rôle est invalide
    }



    /* ============================================== */
    /* =============== TESTS INSCRIPTION ============ */
    /* ============================================== */
    public function testValeursParDefautInscriptionValideActif()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'pass123', Role::TECHNICIEN);
        $this->assertFalse($user->getInscriptionValide());
        $this->assertFalse($user->getActif());
    }

    public function testToutesLesProprietesSontInitialiseesCorrectementAdmin()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Martin', 'Sophie', 'sophie@test.com', 'securePwd', Role::ADMINISTRATEUR);
        $user->setInscriptionValide(true);
        $user->setActif(true);
        $this->assertEquals('Martin', $user->getNom());
        $this->assertEquals('Sophie', $user->getPrenom());
        $this->assertEquals('sophie@test.com', $user->getEmail());
        $this->assertEquals('securePwd', $user->getMotDePasse());
        $this->assertEquals(1, $user->getRole());
        $this->assertTrue($user->getInscriptionValide());
        $this->assertTrue($user->getActif());
    }

    public function testToutesLesProprietesSontInitialiseesCorrectementEmploye()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Martin', 'Sophie', 'sophie@test.com', 'securePwd', Role::UTILISATEUR);

        $this->assertEquals('Martin', $user->getNom());
        $this->assertEquals('Sophie', $user->getPrenom());
        $this->assertEquals('sophie@test.com', $user->getEmail());
        $this->assertEquals('securePwd', $user->getMotDePasse());
        $this->assertEquals(Role::UTILISATEUR, $user->getRole());
        $this->assertFalse($user->getInscriptionValide());
        $this->assertFalse($user->getActif());
    }

    public function testInsertionUtilisateurValide()
    {
        $this->viderToutesLesTables();
        $this->insererRoles();
        $user = new UserCredentials('Dupont', 'Jean', 'jeanvalide@example.com', 'Pass1234', Role::TECHNICIEN);
        $this->assertTrue($user->insertUser());
    }

    public function testConnexionAvecMotDePasseIncorrect()
    {
        $this->viderToutesLesTables();
        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass1234', Role::TECHNICIEN);
        // Simuler un mot de passe incorrect
        $this->assertFalse($user->verifyPassword($user->getEmail(), 'WrongPassword'));
    }
    public function testConnexionAvecMotDePasseCorrect()
    {
        $this->viderToutesLesTables();
        $this->insererRoles();

        $user = new UserCredentials('Dupont', 'Jean', 'jean@example.com', 'Pass1234.', Role::ADMINISTRATEUR);
        $user->setInscriptionValide(true);
        $user->setActif(true);
        $user->insertUser(); // Insérer l'utilisateur dans la base de données
        // Simuler une connexion avec le mot de passe correct
        $this->assertTrue($user->verifyPassword($user->getEmail(), $user->getMotDePasse()));
    }
}

?>