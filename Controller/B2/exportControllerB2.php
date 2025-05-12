<?php
// IMPORTANT : aucune ligne, espace ou saut de ligne ne doit précéder cette balise PHP
ob_start(); // Démarre le tampon de sortie

require_once __DIR__ . '/../../Model/B2/filtreModelB2.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$pdo = Database::getInstance()->getConnection();

// Récupération des données avec jointures
$sql = "SELECT 
    d.num_ticket_dmd as 'Ticket',
    d.sujet_dmd as 'Sujet',
    d.date_creation_dmd as 'Date de création',
    s.nom_site as 'Site',
    b.nom_batiment as 'Bâtiment',
    l.nom_lieu as 'Lieu',
    CONCAT(u.prenom_utilisateur, ' ', u.nom_utilisateur) as 'Demandeur',
    st.nom_statut as 'Statut'
FROM demande d
JOIN lieu l ON d.Id_lieu = l.Id_lieu
JOIN batiment b ON l.Id_batiment = b.Id_batiment
JOIN site s ON b.Id_site = s.Id_site
JOIN utilisateur u ON d.Id_utilisateur = u.Id_utilisateur
LEFT JOIN est e ON d.Id_demande = e.Id_demande
LEFT JOIN statut st ON e.Id_statut = st.Id_statut
ORDER BY d.date_creation_dmd DESC";

$stmt = $pdo->query($sql);
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Création du fichier Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// En-têtes
$headers = [
    'Ticket',
    'Sujet',
    'Date de création',
    'Site',
    'Bâtiment',
    'Lieu',
    'Demandeur',
    'Statut'
];
$sheet->fromArray($headers, NULL, 'A1');

// Formatage de la date
$dateFormat = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY;
$sheet->getStyle('C2:C' . (count($demandes) + 1))->getNumberFormat()->setFormatCode($dateFormat);

// Données
$sheet->fromArray($demandes, NULL, 'A2');

// Ajustement automatique de la largeur des colonnes
foreach (range('A', 'H') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Style des en-têtes
$headerStyle = [
    'font' => ['bold' => true],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'E2E2E2']
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    ]
];
$sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

// Nettoyage du tampon avant d'envoyer les headers
ob_clean();

// Vérification de sécurité (débogage)
if (headers_sent()) {
    die("Erreur : des données ont été envoyées avant les headers !");
}

// Configuration du téléchargement
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="liste_demandes.xlsx"');
header('Cache-Control: max-age=0');

// Génération du fichier
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
