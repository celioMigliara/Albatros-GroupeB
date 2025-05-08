<?php
define('PHPUNIT_RUNNING', true);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../Model/B2/filtreModelB2.php';

class exportControllerTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       // $this->createSchema();
       
        $this->insertTestData();
    }

   /* private function createSchema(): void
    {
        $this->pdo->exec("
            CREATE TABLE demandes (
                id INTEGER PRIMARY KEY,
                sujet TEXT,
                date_creation DATE,
                batiment TEXT,
                lieu TEXT,
                demandeur TEXT,
                statut TEXT,
                site TEXT
            );
        ");
    }
*/
    private function insertTestData1(): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO demandes (sujet, date_creation, batiment, lieu, demandeur, statut, site)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute(['Sujet A', '2024-01-10', 'Bat A', 'Lieu A', 'Alice', 'Ouvert', 'Site A']);
        $stmt->execute(['Sujet B', '2024-02-15', 'Bat B', 'Lieu B', 'Bob', 'Fermé', 'Site B']);
    }
    private function insertTestData(): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO demandes (sujet, date_creation, batiment, lieu, demandeur, statut, site)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute(['Sujet A', '2024-01-10', 'Bat A', 'Lieu A', 'Alice', 'Ouvert', 'Site A']);
        $stmt->execute(['Sujet B', '2024-02-15', 'Bat B', 'Lieu B', 'Bob', 'Fermé', 'Site B']);
    }

    public function testGetDemandesParDatesWithRange(): void
    {
        $filters = [
            'date_debut' => '2024-01-01',
            'date_fin' => '2024-01-31',
        ];

        $results = getDemandesParDates($this->pdo, $filters);

        $this->assertCount(1, $results);
        $this->assertEquals('Sujet A', $results[0]['sujet']);
    }

    public function testGetDemandesParDatesWithoutFilters(): void
    {
        $filters = [];
        $results = getDemandesParDates($this->pdo, $filters);

        $this->assertCount(2, $results);
    }
}
?>
