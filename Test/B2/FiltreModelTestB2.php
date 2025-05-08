<?php
define('PHPUNIT_RUNNING', true);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../Model/B2/filtreModelB2.php';
require_once __DIR__ . '/../../Model/ModeleDBB2.php';

class FiltreModelTest extends TestCase
{
    function extraireFiltresDepuisPost(array $post): array {
        $date_debut = null;
        if (!empty($post['date_debut_jour']) && !empty($post['date_debut_mois']) && !empty($post['date_debut_annee'])) {
            $date_debut = sprintf('%04d-%02d-%02d', $post['date_debut_annee'], $post['date_debut_mois'], $post['date_debut_jour']);
        }
    
        $date_fin = null;
        if (!empty($post['date_fin_jour']) && !empty($post['date_fin_mois']) && !empty($post['date_fin_annee'])) {
            $date_fin = sprintf('%04d-%02d-%02d', $post['date_fin_annee'], $post['date_fin_mois'], $post['date_fin_annee']);
        }
    
        return [
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
        ];
    }
    public function testAvecDatesCompletes()
    {
        $post = [
            'date_debut_jour' => '01',
            'date_debut_mois' => '04',
            'date_debut_annee' => '2024',
            'date_fin_jour' => '30',
            'date_fin_mois' => '04',
            'date_fin_annee' => '2024',
        ];

        $result =         $filters = $this->extraireFiltresDepuisPost($post);
        ($post);

        $this->assertEquals('2024-04-01', $result['date_debut']);
        $this->assertEquals('2024-04-30', $result['date_fin']);
    }

    public function testAvecDateDebutSeulement()
    {
        $post = [
            'date_debut_jour' => '15',
            'date_debut_mois' => '03',
            'date_debut_annee' => '2023',
        ];

        $result = $this->extraireFiltresDepuisPost($post);

        $this->assertEquals('2023-03-15', $result['date_debut']);
        $this->assertNull($result['date_fin']);
    }

    public function testAvecDateFinSeulement()
    {
        $post = [
            'date_fin_jour' => '10',
            'date_fin_mois' => '12',
            'date_fin_annee' => '2022',
        ];

        $result = $this->extraireFiltresDepuisPost($post);

        $this->assertNull($result['date_debut']);
        $this->assertEquals('2022-12-10', $result['date_fin']);
    }

    public function testAvecAucuneDate()
    {
        $post = [];

        $result = $this->extraireFiltresDepuisPost($post);

        $this->assertNull($result['date_debut']);
        $this->assertNull($result['date_fin']);
    }

    public function testAvecDateIncomplète()
    {
        $post = [
            'date_debut_jour' => '05',
            'date_debut_mois' => '07'
            // année manquante
        ];

        $result = $this->extraireFiltresDepuisPost($post);

        $this->assertNull($result['date_debut']);
    }
}
?>