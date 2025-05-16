<?php

define('PHPUNIT_RUNNING', true);
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/TestInit.php';

class TachesControllerTest extends TestCase
{
    protected static $dbInitialized = false;
    protected $controller;

    public static function setUpBeforeClass(): void
    {
        if (!self::$dbInitialized) {
            initTestDatabase();
            self::$dbInitialized = true;
        }
    }

    protected function setUp(): void
    {
        if (!defined('BASE_URL')) {
            define('BASE_URL', '/'); // définie pour éviter l’erreur dans la vue
        }

        require_once __DIR__ . '/../../Model/ModeleDBB2.php';
        require_once __DIR__ . '/../../Model/B1/Taches.php';
        require_once __DIR__ . '/../../Model/B1/Localite/Site.php';
        require_once __DIR__ . '/../../Model/B1/Localite/Batiment.php';
        require_once __DIR__ . '/../../Model/B1/Localite/Lieu.php';
        require_once __DIR__ . '/../../Model/B1/Localite/Statut.php';
        require_once __DIR__ . '/../../Model/B1/Utilisateur.php';
        require_once __DIR__ . '/../../Model/B1/Media.php';
        require_once __DIR__ . '/../../Model/B1/Demande.php'; // Doit contenir DemandeB1
        require_once __DIR__ . '/../../Controller/B1/TachesController.php';

        Database::resetInstance();

        $_SESSION = [
            'user' => [
                'id' => 1,
                'role_id' => 1
            ],
            'logged_in' => true
        ];

        $this->controller = new TachesController();
    }

    protected function tearDown(): void 
    {
        $_POST = [];
        $_GET = [];
        $_FILES = [];
    }

    public function testCreate()
    {
        $_GET['id'] = 1;

        $demande = DemandeB1::getById($_GET['id']);
        $this->assertNotEmpty($demande, "🚨 La demande ID 1 n'existe pas dans la base de test.");

        ob_start();
        $this->controller->create();
        $output = ob_get_clean();

        $this->assertStringContainsString('Création d\'une tâche', $output);
        $this->assertStringContainsString('form', $output);
        $this->assertStringContainsString('name="nom_tache"', $output);
        $this->assertStringContainsString('name="technicien"', $output);
    }

    public function testEdit()
    {
        $data = [
            'nom_tache' => 'Tâche pour test edit ' . uniqid(),
            'technicien' => 2,
            'date' => date('Y-m-d'),
            'date_planif_tache' => date('Y-m-d', strtotime('+1 day')),
            'id_demande' => 1,
            'description' => 'Description pour test edit',
            'statut' => 1,
            'site' => 1,
            'batiment' => 1,
            'lieu' => 1
        ];

        Taches::createTask($data);

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id_tache FROM tache WHERE sujet_tache = ?");
        $stmt->execute([$data['nom_tache']]);
        $taskId = $stmt->fetchColumn();

        $tache = Taches::getTacheById($taskId);

        $this->assertNotEmpty($tache);
        $this->assertEquals($data['nom_tache'], $tache['sujet_tache']);
        $this->assertEquals($data['description'], $tache['description_tache']);
        $this->assertStringContainsString($data['date_planif_tache'], $tache['date_planif_tache']);
    }

    public function testUpdate()
    {
        $data = [
            'nom_tache' => 'Tâche pour test update ' . uniqid(),
            'technicien' => 2,
            'date' => date('Y-m-d'),
            'date_planif_tache' => date('Y-m-d', strtotime('+1 day')),
            'id_demande' => 1,
            'description' => 'Description pour test update',
            'statut' => 1,
            'site' => 1,
            'batiment' => 1,
            'lieu' => 1
        ];

        Taches::createTask($data);

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id_tache FROM tache WHERE sujet_tache = ?");
        $stmt->execute([$data['nom_tache']]);
        $taskId = $stmt->fetchColumn();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'id_tache' => $taskId,
            'nom_tache' => 'Tâche mise à jour par test',
            'technicien' => 2,
            'date_planif_tache' => date('Y-m-d', strtotime('+2 days')),
            'statut' => 2,
            'id_demande' => 1,
            'description' => 'Description mise à jour par test',
            'commentaire_technicien' => 'Commentaire de test pour update',
            'site' => 1,
            'batiment' => 1,
            'lieu' => 1
        ];

        $_FILES = ['media' => ['name' => '', 'tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE]];

        $testController = new class extends TachesController {
            public function update() {
                return Taches::updateTask($_POST);
            }
        };

        $testController->update();
        $tache = Taches::getTacheById($taskId);

        $this->assertEquals($_POST['nom_tache'], $tache['sujet_tache']);
        $this->assertEquals($_POST['commentaire_technicien'], $tache['commentaire_technicien_tache']);
        $this->assertStringContainsString($_POST['date_planif_tache'], $tache['date_planif_tache']);
    }

    public function testTasksForTechnicien()
    {
        $_SESSION['user'] = [
            'id' => 2,
            'role_id' => 2
        ];

        $data = [
            'nom_tache' => 'Tâche pour technicien view ' . uniqid(),
            'technicien' => 2,
            'date' => date('Y-m-d'),
            'date_planif_tache' => date('Y-m-d', strtotime('+1 day')),
            'id_demande' => 1,
            'description' => 'Description pour la vue technicien',
            'statut' => 1,
            'site' => 1,
            'batiment' => 1,
            'lieu' => 1
        ];

        Taches::createTask($data);

        ob_start();
        $this->controller->tasksForTechnicien();
        $output = ob_get_clean();

        $this->assertStringContainsString('Tâches à réaliser', $output);
        $this->assertStringContainsString($data['nom_tache'], $output);
    }
}
