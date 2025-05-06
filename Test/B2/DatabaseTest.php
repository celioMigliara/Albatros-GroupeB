<?php
use PHPUnit\Framework\TestCase;

define('PHPUNIT_RUNNING', true);

require_once __DIR__ . '/../../Model/ModeleDBB2.php';

class DatabaseTest extends TestCase
{
    // Vérifie que la méthode getConnection() retourne bien un objet PDO
    public function testDatabaseConnectionReturnsPDO()
    {
        $db = Database::getInstance(); // Singleton
        $this->assertInstanceOf(PDO::class, $db->getConnection());
    }

    // Vérifie que la connexion utilise bien la base "albatros_test"
    public function testDatabaseConnectionUsesTestDb()
    {
        $_ENV['DB_ENV'] = 'TEST'; // Force TEST

        // Réinitialisation manuelle de l'instance pour prendre en compte la nouvelle valeur
        $this->resetDatabaseInstance();

        $db = Database::getInstance();
        $pdo = $db->getConnection();
        $stmt = $pdo->query("SELECT DATABASE()");
        $dbName = $stmt->fetchColumn();
        $this->assertEquals("albatros_test", $dbName);
    }

    public function testDatabaseConnectionUsesProdDb()
    {
        $ancienEnv = $_ENV['DB_ENV'] ?? null;
        $_ENV['DB_ENV'] = 'PROD'; // Force PROD

        $this->resetDatabaseInstance();

        $db = Database::getInstance();
        $pdo = $db->getConnection();
        $dbName = $pdo->query("SELECT DATABASE()")->fetchColumn();

        $this->assertEquals("albatros", $dbName);

        // Restauration de l'env initial
        if ($ancienEnv !== null) {
            $_ENV['DB_ENV'] = $ancienEnv;
        } else {
            unset($_ENV['DB_ENV']);
        }
        $this->resetDatabaseInstance(); // Recharger l'ancienne config
    }

    // Simule une connexion cassée (sans utiliser Database)
    public function testThrowsExceptionOnConnectionError()
    {
        $this->expectException(RuntimeException::class);

        // Simulation manuelle d’une tentative de connexion invalide
        $host = "invalid_host";
        $dbname = "albatros_test";
        $username = "root";
        $password = "";

        try {
            new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        } catch (PDOException $e) {
            throw new RuntimeException("Erreur de connexion : " . $e->getMessage());
        }
    }

    public function testCharsetIsUtf8()
    {
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $charset = $pdo->query("SELECT @@character_set_connection")->fetchColumn();
        $this->assertEquals("utf8", $charset);
    }

    public function testErrModeIsException()
    {
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
    }

    public function testSingletonReturnsSameInstance()
    {
        $db1 = Database::getInstance(); 
        $db2 = Database::getInstance(); 
        $this->assertSame($db1, $db2); // Doit être la même instance
    }

    // Méthode utilitaire pour réinitialiser l'instance du singleton
    private function resetDatabaseInstance(): void
    {
        Database::resetInstance();
    }
}
