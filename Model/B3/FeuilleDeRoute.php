<?php
require 'vendor/autoload.php';

// On a besoin de Dompdf pour convertir le HTML souhaité en PDF
use Dompdf\Dompdf;
use Dompdf\Options;

class FeuilleDeRoute 
{
    // Constantes pour la pagination
    public const tasksParPage = 4;

    // Fonction pour générer le PDF
    public static function generatePDF($tasks, $prenom, $nom, $debutPage = 1, $nombreDePages = 1) 
    {
        // On veut les nom/prenoms en miniscule avec la première lettre en majuscule
        $prenom = ucfirst(strtolower($prenom));
        $nom = ucfirst(strtolower($nom));
    
        // Déterminer combien de tâches à afficher
        $maxTasks = $nombreDePages > 0 ? $nombreDePages * self::tasksParPage : count($tasks);
        
        // Prendre uniquement les taches pertinentes à nos critères
        $tasks = array_slice($tasks, max(($debutPage - 1) * self::tasksParPage, 0), $maxTasks);
    
        // Découpage des tâches par page avec un principe de groupes
        $groupes = array_chunk($tasks, self::tasksParPage);
        $dateJour = date('d/m/Y');
    
        // Construction du HTML
        $html = "<html><head><style>
            body { font-family: DejaVu Sans, sans-serif; padding: 30px; font-size: 12px; }
            h1 { text-align: center; font-size: 20px; margin-bottom: 30px; }
            .info { margin-bottom: 20px; }
            .tache { border: 1px solid #aaa; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
            .tache h2 { margin: 0 0 10px; font-size: 16px; }
            .meta { color: #555; font-size: 11px; margin-bottom: 5px; }
            .commentaire, .description { margin-top: 10px; }
            .page { page-break-after: always; }
            .footer { position: fixed; bottom: -30px; left: 0; right: 0; text-align: center; font-size: 11px; color: #666; }
        </style></head><body>";
    
        $numeroOrdre = 1;
        // Génération de chaque page
        foreach ($groupes as $index => $pageTasks) 
        {
            $html .= "<div class='page'>";
            $html .= "<h1>Feuille de route - $prenom $nom</h1>";
            $html .= "<div class='info'>Date : $dateJour</div>";
    
            foreach ($pageTasks as $task) 
            {
                $planif = $task['date_planif_tache'] ? date('d/m/Y H:i', strtotime($task['date_planif_tache'])) : 'Non planifiée';
                $html .= "<div class='tache'>
                    <h2>#{$numeroOrdre} - " . htmlspecialchars($task['sujet_tache']) . "</h2>
                    <div class='meta'>Planifiée le : $planif</div>
                    <div class='description'><strong>Description :</strong><br>" . nl2br(htmlspecialchars($task['description_tache'] ?? '')) . "</div>
                    <div class='commentaire'><strong>Commentaire :</strong><br>" . nl2br(htmlspecialchars($task['commentaire_technicien_tache'] ?? '')) . "</div>
                </div>";
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
