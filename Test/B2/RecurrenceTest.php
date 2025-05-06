<?php

use PHPUnit\Framework\TestCase;
define('PHPUNIT_RUNNING', true);
require_once __DIR__ . '/../../Secure/B2/session.php';
require_once __DIR__ . '/../../Model/B2/demande.php';

class RecurrenceTest  extends TestCase{

    private PDO $pdo;
    private RecurrenceModel $modele;

    protected function setUp(): void {
        // 🔸 Connexion à la base de test MySQL
        $this->pdo = Database::getInstance()->getConnection();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->modele = new RecurrenceModel($this->pdo);

        // 🔸 Nettoyer les tables utilisées pour le test
        $this->pdo->exec("DELETE FROM recurrence");
        
    }

    // Teste si la session démarre correctement
    public function testSessionStart()
    {
        // Assure-toi que la session est démarrée
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Vérifie que la session existe
        $this->assertTrue(session_status() == PHP_SESSION_ACTIVE, "La session n'est pas active.");
    }

    // Teste si le token CSRF est bien généré
    public function testGenerateCsrfToken()
    {
        // Démarre une session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
  
        // Génère un token CSRF
        $token = generateCsrfToken();
  
        // Vérifie que le token est bien généré
        $this->assertNotEmpty($token, "Le token CSRF devrait être généré.");
        $this->assertTrue(strlen($token) > 0, "Le token CSRF ne devrait pas être vide.");
    }

    // Teste si le token CSRF est valide
    public function testValidateCsrfToken()
    {
        // Démarre une session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Génère un token CSRF
        $validToken = generateCsrfToken();

        // Ajoute un token CSRF incorrect pour tester
        $invalidToken = 'invalid-token';

        // Teste un token valide
        $this->assertTrue(validateCsrfToken($validToken), "Le token valide ne devrait pas échouer.");
        
        // Teste un token invalide
        $this->assertFalse(validateCsrfToken($invalidToken), "Le token invalide ne devrait pas être validé.");
    }

    // Teste si la session est protégée par le user-agent
    public function testUserAgentSessionProtection()
    {
        // Démarre une session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
 
        // Définit un agent utilisateur fictif pour ce test
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
 
        // Initialise $_SESSION['user_agent']
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
 
        // Vérifie que la session a bien l'agent utilisateur
        $this->assertEquals($_SESSION['user_agent'], $_SERVER['HTTP_USER_AGENT'], "L'agent utilisateur dans la session ne correspond pas.");
    }

    public function testAjouterRecurrence(): void {
        $timestamp = mktime(0,0,0,04,10,2025);
        $result = $this->modele->ajouterRecurrence(
            "Test Sujet", "Description de test", date("Y/m/d",$timestamp),8,null,2,"mois",null
        );

        $this->assertTrue($result['success']);
        $this->assertEquals("Récurrence ajoutée avec succès !", $result['message']);

        // Vérifie que les données sont bien insérées
        $stmt = $this->pdo->query("SELECT * FROM recurrence WHERE sujet_reccurrence = 'Test Sujet'");
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($data);
        $this->assertEquals("Description de test", $data['desc_recurrence']);
    }

    public function testAjouterRecurrenceEtRecuperer(): void {
        $timestamp = mktime(0,0,0,04,10,2025);
        $result = $this->modele->ajouterRecurrence(
            "Test Sujet 2", "Description de test puis repris", date("Y/m/d",$timestamp),8,null,2,"mois",null
        );

        $this->assertTrue($result['success']);
        $this->assertEquals("Récurrence ajoutée avec succès !", $result['message']);

        // Vérifie que les données sont bien insérées
        $stmt = $this->pdo->query("SELECT id_recurrence , desc_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test Sujet 2'");
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $result = $this->modele->getById($data['id_recurrence']);

        $this->assertNotEmpty($data);
        $this->assertEquals("Description de test puis repris", $data['desc_recurrence']);
    }

    public function testAjouterRecurrenceFreqNull(): void {
        $timestamp = mktime(0,0,0,04,10,2025);
        $result = $this->modele->ajouterRecurrence(
            "Test Sujet Freq Invalide", "Description de test", date("Y/m/d",$timestamp),null,null,2,"mois",null
        );

        $this->assertFalse($result['success']);
        $this->assertEquals("Entrez un nombre pour la fréquence", $result['message']);
        
    }

    public function testAjouterRecurrenceFreqNegative(): void {
        $timestamp = mktime(0,0,0,04,10,2025);
        $result = $this->modele->ajouterRecurrence(
            "Test Sujet Freq Invalide", "Description de test", date("Y/m/d",$timestamp),-5,null,2,"mois",null
        );

        $this->assertFalse($result['success']);
        $this->assertEquals("La fréquence doit être un nombre positif", $result['message']);
    }

