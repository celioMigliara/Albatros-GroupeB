<?php

// ===============================
// files : Demande.php (Modèle)
// Représente une demande d’intervention et contient les accès à la base
// ===============================
require_once(__DIR__ . "/../../Secure/B2/session_secureB2.php");
require_once __DIR__ . '/../ModeleDBB2.php';

// ===============================
// CLASSE : Demande (modèle métier)
// Représente une demande avec ses champs (objet "métier" simple)
// ===============================
class Demande
{
    public string $sujet;
    public string $description;
    public string $site;
    public string $batiment;
    public string $lieu;
    public int $idUtilisateur;
    public array $piecesJointes; // tableau de filess (nomOriginal + chemin)

    /**
     * Initialise une demande à partir d’un tableau associatif
     * @param array $data Données issues du formulaire ou du contrôleur
     */
    public function __construct(array $data)
    {
        $this->sujet         = trim($data['sujet'] ?? '');
        $this->description   = trim($data['description'] ?? '');
        $this->site          = trim($data['site'] ?? '');
        $this->batiment      = trim($data['batiment'] ?? '');
        $this->lieu          = trim($data['lieu'] ?? '');
        $this->idUtilisateur = (int)($data['idUtilisateur'] ?? 0);

        //Verif si piecesJointes exciste dans data et bien un tb, si bien un tb alors filtre pour garde que que les tbs valide
        //filtre car un form mal rempli peut renvoyer un tableau de string ou autre chose
        //si piecesJointes exciste ou pas un tb pas on renvoie un tb vide
        $this->piecesJointes = isset($data['piecesJointes']) && is_array($data['piecesJointes'])
            ? array_filter($data['piecesJointes'], fn($pj) => is_array($pj))
            : [];
    }

    /**
     * Vérifie que tous les champs obligatoires sont bien remplis
     * @return bool true si la demande est valide
     */
    public function estValide(): bool
    {
        return $this->sujet && $this->site && $this->batiment && $this->lieu;
    }
}

// ===============================
// CLASSE : DemandeModel (modèle base de données)
// Gère toutes les requêtes SQL relatives aux demandes
// ===============================
class DemandeModel
{
    private PDO $db;

    /**
     * Initialise la connexion à la base de données
     * accepte un PDO injecté ou on crée une connexion via ModeleDB
     */
    public function __construct(?PDO $pdo = null)
    {
        $this->db = $pdo ?? Database::getInstance()->getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Ajoute une demande d’intervention en base
     * Gère aussi le statut et les filess associés
     * @param Demande $demande Objet métier contenant toutes les infos
     * @return string|array Numéro de ticket ou tableau d’erreur
     */
    public function ajouterDemande(Demande $demande): string|array
    {
        if (mb_strlen($demande->sujet) > 50) {
            return ['success' => false, 'message' => "Le sujet est trop long."];
        }

        if (mb_strlen($demande->description) > 512) {
            return ['success' => false, 'message' => "La description est trop longue."];
        }

        try {
            $idLieu = $this->obteniriIdEndroit($demande->site, $demande->batiment, $demande->lieu);
            if (!$idLieu) {
                return ['success' => false, 'message' => "Lieu invalide."];
            }

            $stmt = $this->db->prepare("
                INSERT INTO demande (sujet_dmd, description_dmd, date_creation_dmd, id_utilisateur, id_lieu)
                VALUES (?, ?, NOW(), ?, ?)
            ");
            $stmt->execute([
                $demande->sujet,
                $demande->description,
                $demande->idUtilisateur,
                $idLieu
            ]);

            $idDemande = $this->db->lastInsertId();
            $numTicket = $this->genererNumeroTicket($idDemande);

            $stmt = $this->db->prepare("UPDATE demande SET num_ticket_dmd = ? WHERE id_demande = ?");
            $stmt->execute([$numTicket, $idDemande]);

            $stmt = $this->db->prepare("SELECT id_statut FROM statut WHERE nom_statut = 'Nouvelle'");
            $stmt->execute();
            $idStatutNouveau = $stmt->fetchColumn();

            if (!$idStatutNouveau) {
                throw new Exception("Le statut 'Nouvelle' n'existe pas dans la table 'statut'.");
            }

            $stmt = $this->db->prepare("
                INSERT INTO est (id_demande, id_statut, date_modif_dmd)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$idDemande, $idStatutNouveau]);

            // Ajout dans media uniquement si piecesJointes est un tableau valide
            if (is_array($demande->piecesJointes)) {
                $stmt = $this->db->prepare("INSERT INTO media (nom_media, url_media, id_demande) VALUES (?, ?, ?)");
                foreach ($demande->piecesJointes as $fichier) {
                    if (!is_array($fichier)) continue;

                    // Utilisez seulement le nom du fichier dans 'nom_media'
                    $stmt->execute([
                        $fichier['nomOriginal'] ?? 'fichier inconnu', // Stocke le nom du fichier
                        $fichier['chemin'] ?? '', // Vous pouvez aussi stocker une référence, comme une URL relative si nécessaire
                        $idDemande
                    ]);
                }
            }

            return $numTicket;
        } catch (Exception $e) {
            error_log("[ERREUR BDD] " . $e->getMessage());
            return ['success' => false, 'message' => "Erreur BDD : " . $e->getMessage()];
        }
    }

    /**
     * Génère un numéro de ticket unique basé sur l'année et l’ID
     * @param int $idDemande L’ID auto-incrémenté de la demande
     * @return string Exemple : "2025-123"
     */
    public function genererNumeroTicket(int $idDemande): string
    {
        $annee = date("Y");
        return $annee . '-' . $idDemande;
    }

    /**
     * Récupère l’ID du lieu correspondant au triplet site/bâtiment/lieu
     * Vérifie aussi que les trois entités sont actives
     * @return int|null L’ID du lieu ou null s’il n’existe pas
     */
    public function obteniriIdEndroit(string $idSite, string $idBatiment, string $idLieu): ?int
    {
        $stmt = $this->db->prepare("
            SELECT l.id_lieu
            FROM lieu l
            JOIN batiment b ON l.id_batiment = b.id_batiment
            JOIN site s ON b.id_site = s.id_site
            WHERE s.id_site = ? AND b.id_batiment = ? AND l.id_lieu = ?
              AND s.actif_site = 1 AND b.actif_batiment = 1 AND l.actif_lieu = 1
        ");
        $stmt->execute([(int)$idSite, (int)$idBatiment, (int)$idLieu]);
        $value = $stmt->fetchColumn(); // Récupère la première colonne de la première ligne qui est retournée avec les bonnes valeurs 
        return $value !== false ? (int)$value : null;
    }
}
