<?php
require 'vendor/autoload.php';

// On a besoin de Dompdf pour convertir le HTML souhaité en PDF
use Dompdf\Dompdf;
use Dompdf\Options;

class FeuilleDeRoute 
{
    // Constantes pour la pagination
    public const tasksParPage = 3;

    // Fonction pour générer le PDF
    public static function generatePDF($tasks, $prenom, $nom, $debutTask = 1, $nombreDeTask = 1) 
    {
        // On veut les nom/prenoms en miniscule avec la première lettre en majuscule
        $prenom = ucfirst(strtolower($prenom));
        $nom = ucfirst(strtolower($nom));
    
        // Déterminer combien de tâches à afficher
        $maxTasks = $nombreDeTask > 0 ? $nombreDeTask : count($tasks);
        
        // Prendre uniquement les taches pertinentes à nos critères
        $tasks = array_slice($tasks, max($debutTask - 1, 0), $maxTasks);
    
        // Découpage des tâches par page avec un principe de groupes
        $tasksPerPage = $_ENV['TASK_PAR_PAGE_FDR'] ?? self::tasksParPage; 
        $groupes = array_chunk($tasks, $tasksPerPage);
        $dateJour = date('d/m/Y');
    
        // Construction du HTML
        $html = "<html><head><style>
            body { font-family: DejaVu Sans, sans-serif; padding: 30px; font-size: 13px; color: #000; }
            h1 { text-align: center; font-size: 22px; margin-bottom: 30px; color: #000; }
            .info { margin-bottom: 20px; font-weight: bold; }
            .tache {
                border: 1px solid #aaa;
                padding: 10px 15px;
                margin-bottom: 20px;
                border-radius: 5px;
                background-color: #f2f2f2;
                position: relative;
            }
            .tache h2 {
                margin: 0 0 10px;
                font-size: 16px;
                font-weight: bold;
                color: #111;
            }
            .meta, .localisation {
                color: #333;
                font-size: 12px;
                margin-bottom: 5px;
            }
            .description, .commentaire {
                margin-top: 10px;
                font-size: 13px;
                color: #111;
            }
            .checkbox {
                position: absolute;
                top: 10px;
                right: 15px;
                text-align: center;
                font-size: 11px;
                color: #333;
            }
            .checkbox label {
                display: block;
                margin-bottom: 3px;
                font-weight: bold;
            }
            .checkbox input[type='checkbox'] {
                transform: scale(2);
                margin-top: 10px;
                background-color: #f2f2f2;
            }
            .page { page-break-after: always; }
            .footer {
                position: fixed;
                bottom: -30px;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 11px;
                color: #667;
            }
        </style></head><body>";

        $numeroOrdre = 1;

        foreach ($groupes as $index => $pageTasks) {
            $html .= "<div class='page'>";
            $html .= "<h1>Feuille de route - $prenom $nom</h1>";
            $html .= "<div class='info'>Date : $dateJour</div>";

            foreach ($pageTasks as $task) {

                // On setup les données avec le format souhaité
                $planif = $task['date_planif_tache'] ? date('d/m/Y H:i', strtotime($task['date_planif_tache'])) : 'Non planifiée';
                $lieu = ucfirst(strtolower($task['nom_lieu'] ?? ''));
                $batiment = ucfirst(strtolower($task['nom_batiment'] ?? ''));
                $site = ucfirst(strtolower($task['nom_site'] ?? ''));
                $ticket = htmlspecialchars($task['num_ticket_dmd'] ?? 'N/A');
                $sujet = htmlspecialchars($task['sujet_tache'] ?? '');
                $description = nl2br(htmlspecialchars($task['description_tache'] ?? ''));
                $commentaire = nl2br(htmlspecialchars($task['commentaire_technicien_tache'] ?? ''));

                // On rajoute les données pour l'affichage
                $html .= "<div class='tache'>
                    <div class='checkbox'>
                        <label>Tâche complétée</label>
                        <input type='checkbox'>
                    </div>
                    <h2>#{$numeroOrdre} - $sujet</h2>
                    <div class='meta'><strong>Ticket :</strong> $ticket &nbsp; | &nbsp; <strong>Planifiée le :</strong> $planif</div>
                    <div class='localisation'><strong>Site :</strong> $site &nbsp; | &nbsp; <strong>Bâtiment :</strong> $batiment &nbsp; | &nbsp; <strong>Lieu :</strong> $lieu</div>
                    <div class='description'><strong>Description :</strong><br>$description</div>
                    <div class='commentaire'><strong>Commentaire :</strong><br>$commentaire</div>
                </div>";

                // On incrémente le compteur pour que la prochaine tache est le numéro d'ordre "virtuel" suivant
                $numeroOrdre++;
            }

            $html .= "</div>"; // .page
        }

        $html .= "</body></html>";

        // Configuration Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
    
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
    
        // Ajout pagination visuelle
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('Helvetica');
        $canvas->page_text(520, 820, "Page {PAGE_NUM} / {PAGE_COUNT}", $font, 10, [0, 0, 0]);
    
        // Nom de fichier unique avec date
        $dateNom = date('Y-m-d');
        $NomFichier = "fdr_{$prenom}_{$nom}_$dateNom.pdf";
    
        $dompdf->stream($NomFichier, ["Attachment" => false]);
    }    
}
