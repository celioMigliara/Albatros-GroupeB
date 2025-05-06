<?php
/*
use PHPUnit\Framework\TestCase;

class TachesTest extends TestCase
{
    protected static $dbInitialized = false;
    
    public static function setUpBeforeClass(): void
    {
        // Initialiser la base de données de test une seule fois avant tous les tests
        require_once __DIR__ . '/../TestInit.php';
        
        if (!self::$dbInitialized) {
            initTestDatabase();
            self::$dbInitialized = true;
        }
    }
    
    protected function setUp(): void
    {
        // Inclure les fichiers nécessaires pour chaque test
        require_once __DIR__ . '/../../Model/Taches.php';
        
        // Réinitialiser la connexion pour être sûr d'utiliser la base de test
        DB::resetConnection();
    }
    
    public function testGetTacheById()
    {
        // Test pour vérifier que la méthode getTacheById fonctionne correctement
        // Nous utilisons l'ID 1 qui est défini dans nos données de test
        $tache = Taches::getTacheById(1);
        
        // Vérifier que la tâche a été récupérée
        $this->assertIsArray($tache);
        $this->assertArrayHasKey('id_tache', $tache);
        $this->assertEquals(1, $tache['id_tache']);
    }
    
    public function testGetTachesFromDemande()
    {
        // Test pour vérifier que la méthode getTachesFromDemande fonctionne correctement
        // Correction: utilisation du nom correct de la méthode
        $taches = Taches::getTachesFromDemande(1);
        
        // Vérifier que les tâches ont été récupérées
        $this->assertIsArray($taches);
        // Nous devrions avoir 2 tâches pour cette demande selon nos données de test
        $this->assertCount(2, $taches);
    }
    
    public function testCreateTask()
    {
        // Données pour créer une nouvelle tâche
        $data = [
            'nom_tache' => 'Nouvelle tâche de test ' . uniqid(),
            'technicien' => 2,  // ID d'un technicien dans les données de test
            'date' => date('Y-m-d'),
            'date_planif_tache' => date('Y-m-d', strtotime('+1 day')), // Ajout du champ requis
            'id_demande' => 1,  // ID d'une demande dans les données de test
            'description' => 'Description pour une nouvelle tâche de test',
            'statut' => 1,      // ID d'un statut dans les données de test
            'site' => 1,        // ID d'un site dans les données de test
            'batiment' => 1,    // ID d'un bâtiment dans les données de test
            'lieu' => 1         // ID d'un lieu dans les données de test
        ];
        
        // Créer la tâche
        $result = Taches::createTask($data);
        
        // Vérifier que la création a réussi
        $this->assertTrue($result);
        
        // Récupérer la dernière tâche créée pour vérifier ses données
        $db = DB::getConnection();
        $stmt = $db->query("SELECT * FROM tache ORDER BY id_tache DESC LIMIT 1");
        $tache = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vérifier que les données correspondent
        $this->assertEquals($data['nom_tache'], $tache['sujet_tache']);
        $this->assertEquals($data['description'], $tache['description_tache']);
        $this->assertEquals($data['technicien'], $tache['id_utilisateur']);
        $this->assertEquals($data['date_planif_tache'], $tache['date_planif_tache']);
    }
    
    public function testUpdateTask()
    {
        // Créer une tâche pour le test avec date_planif_tache
        $nomTache = 'Tâche à mettre à jour ' . uniqid();
        $data = [
            'nom_tache' => $nomTache,
            'technicien' => 2,
            'date' => date('Y-m-d'),
            'date_planif_tache' => date('Y-m-d', strtotime('+1 day')), // Ajout du champ requis
            'id_demande' => 1,
            'description' => 'Description initiale',
            'statut' => 1,
            'site' => 1,
            'batiment' => 1,
            'lieu' => 1
        ];
        
        Taches::createTask($data);
        
        // Récupérer l'ID de la tâche créée
        $db = DB::getConnection();
        $stmt = $db->prepare("SELECT id_tache FROM tache WHERE sujet_tache = ?");
        $stmt->execute([$nomTache]);
        $taskId = $stmt->fetchColumn();
        
        // Données pour la mise à jour
        $updateData = [
            'id_tache' => $taskId,
            'nom_tache' => 'Tâche mise à jour ' . uniqid(),
            'technicien' => 2,
            'date_planif_tache' => date('Y-m-d', strtotime('+2 days')),
            'statut' => 2,
            'id_demande' => 1,
            'commentaire_technicien' => 'Commentaire de test pour mise à jour',
            'description' => 'Description mise à jour',
            'site' => 1,
            'batiment' => 1,
            'lieu' => 1
        ];
        
        // Mettre à jour la tâche
        $result = Taches::updateTask($updateData);
        
        // Vérifier que la mise à jour a réussi
        $this->assertTrue($result);
        
        // Récupérer la tâche mise à jour
        $tache = Taches::getTacheById($taskId);
        
        // Vérifier que les données ont été mises à jour
        $this->assertEquals($updateData['nom_tache'], $tache['sujet_tache']);
        $this->assertEquals($updateData['commentaire_technicien'], $tache['commentaire_technicien_tache']);
    }
    
    public function testGetTasksByTechnicien()
    {
        // ID du technicien de test
        $technicienId = 2;
        
        // Récupérer les tâches du technicien
        $taches = Taches::getTasksByTechnicien($technicienId);
        
        // Vérifier que des tâches ont été récupérées
        $this->assertIsArray($taches);
        $this->assertGreaterThan(0, count($taches));
        
        // Vérifier que toutes les tâches récupérées sont assignées au bon technicien
        foreach ($taches as $tache) {
            $this->assertEquals($technicienId, $tache['id_utilisateur']);
        }
    }
}*/