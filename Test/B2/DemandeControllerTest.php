<?php
define('PHPUNIT_RUNNING', true);

use PHPUnit\Framework\TestCase;

// Pour désactiver la vérification CSRF pendant les tests

// Inclusion des filess nécessaires
require_once __DIR__ . '/../../Controller/B2/DemandeControllerB2.php'; // contient la fonction traiter()
require_once __DIR__ . '/../../Model/B2/DemandeB2.php';

class DemandeControllerTest extends TestCase
{
    private PDO $pdo;
    private DemandeModel $model;

    protected function setUp(): void
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->model = new DemandeModel($this->pdo);
    }
    
    
    private function creerfilesTemp(string $contenu, int $taille = 1024): string
    {
        $chemin = tempnam('/Test/Uploads', 'upload');
        file_put_contents($chemin, str_pad($contenu, $taille, pad_string: 'X'));
        return $chemin;
    }
  
    public function testEchecVerificationCsrf()
{
    putenv('FORCE_CSRF_TEST=true'); // force l'exécution du bloc CSRF

    $_SESSION['csrf_token'] = 'token_attendu';
    $_SESSION['csrf_token_expire'] = time() + 3600;

    $post = [
        'sujet' => 'Sujet',
        'description' => 'CSRF test',
        'site' => '1',
        'batiment' => '1',
        'lieu' => '1',
        'csrf_token' => 'token_invalide' // mauvais token
    ];

    $res = traiter($post, [], 1, $this->model);

    $this->assertFalse($res['success']);
    $this->assertStringContainsString('Votre session a expiré ou est invalide', $res['message']);

    putenv('FORCE_CSRF_TEST'); // nettoyage
}

    public function testChampsObligatoiresManquants()
    {
        $res = traiter(['sujet' => '', 'site' => '', 'batiment' => '', 'lieu' => ''], [], 1, $this->model);
        $this->assertFalse($res['success']);
    }

    public function testChampAvecEspacesEstRefuse()
    {
        $res = traiter([
            'sujet' => '   ',
            'description' => 'Test',
            'site' => '1',
            'batiment' => '1',
            'lieu' => '1',
        ], [], 1, $this->model);
        $this->assertFalse($res['success']);
    }

    public function testChampsTrim()
    {
        $res = traiter([
            'sujet' => '  Sujet   ',
            'description' => '  Desc  ',
            'site' => ' 1 ',
            'batiment' => ' 1 ',
            'lieu' => ' 1 ',
        ], [], 1, $this->model);
        $this->assertTrue($res['success']);
        $this->assertMatchesRegularExpression('/Ticket : \d{4}-\d+/', $res['message']);
    }

    public function testDescriptionVideEstAcceptee()
    {
        $res = traiter([
            'sujet' => 'Sujet',
            'description' => '',
            'site' => '1',
            'batiment' => '1',
            'lieu' => '1',
        ], [], 1, $this->model);
        $this->assertTrue($res['success']);
    }

    public function testChampsAvecNull()
    {
        $res = traiter([
            'sujet' => null,
            'description' => null,
            'site' => null,
            'batiment' => null,
            'lieu' => null,
        ], [], 1, $this->model);
        $this->assertFalse($res['success']);
    }

    public function testDemandeSansfiles()
    {
        $res = traiter([
            'sujet' => 'Demande simple',
            'description' => 'Sans files',
            'site' => '1',
            'batiment' => '1',
            'lieu' => '1',
        ], [], 1, $this->model);
        $this->assertTrue($res['success']);
    }

   

    public function testfilesTropGros()
    {
        $tmp = $this->creerfilesTemp('X', 35 * 1024 * 1024); // 35 Mo

        $res = traiter([
            'sujet' => 'files trop lourd',
            'description' => 'Test',
            'site' => '1',
            'batiment' => '1',
            'lieu' => '1'
        ], [
            'piece_jointe' => [
                'name' => ['trop.mp4'],
                'tmp_name' => [$tmp],
                'error' => [UPLOAD_ERR_OK],
                'size' => [filesize($tmp)]
            ]
        ], 1, $this->model);

        $this->assertFalse($res['success']);
        $this->assertStringContainsString('erreur est survenue', $res['message']);
    }

    public function testfilesTmpNameInvalide()
    {
        $res = traiter([
            'sujet' => 'files introuvable',
            'description' => 'Erreur chemin',
            'site' => '1',
            'batiment' => '1',
            'lieu' => '1'
        ], [
            'piece_jointe' => [
                'name' => ['test.pdf'],
                'tmp_name' => ['/Test/Uploads/files.pdf'],
                'error' => [UPLOAD_ERR_OK],
                'size' => [512]
            ]
        ], 1, $this->model);

        $this->assertFalse($res['success']);
    }

    public function testTicketCommenceParAnnee()
    {
        $tmp = $this->creerfilesTemp('année');

        $res = traiter([
            'sujet' => 'Verif annee',
            'description' => 'ticket',
            'site' => '1',
            'batiment' => '1',
            'lieu' => '1'
        ], [
            'piece_jointe' => [
                'name' => ['ticket.pdf'],
                 'tmp_name' => [$tmp],
                'error' => [UPLOAD_ERR_OK],
                'size' => [1024]
            ]
        ], 1, $this->model);

        $this->assertTrue($res['success']);
        preg_match('/Ticket : (\d{4})-\d+/', $res['message'], $matches);
        $this->assertEquals(date('Y'), $matches[1]);
    }

    public function testTicketEtStatutNouvelle()
    {
        $res = traiter([
            'sujet' => 'Test statut',
            'description' => 'base de donnees',
            'site' => '1',
            'batiment' => '1',
            'lieu' => '1'
        ], [], 1, $this->model);

        $this->assertTrue($res['success']);
        preg_match('/Ticket : (\d{4}-\d+)/', $res['message'], $matches);
        $ticket = $matches[1];

        // Vérifie l'insertion en base
        $stmt = $this->pdo->prepare("SELECT id_demande FROM demande WHERE num_ticket_dmd = ?");
        $stmt->execute([$ticket]);
        $idDemande = $stmt->fetchColumn();
        $this->assertNotEmpty($idDemande);

        // Vérifie le statut
        $stmt = $this->pdo->prepare("
            SELECT s.nom_statut
            FROM est e
            JOIN statut s ON e.id_statut = s.id_statut
            WHERE e.id_demande = ?
        ");
        $stmt->execute([$idDemande]);
        $statut = $stmt->fetchColumn();

        $this->assertEquals("Nouvelle", $statut);
    }
   
    public function testUploadErreurSysteme()
    {
        $res = traiter([
            'sujet' => 'Erreur systeme',
            'description' => 'upload',
            'site' => '1',
            'batiment' => '1',
            'lieu' => '1'
        ], [
            'piece_jointe' => [
                'name' => ['doc.pdf'],
                'tmp_name' => [''],
                'error' => [UPLOAD_ERR_CANT_WRITE],
                'size' => [1024]
            ]
        ], 1, $this->model);
    
        $this->assertFalse($res['success']);
        $this->assertStringContainsString('erreur est survenue', $res['message']);
    }
    


public function testSujetDescriptionValide()
{
    // Sujet et description valides
    $res = traiter([
        'sujet' => 'Sujet valide',
        'description' => 'Description valide avec apostrophe et tiret',
        'site' => '1',
        'batiment' => '1',
        'lieu' => '1',
    ], [], 1, $this->model);

    $this->assertTrue($res['success']);
    $this->assertMatchesRegularExpression('/Ticket : \d{4}-\d+/', $res['message']);
}
public function testSujetDescriptionAvecAccentsEtPoint()
{
    // Sujet et description valides avec des accents et un point
    $res = traiter([
        'sujet' => 'Sujet avec é è à et point.',
        'description' => 'Description correcte avec des accents apostrophes et tirets.',
        'site' => '1',
        'batiment' => '1',
        'lieu' => '1',
    ], [], 1, $this->model);

    // Vérifie que la demande a bien été envoyée
    $this->assertTrue($res['success'], 'La demande devrait être acceptée');
    $this->assertMatchesRegularExpression('/Ticket : \d{4}-\d+/', $res['message']);
}


public function testSujetDescriptionAvecCaractèresNonAutorises()
{
    // Sujet avec des caractères non autorisés
    $res = traiter([
        'sujet' => 'Sujet123!',
        'description' => 'Description pour sujet!',
        'site' => '1',
        'batiment' => '1',
        'lieu' => '1',
    ], [], 1, $this->model);

    // Vérifie que la validation échoue et retourne le message approprié
    $this->assertFalse($res['success']);
    $this->assertStringContainsString("Seuls les lettres, espaces, apostrophes, tirets, points, virgules et accents sont autorisés dans le sujet.", $res['message']);
    
}

// Vérifie que l’upload échoue si des clés sont manquantes dans $_FILES
public function testGererUploadAvecClesManquantes()
{
    $files = ['piece_jointe' => ['name' => ['test.pdf']]]; // pas de tmp_name, size, error
    $res =gererUpload($files);
    $this->assertTrue($res['erreur']);
    $this->assertStringContainsString('Données de fichier manquantes', $res['message']);
}

public function testFichiersManquants()
{
    // Simuler des données de fichier incomplètes (manque tmp_name, error ou size)
    $files = [
        'piece_jointe' => [
            'name' => ['file1.jpg'],
            'tmp_name' => [''], // tmp_name manquant
            'error' => [UPLOAD_ERR_OK], // pas d'erreur, mais tmp_name manquant
            'size' => [1024], // taille définie, mais tmp_name manquant
        ]
    ];

    // Appel de la fonction gererUpload avec des données incomplètes
    $resultat = gererUpload($files);

    // Vérification que l'erreur a bien été retournée avec le message correct
    $this->assertTrue($resultat['erreur']);
    $this->assertEquals('Fichier temporaire inexistant pour le test : ', $resultat['message']);
}
public function testGererUploadAvecDonneesManquantesParIndex()
{
    // Créer un vrai fichier temporaire pour le premier élément
    $tmp1 = $this->creerfilesTemp('contenu');
    
    // Simuler un tableau de fichiers avec des données manquantes pour le second fichier
    $files = [
        'piece_jointe' => [
            'name' => ['fichier1.pdf', 'fichier2.pdf'], //valide nom
            'tmp_name' => [$tmp1], // Seulement le premier fichier a un tmp_name
            'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK], // Les deux ont des codes d'erreur
            'size' => [1024, 2048] // Les deux ont des tailles
        ]
    ];
    
    $resultat = gererUpload($files);
    
    $this->assertTrue($resultat['erreur']);
    $this->assertStringContainsString("Données de fichier manquantes pour l'index 1", $resultat['message']);}

public function testDifferentsTypesErreurUpload()
{
    // Test pour différents codes d'erreur d'upload
    $erreurTypes = [
        UPLOAD_ERR_INI_SIZE => "Le fichier dépasse la taille maximale autorisée par PHP",
        UPLOAD_ERR_FORM_SIZE => "Le fichier dépasse la taille maximale spécifiée dans le formulaire HTML",
        UPLOAD_ERR_PARTIAL => "Le fichier n'a été que partiellement téléchargé",
        UPLOAD_ERR_NO_TMP_DIR => "Répertoire temporaire manquant",
        UPLOAD_ERR_CANT_WRITE => "Erreur d'écriture sur le disque",
        UPLOAD_ERR_EXTENSION => "Upload stoppé par une extension PHP"
    ];
    
    foreach ($erreurTypes as $code => $messageAttendu) {
        $files = [
            'piece_jointe' => [
                'name' => ['fichier.pdf'],
                'tmp_name' => ['chemin_temp'],
                'error' => [$code],
                'size' => [1024]
            ]
        ];
        
        $resultat = gererUpload($files);
        
        $this->assertTrue($resultat['erreur']);
        $this->assertStringContainsString("Erreur upload file fichier.pdf (code $code)", $resultat['message']);
    }
}
public function testUploadDePlusieursFichiers()
{
    // Créer des fichiers temporaires simulés
    $tmpFile1 = $this->creerfilesTemp('Contenu du fichier 1');
    $tmpFile2 = $this->creerfilesTemp('Contenu du fichier 2');

    // Simuler des données de fichier multiple
    $files = [
        'piece_jointe' => [
            'name' => ['file1.pdf', 'file2.pdf'],
            'tmp_name' => [$tmpFile1, $tmpFile2],
            'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
            'size' => [filesize($tmpFile1), filesize($tmpFile2)]
        ]
    ];

    // Simuler l'upload avec les fichiers
    $resultat = gererUpload($files);

    // Vérifier que l'upload s'est bien passé et que les fichiers sont retournés correctement
    $this->assertFalse($resultat['erreur']);
    $this->assertCount(2, $resultat['files']); // Vérifie qu'il y a 2 fichiers retournés
    $this->assertEquals('file1.pdf', $resultat['files'][0]['nomOriginal']);
    $this->assertEquals('file2.pdf', $resultat['files'][1]['nomOriginal']);
}
    // ===============================
    // TESTS UNITAIRES des fonctions extraites
    // ===============================

    public function testValiderChaineAccepteCaracteresValides()
    {
        $this->assertTrue(validerChaine("Sujet avec éèà-.,"));
        $this->assertTrue(validerChaine("Texte avec accents é â î ô ü ç"));
    }

    public function testValiderChaineRefuseCaracteresInvalides()
    {
        $this->assertFalse(validerChaine("Sujet123"));
        $this->assertFalse(validerChaine("<script>alert</script>"));
        $this->assertFalse(validerChaine("Texte !@#$%^&*()"));
    }

    public function testChampsObligatoiresTousRemplis()
    {
        $post = [
            'sujet' => 'Sujet',
            'site' => 'Site',
            'batiment' => 'B1',
            'lieu' => 'Salle 101'
        ];
        $this->assertTrue(champsObligatoiresRemplis($post));
    }

    public function testNettoyerChaineEnleveEspaces()
    {
        $this->assertEquals("Bonjour", nettoyerChaine("  Bonjour  "));
        $this->assertEquals("", nettoyerChaine(null));
        $this->assertEquals("", nettoyerChaine("     "));
    }

    public function testVerifierCsrfRetourneVraiSiValide()
    {
        $_SESSION['csrf_token'] = 'secure_token';
        $_SESSION['csrf_token_expire'] = time() + 300;

        $this->assertTrue(verifierCsrf('secure_token'));
    }

    public function testVerifierCsrfMauvaisToken()
    {
        $_SESSION['csrf_token'] = 'secure_token';
        $_SESSION['csrf_token_expire'] = time() + 300;

        $this->assertFalse(verifierCsrf('wrong_token'));
    }


}