<?php
define('PHPUNIT_RUNNING', true);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../Model/B2/filtreModelB2.php';

class FiltreControllerTest extends TestCase
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
    public function testExtractionFiltreComplet(): void
    {
        $post = [
            'date_debut_jour' => '01',
            'date_debut_mois' => '04',
            'date_debut_annee' => '2024',
            'date_fin_jour' => '30',
            'date_fin_mois' => '04',
            'date_fin_annee' => '2024',
        ];

        $filters = $this->extraireFiltresDepuisPost($post);


        $this->assertEquals('2024-04-01', $filters['date_debut']);
        $this->assertEquals('2024-04-30', $filters['date_fin']);
    }

    public function testExtractionFiltrePartiel(): void
    {
        $post = [
            'date_debut_jour' => '15',
            'date_debut_mois' => '03',
            'date_debut_annee' => '2024',
        ];

        $filters = $this->extraireFiltresDepuisPost($post);

        $this->assertEquals('2024-03-15', $filters['date_debut']);
        $this->assertNull($filters['date_fin']);
    }

    public function testExtractionVide(): void
    {
        $filters = $this->extraireFiltresDepuisPost([]);
        $this->assertNull($filters['date_debut']);
        $this->assertNull($filters['date_fin']);
    }
}
?>