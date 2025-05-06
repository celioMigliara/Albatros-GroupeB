<?php
define('PHPUNIT_RUNNING', true);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../Controller/B2/LocationControllerB2.php';

class LocationControllerTest extends TestCase
{
    private array $data;
    protected function setUp(): void
    {
        $json = getLocationJson();           // Doit utiliser Database::getInstance() en interne
        $this->assertJson($json);
        $this->data = json_decode($json, true);
    }
    

    public function testStructurePrincipale()
    {
        $this->assertIsArray($this->data);
        $this->assertArrayHasKey('sites', $this->data);
        $this->assertArrayHasKey('batiments', $this->data);
        $this->assertArrayHasKey('lieux', $this->data);
    }

    public function testSitesContiennentIdNom()
    {
        foreach ($this->data['sites'] as $site) {
            $this->assertArrayHasKey('id', $site);
            $this->assertArrayHasKey('nom', $site);
        }
    }

    public function testBatimentsContiennentIdNomIdSite()
    {
        foreach ($this->data['batiments'] as $bat) {
            $this->assertArrayHasKey('id', $bat);
            $this->assertArrayHasKey('nom', $bat);
            $this->assertArrayHasKey('id_site', $bat);
        }
    }

    public function testLieuxContiennentIdNomIdBatiment()
    {
        foreach ($this->data['lieux'] as $lieu) {
            $this->assertArrayHasKey('id', $lieu);
            $this->assertArrayHasKey('nom', $lieu);
            $this->assertArrayHasKey('id_batiment', $lieu);
        }
    }

    public function testCohérenceLiens()
    {
        $siteIds = array_column($this->data['sites'], 'id');
        foreach ($this->data['batiments'] as $bat) {
            $this->assertContains($bat['id_site'], $siteIds);
        }

        $batimentIds = array_column($this->data['batiments'], 'id');
        foreach ($this->data['lieux'] as $lieu) {
            $this->assertContains($lieu['id_batiment'], $batimentIds);
        }
    }
    public function testNomsConnusSontPresentsEnBase()
{
    // Sites
    $nomsSitesAttendus = ['Petite Chaplle'];
    $nomsSitesTrouves = array_column($this->data['sites'], 'nom');
    foreach ($nomsSitesAttendus as $nom) {
        $this->assertContains($nom, $nomsSitesTrouves, "Le site '$nom' n’a pas été trouvé.");
    }

    // Bâtiments
    $nomsBatimentsAttendus = ['ESPIEGLERIE'];
    $nomsBatimentsTrouves = array_column($this->data['batiments'], 'nom');
    foreach ($nomsBatimentsAttendus as $nom) {
        $this->assertContains($nom, $nomsBatimentsTrouves, "Le bâtiment '$nom' n’a pas été trouvé.");
    }

    // Lieux
    $nomsLieuxAttendus = ['wc pmr'];
    $nomsLieuxTrouves = array_column($this->data['lieux'], 'nom');
    foreach ($nomsLieuxAttendus as $nom) {
        $this->assertContains($nom, $nomsLieuxTrouves, "Le lieu '$nom' n’a pas été trouvé.");
    }
}
public function testAucunNomVide()
{
    foreach (['sites', 'batiments', 'lieux'] as $type) {
        foreach ($this->data[$type] as $item) {
            $this->assertNotEmpty($item['nom'], "Un nom vide a été trouvé dans $type.");
        }
    }
}
public function testIdsSontDesEntiersPositifs()
{
    foreach (['sites', 'batiments', 'lieux'] as $type) {
        foreach ($this->data[$type] as $item) {
            $this->assertIsInt($item['id']);
            $this->assertGreaterThan(0, $item['id'], "ID non valide dans $type.");
        }
    }
}
public function testSitesTriesParNom()
{
    $noms = array_column($this->data['sites'], 'nom');
    $sorted = $noms;
    sort($sorted, SORT_NATURAL | SORT_FLAG_CASE);
    $this->assertEquals($sorted, $noms, "Les sites ne sont pas triés par nom.");
}
public function testPasDeDoublonsDansLesNoms()
{
    foreach (['sites', 'batiments', 'lieux'] as $type) {
        $noms = array_column($this->data[$type], 'nom');
        $uniques = array_unique($noms);
        $this->assertCount(count($noms), $uniques, "Doublons détectés dans $type.");
    }
}

}
