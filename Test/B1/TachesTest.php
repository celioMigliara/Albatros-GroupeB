<?php

use PHPUnit\Framework\TestCase;

define('PHPUNIT_RUNNING', true);

require_once __DIR__ . '/TestInit.php';
require_once __DIR__ . '/../../Model/B1/Taches.php';

class TachesTest extends TestCase
{
    protected static $dbInitialized = false;

    public static function setUpBeforeClass(): void
    {
        if (!self::$dbInitialized) {
            initTestDatabase();
            self::$dbInitialized = true;
        }
    }

    protected function setUp(): void
    {
        Database::resetInstance();
    }

    public function testGetTacheById()
    {
        $tache = Taches::getTacheById(1);
        $this->assertIsArray($tache);
        $this->assertArrayHasKey('id_tache', $tache);
        $this->assertEquals(1, $tache['id_tache']);
    }

    public function testGetTachesFromDemande()
    {
        $taches = Taches::getTachesFromDemande(1);
        $this->assertIsArray($taches);
        $this->assertCount(2, $taches);
    }

    public function testCreateTask()
    {
        $data = [
            'nom_tache' => 'Nouvelle tâche ' . uniqid(),
            'technicien' => 2,
            'date' => date('Y-m-d'),
            'date_planif_tache' => date('Y-m-d', strtotime('+1 day')),
            'id_demande' => 1,
            'description' => 'Test création',
            'statut' => 1,
            'site' => 1,
            'batiment' => 1,
            'lieu' => 1
        ];

        $result = Taches::createTask($data);
        $this->assertTrue($result);

        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM tache ORDER BY id_tache DESC LIMIT 1");
        $tache = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals($data['nom_tache'], $tache['sujet_tache']);
        $this->assertEquals($data['description'], $tache['description_tache']);
        $this->assertEquals($data['technicien'], $tache['id_utilisateur']);
        $this->assertStringStartsWith($data['date_planif_tache'], $tache['date_planif_tache']);
    }

    public function testUpdateTask()
    {
        $nomTache = 'À modifier ' . uniqid();
        $data = [
            'nom_tache' => $nomTache,
            'technicien' => 2,
            'date' => date('Y-m-d'),
            'date_planif_tache' => date('Y-m-d', strtotime('+1 day')),
            'id_demande' => 1,
            'description' => 'Initiale',
            'statut' => 1,
            'site' => 1,
            'batiment' => 1,
            'lieu' => 1
        ];

        Taches::createTask($data);

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id_tache FROM tache WHERE sujet_tache = ?");
        $stmt->execute([$nomTache]);
        $taskId = $stmt->fetchColumn();

        $updateData = [
            'id_tache' => $taskId,
            'nom_tache' => 'Modifiée ' . uniqid(),
            'technicien' => 2,
            'date_planif_tache' => date('Y-m-d', strtotime('+2 days')),
            'statut' => 2,
            'id_demande' => 1,
            'commentaire_technicien' => 'Commentaire test',
            'description' => 'Modifiée',
            'site' => 1,
            'batiment' => 1,
            'lieu' => 1
        ];

        $result = Taches::updateTask($updateData);
        $this->assertTrue($result);

        $tache = Taches::getTacheById($taskId);
        $this->assertEquals($updateData['nom_tache'], $tache['sujet_tache']);
        $this->assertEquals($updateData['commentaire_technicien'], $tache['commentaire_technicien_tache']);
    }

    public function testGetTasksByTechnicien()
    {
        $technicienId = 2;
        $taches = Taches::getTasksByTechnicien($technicienId);

        $this->assertIsArray($taches);
        $this->assertGreaterThan(0, count($taches));

        foreach ($taches as $tache) {
            $this->assertEquals($technicienId, $tache['id_utilisateur']);
        }
    }
}