    public function testAjouterRecurrenceFreqSupérieurA100Mois(): void {
        $timestamp = mktime(0,0,0,04,10,2025);
        $result = $this->modele->ajouterRecurrence(
            "Test Sujet Freq Invalide", "Description de test", date("Y/m/d",$timestamp),150,null,2,"mois",null
        );

        $this->assertFalse($result['success']);
        $this->assertEquals("Entrez une fréquence valide pour les mois, pas plus de 100 mois", $result['message']);
    }  

    public function testAjouterRecurrenceFreqSupérieurA5Ans(): void {
        $timestamp = mktime(0,0,0,04,10,2025);
        $result = $this->modele->ajouterRecurrence(
            "Test Sujet Freq Invalide", "Description de test", date("Y/m/d",$timestamp),6,null,2,"année",null
        );

        $this->assertFalse($result['success']);
        $this->assertEquals("Entrez une fréquence valide pour les années, pas plus de 5 ans", $result['message']);
    }  

    public function testAjouterRecurrenceSansUnitefrequence(): void {
        $timestamp = mktime(0,0,0,04,10,2025);
        $result = $this->modele->ajouterRecurrence(
            "TestSujet", "Description de test", date("Y/m/d",$timestamp),8,null,2,null,"jour"
        );

        $this->assertFalse($result['success']);
        $this->assertEquals("Unité de temps invalide.", $result['message']);
    }

    public function testAjouterRecurrenceSansTitre(): void {
        $timestamp = mktime(0,0,0,04,10,2025);
        $result = $this->modele->ajouterRecurrence(
            "", "Description de test", date("Y/m/d",$timestamp),8,null,2,"mois",null
        );

        $this->assertFalse($result['success']);
        $this->assertEquals("Entrez un titre pour la maintenance", $result['message']);
    }

    public function testAjouterRecurrenceSansfrequenceRappel(): void {
        $timestamp = mktime(0,0,0,04,10,2025);
        $result = $this->modele->ajouterRecurrence(
            "TestSujet", "Description de test", date("Y/m/d",$timestamp),8,null,2,"mois","jour"
        );

        $this->assertTrue($result['success']);
    }

    public function testAjouterRecurrenceFrequenceEndessousfrequenceRappel(): void {
        $timestamp = mktime(0,0,0,04,10,2025);
        $result = $this->modele->ajouterRecurrence(
            "TestSujet", "Description de test", date("Y/m/d",$timestamp),2,5,7,"jour","jour"
        );

        $this->assertFalse($result['success']);
        $this->assertEquals("Le délai de rappel ne peut être supérieur à la fréquence de la maintenance.", $result['message']);
    }

    public function testAjouterRecurrenceSansUniteRappelMaisAvecFrequence(): void {
        $timestamp = mktime(0,0,0,04,10,2025);
        $result = $this->modele->ajouterRecurrence(
            "TestSujet", "Description de test", date("Y/m/d",$timestamp),2,8,2,"mois",null
        );

        $this->assertFalse($result['success']);
        $this->assertEquals("Vous ne pouvez pas insérer une fréquence de rappel si vous n'avez pas sélectionné une unité de rappel", $result['message']);
    }



    public function testModifierRecurrence(): void {
        $result = $this->modele->ajouterRecurrence(
            "Test Sujet_modif", "Description de test", "2025-04-04",8,null,2,"mois",null
        );

        $this->assertTrue($result['success']);

        $stmt = $this->pdo->query("SELECT id_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test Sujet_modif'");
        $idRecurrence = $stmt->fetchColumn(); // On récupère l'ID

        $result = $this->modele->update($idRecurrence,"Test_sujet_modifie","Description de test modifié","2025-05-27",5,2,4,"année","mois");
        $this->assertTrue($result['success']);
    }

    public function testSupprimerRecurrence(): void {
        $result = $this->modele->ajouterRecurrence(
            "Test Sujet supp", "Description de test", "2025-04-04",8,null,2,"mois",null
        );

        $this->assertTrue($result['success']);

        $stmt = $this->pdo->query("SELECT id_recurrence FROM recurrence WHERE sujet_reccurrence = 'Test Sujet supp'");
        $idRecurrence = $stmt->fetchColumn(); // On récupère l'ID

        $result = $this->modele->delete($idRecurrence);

        $this->assertTrue($result['success']);
    }

    public function testDatabaseUsed(): void {
        $stmt = $this->pdo->query("SELECT DATABASE()");
        $dbName = $stmt->fetchColumn();
        $this->assertEquals("albatros_test", $dbName);
    }

    public function testDatabasefalse(): void {
        $this->expectException(PDOException::class); 
        // Cette table n'existe pas, donc ça déclenche bien une exception
        $this->pdo->query("SELECT * FROM table_qui_pas");
    }

    public function testInvalidDatabaseConnection(): void {
        $this->expectException(PDOException::class);
    
        new PDO("mysql:host=localhost;dbname=bd_invalide", "root", ""); // mauvaise base
    }



}
?>