<?php
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Date;
use PhpParser\Node\Stmt;

require_once __DIR__ . '/../ModeleDBB2.php';

class RecurrenceModel {
    private PDO $db;

    public function __construct(PDO $connexion) {
        $this->db = $connexion;
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
 public function ajouterRecurrence($sujet, $description, $dateAnniv, $frequence, $rappel, $idLieu, $uniteFrequence, $uniteRappel) {

        $today = new DateTime(); // Date actuelle
        $dateAnnivObj = new DateTime($dateAnniv); // Conversion de la date en objet DateTime

        // 🔹 Insérer la récurrence
        $idUnite = $this->obtenirIdUnite($uniteFrequence);
        $idUnite1 = $this->obtenirIdUnite($uniteRappel);

        // Vérification que le lieu, le bâtiment et le site sont tous actifs
        $stmt = $this->db->prepare("
            SELECT l.id_lieu
            FROM lieu l
            JOIN batiment b ON l.id_batiment = b.id_batiment
            JOIN site s ON b.id_site = s.id_site
            WHERE l.id_lieu = ? AND l.actif_lieu != 0 AND b.actif_batiment != 0 AND s.actif_site != 0
        ");
        $stmt->execute([$idLieu]);
        $lieuActif = $stmt->fetch();

        if (!$lieuActif) {
            return ['success' => false, 'message' => "Le lieu sélectionné n'est pas valide ou inactif."];
        }

        // 🔹 Vérifications des champs obligatoires
        if (empty($sujet)) {
            return ['success' => false, 'message' => "Entrez un titre pour la maintenance"];
        }
    
        if (empty($frequence)) {
            return ['success' => false, 'message' => "Entrez un nombre pour la fréquence"];
        }
    
        if (!is_numeric($frequence) || $frequence <= 0) {
            return ['success' => false, 'message' => "La fréquence doit être un nombre positif"];
        }

        if (!$idUnite) {
            return ['success' => false, 'message' => "Unité de temps invalide."];
        }
    
        if (empty($idUnite1) && $rappel) {
            return ['success' => false, 'message' => "Vous ne pouvez pas insérer une fréquence de rappel si vous n'avez pas sélectionné une unité de rappel"];
        }

        if($rappel =="" && $idUnite1==""){
            $rappel = 0;
            $idUnite1 = 1;
        }

        if($dateAnnivObj < $today->modify('-1 day')){
            return ['success' => false, 'message' => "La date n'est pas valide"];
        }

        // Si rappel est renseigné, on poursuit les autres vérifications
        if (!empty($rappel)) {

            if (!is_numeric($rappel) || $rappel < 0) {
                return ['success' => false, 'message' => "Le délai de rappel doit être un nombre positif."];
            }

            // Obtenir les durées en jours pour comparer plus logiquement
            $joursFrequence = $this->convertirEnJours($frequence, $idUnite);
            $joursRappel = $this->convertirEnJours($rappel, $idUnite1);

            if ($joursRappel > $joursFrequence) {
                return ['success' => false, 'message' => "Le délai de rappel ne peut pas dépasser la fréquence, toutes unités confondues."];
            }
        }

        // Insertion dans la base de données
        $stmt = $this->db->prepare("INSERT INTO recurrence (sujet_reccurrence, desc_recurrence, date_anniv_recurrence, valeur_freq_recurrence, valeur_rappel_recurrence, id_lieu, id_unite, id_unite_1) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$sujet, $description, $dateAnniv, $frequence, $rappel, $idLieu, $idUnite, $idUnite1]);
    
        return ["success" => true, "message" => "Récurrence ajoutée avec succès !"];
    }

    private function convertirEnJours($valeur, $idUnite) {
        // Mappe les ID d'unités aux jours 
        switch ($idUnite) {
            case 1: // Jour
                return $valeur;
            case 2: // Mois
                return $valeur * 30;
            case 3: // Année
                return $valeur * 365;
            default:
                return 0;
        }
    }

    public function obtenirIdUnite($nomUnite) {
        $stmt = $this->db->prepare("SELECT id_unite FROM unite WHERE nom_unite = ?");
        $stmt->execute([trim($nomUnite)]);
        return $stmt->fetchColumn() ?: null;
    }    

    public function getById($idRecurrence) {
        if (!$idRecurrence) {
            return null;
        }
        try {
            // Récupérer les informations de la maintenance
            $query = "
                SELECT r.id_recurrence, r.sujet_reccurrence, r.date_anniv_recurrence, r.desc_recurrence, 
                r.valeur_freq_recurrence, r.id_unite, u.nom_unite AS nom_unite_frequence, 
                r.valeur_rappel_recurrence, r.id_unite_1, u1.nom_unite AS nom_unite_rappel, 
                s.id_site, b.id_batiment, l.id_lieu
            FROM recurrence r
            JOIN lieu l ON r.id_lieu = l.id_lieu
            JOIN batiment b ON l.id_batiment = b.id_batiment
            JOIN site s ON b.id_site = s.id_site
            LEFT JOIN unite u ON r.id_unite = u.id_unite
            LEFT JOIN unite u1 ON r.id_unite_1 = u1.id_unite
            WHERE r.id_recurrence = :id
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $idRecurrence]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch (Exception) {
            return ["success" => false, "message" => "Erreur lors de la récupération"];
        }
    }
    
    public function update($idRecurrence, $sujet, $description, $dateAnniv, $frequence, $rappel, $idLieu, $uniteFrequence, $uniteRappel) {
        try {

            $today = new DateTime(); // Date actuelle
            $dateAnnivObj = new DateTime($dateAnniv); // Conversion de la date en objet DateTime

           
            $idUnite = $this->obtenirIdUnite($uniteFrequence);
            $idUnite1 = $this->obtenirIdUnite($uniteRappel);

            // Vérification que le lieu, le bâtiment et le site sont tous actifs
            $stmt = $this->db->prepare("
                SELECT l.id_lieu
                FROM lieu l
                JOIN batiment b ON l.id_batiment = b.id_batiment
                JOIN site s ON b.id_site = s.id_site
                WHERE l.id_lieu = ? AND l.actif_lieu != 0 AND b.actif_batiment != 0 AND s.actif_site != 0
            ");
            $stmt->execute([$idLieu]);
            $lieuActif = $stmt->fetch();

            if (!$lieuActif) {
                return ['success' => false, 'message' => "Le lieu sélectionné n'est pas valide ou inactif."];
            }

            if (!is_numeric($frequence) || $frequence <= 0) {
                return ['success' => false, 'message' => "La fréquence doit être un nombre positif"];
            }

            if (!$idUnite) {
                return ['success' => false, 'message' => "Unité de temps invalide."];
            }

            // Si rappel est renseigné, on poursuit les autres vérifications
            if (!empty($rappel)) {

                if (!is_numeric($rappel) || $rappel < 0) {
                    return ['success' => false, 'message' => "Le délai de rappel doit être un nombre positif ou alors 0 si vous ne voulez pas de rappel."];
                }

                 // Obtenir les durées en jours pour comparer plus logiquement
                $joursFrequence = $this->convertirEnJours($frequence, $idUnite);
                $joursRappel = $this->convertirEnJours($rappel, $idUnite1);

                if ($joursRappel > $joursFrequence) {
                    return ['success' => false, 'message' => "Le délai de rappel ne peut pas dépasser la fréquence, toutes unités confondues."];
                }
            }

            if($rappel =="" && $idUnite1==""){
                $rappel = 0;
                $idUnite1 = 1;
            }

            if (empty($idUnite1) && $rappel) {
                return ['success' => false, 'message' => "Vous ne pouvez pas insérer une fréquence de rappel si vous n'avez pas sélectionné une unité de rappel"];
            }

            if($dateAnnivObj < $today->modify('-1 day')){
                return ['success' => false, 'message' => "La date n'est pas valide"];
            }

            $stmt = $this->db->prepare("
                UPDATE recurrence 
                SET sujet_reccurrence = ?, 
                    desc_recurrence = ?, 
                    date_anniv_recurrence = ?, 
                    valeur_freq_recurrence = ?, 
                    valeur_rappel_recurrence = ?, 
                    id_unite = ?, 
                    id_unite_1 = ?,
                    id_lieu = ?
                WHERE id_recurrence = ?
            ");

            $stmt->execute([$sujet, $description, $dateAnniv, $frequence, $rappel, $idUnite, $idUnite1,  $idLieu, $idRecurrence]);

            return ['success' => true, 'message' => "Récurrence mise à jour avec succès !"];
        } catch (Exception $e) {
            return ['success' => false, 'message' => "Erreur BDD : " . $e->getMessage()];
        }
    }

  public function delete($idRecurrence) {

    if (!$idRecurrence) {
        return ['success' => false, 'message' => "ID de la récurrence est vide."];
    }

    try {
        $this->db->beginTransaction();

        //  Récupérer toutes les demandes liées à la récurrence
        $stmt = $this->db->prepare("SELECT id_demande FROM demande WHERE id_recurrence = ?");
        $stmt->execute([$idRecurrence]);
        $demandes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($demandes)) {
            // Préparer la clause IN pour les demandes
            $placeholdersDemandes = implode(',', array_fill(0, count($demandes), '?'));

            // Récupérer les taches liées à ces demandes
            $stmtTache = $this->db->prepare("SELECT id_tache FROM tache WHERE id_demande IN ($placeholdersDemandes)");
            $stmtTache->execute($demandes);
            $taches = $stmtTache->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($taches)) {
                // Supprimer les historiques liés aux taches
                $placeholdersTaches = implode(',', array_fill(0, count($taches), '?'));
                $stmtHisto = $this->db->prepare("DELETE FROM historique WHERE id_tache IN ($placeholdersTaches)");
                $stmtHisto->execute($taches);
            }

            // Supprimer les taches
            $stmtDelTache = $this->db->prepare("DELETE FROM tache WHERE id_demande IN ($placeholdersDemandes)");
            $stmtDelTache->execute($demandes);

            // Supprimer les lignes dans est
            $stmtEst = $this->db->prepare("DELETE FROM est WHERE id_demande IN ($placeholdersDemandes)");
            $stmtEst->execute($demandes);

            // Supprimer les demandes elles-mêmes
            $stmtDemande = $this->db->prepare("DELETE FROM demande WHERE id_recurrence = ?");
            $stmtDemande->execute([$idRecurrence]);
        }

        // Supprimer la récurrence
        $stmtRecurrence = $this->db->prepare("DELETE FROM recurrence WHERE id_recurrence = ?");
        $stmtRecurrence->execute([$idRecurrence]);

        $this->db->commit();

        return ['success' => true, 'message' => "Récurrence (et toutes ses dépendances) supprimée avec succès."];

    } catch (Exception $e) {
        $this->db->rollBack();
        return ['success' => false, 'message' => "Erreur lors de la suppression : " . $e->getMessage()];
    }
}

}
?>