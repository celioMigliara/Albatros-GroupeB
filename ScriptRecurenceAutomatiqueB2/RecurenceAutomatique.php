<?php

require_once __DIR__ . '/../Model/B2/DemandeB2.php'; // pour DemandeModel

class RecurrenceService
{
    private PDO $pdo;
    private DateTime $date;
    private DemandeModel $demandeModel;

    // Constructeur : initialise la connexion PDO, la date et le modèle
    //Date pour les tests 
    public function __construct(PDO $pdo, ?DateTime $date = null)
    {
        $this->pdo = $pdo;
        $this->date = $date ?? new DateTime(); //Si date pas null on l'utilise si null on pred dateTime
        $this->demandeModel = new DemandeModel();
    }

    /**
     * Génère automatiquement les demandes récurrentes prévues pour aujourd’hui
     */
    public function genererDemandes(): array
    {
        $logs = [];
        //recup date du jour
        $jourJStr = $this->date->format('Y-m-d');

        // Récupération de l'utilisateur "Systeme"
        $stmt = $this->pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE nom_utilisateur = ?");
        $stmt->execute(['Systeme']);
        $idUtilisateurSysteme = $stmt->fetchColumn();

        if (!$idUtilisateurSysteme) {
            throw new RuntimeException("L'utilisateur 'Systeme' est introuvable dans la base de données.");
        }

        // Récupération de toutes les récurrences
        $stmt = $this->pdo->query("SELECT * FROM recurrence");
        $recs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //verif si pas deja fait ajourdhui
        //calcul la prochaine date avec rappel ou pas
        //si aujourdhui on cree la demande
        foreach ($recs as $rec) {
            $logs[] = "Traitement de la récurrence ID {$rec['id_recurrence']}";

            // Vérifie si une demande a déjà été générée aujourd'hui
            $check = $this->pdo->prepare("
                SELECT COUNT(*) FROM demande WHERE id_recurrence = ? AND DATE(date_creation_dmd) = ? 
            ");
            $check->execute([$rec['id_recurrence'], $jourJStr]);
            if ($check->fetchColumn() > 0) {
                $logs[] = "Demande déjà générée aujourd'hui.";
                continue;
            }

            // Calcul de la date de récurrence avec rappel
            $prochaineDateAvecRappel = self::calculerProchaineDateAvecRappel(
                $rec['date_anniv_recurrence'],
                (int) $rec['valeur_freq_recurrence'],
                (int) $rec['valeur_rappel_recurrence'],
                (int) $rec['id_unite'],     // fréquence
                (int) $rec['id_unite_1'],   // rappel 
                $this->date
            );

            // Si la date calculée est null ou si elle n'est pas celle d'aujourd'hui, on passe
            // Cette condition gère les cas où il n'y a pas de rappel ou quand la date n'est pas aujourd'hui

            // Ajout du log ici avant le check
            if ($prochaineDateAvecRappel !== null) {
                $logs[] = "Prochaine date avec rappel : " . $prochaineDateAvecRappel->format('Y-m-d');
            }

            if ($prochaineDateAvecRappel === null || $prochaineDateAvecRappel->format('Y-m-d') !== $jourJStr) {
                $logs[] = "Pas prévu aujourd'hui.";
                continue;
            }

            // Récupère l’ID suivant pour le ticket
            $res = $this->pdo->query("SELECT MAX(id_demande) AS max_id FROM demande");
            $id = ($res->fetch()['max_id'] ?? 0) + 1;

            // Génère le ticket via DemandeModel
            $ticket = $this->demandeModel->genererNumeroTicket($id);

            // Insertion de la demande
            $insert = $this->pdo->prepare("
                INSERT INTO demande (num_ticket_dmd, sujet_dmd, description_dmd, date_creation_dmd, id_recurrence, id_utilisateur, id_lieu)
                VALUES (?, ?, ?, NOW(), ?, ?, ?)
            ");
            $insert->execute([
                $ticket,
                $rec['sujet_reccurrence'],
                $rec['desc_recurrence'],
                $rec['id_recurrence'],
                $idUtilisateurSysteme, // Utilisation de l'utilisateur "Systeme"
                $rec['id_lieu']
            ]);

            $idDemande = $this->pdo->lastInsertId();

            // Cherche l’ID réel du statut "Nouvelle"
            $stmt = $this->pdo->prepare("SELECT id_statut FROM statut WHERE nom_statut = 'Nouvelle'");
            $stmt->execute();
            $idStatut = $stmt->fetchColumn();

            if (!$idStatut) {
                throw new RuntimeException("Statut 'Nouvelle' introuvable dans la table `statut`.");
            }

            $stmtStatut = $this->pdo->prepare("
             INSERT INTO est (id_demande, id_statut, date_modif_dmd)
              VALUES (?, ?, NOW())
             ");
            $stmtStatut->execute([$idDemande, $idStatut]);

            // Met à jour la date anniversaire pour préparer la prochaine génération
            $prochaineDateAnniv = new DateTime($rec['date_anniv_recurrence']);
            $prochaineDateAnniv->add(new DateInterval(match ($rec['id_unite']) {
                1 => "P{$rec['valeur_freq_recurrence']}D",
                2 => "P{$rec['valeur_freq_recurrence']}M",
                3 => "P{$rec['valeur_freq_recurrence']}Y",
                default => throw new InvalidArgumentException("Unité de fréquence invalide")
            }));

            $update = $this->pdo->prepare("UPDATE recurrence SET date_anniv_recurrence = ? WHERE id_recurrence = ?");
            $update->execute([$prochaineDateAnniv->format('Y-m-d'), $rec['id_recurrence']]);

            $logs[] = "Date anniversaire mise à jour : " . $prochaineDateAnniv->format('Y-m-d');


            $logs[] = "Demande générée : $ticket";
        }

        return $logs;
    }

    /**
     * Calcule la prochaine date de récurrence ajustée avec rappel
     * date d'anniv initiale, de la frequence, et évnetuel rappel si il y a
     */
    public static function calculerProchaineDateAvecRappel(
        string $dateAnniv,
        int $valeurFreq,
        int $valeurRappel,
        int $idUniteFrequence,
        int $idUniteRappel,
        DateTime $dateCourante
    ): ?DateTime {


        if ($valeurFreq <= 0)
            return null;

        // Si le rappel est 0, on retourne simplement la date actuelle (car il n'y a pas de soustraction à faire)
        if ($valeurRappel == 0) {
            return $dateCourante;
        }
        // Détermine l'intervalle en fonction de l'unité de fréquence
        $interval = match ($idUniteFrequence) {
            1 => "P{$valeurFreq}D", // jours
            2 => "P{$valeurFreq}M", // mois
            3 => "P{$valeurFreq}Y", // années
            default => throw new InvalidArgumentException("Unité de fréquence invalide : $idUniteFrequence")
        };

        // Initialisation de la prochaine date 
        $prochaine = new DateTime($dateAnniv);
        $intv = new DateInterval($interval);

        //Boucle jusqu a atteindre la prochaine date
        while ($prochaine < $dateCourante) {
            $prochaine->add($intv);
        }

        // Si un rappel est défini, on ajuste la date
        if ($valeurRappel > 0) {
            // Vérifie l'unité de rappel (id_unite_1)
            $rappelInterval = match ($idUniteRappel) {
                1 => "P{$valeurRappel}D", // jours
                2 => "P{$valeurRappel}M", // mois
                3 => "P{$valeurRappel}Y", // années
                default => throw new InvalidArgumentException("Unité de rappel invalide : $idUniteRappel")
            };
            $prochaine->sub(new DateInterval($rappelInterval)); // Soustraction du rappel
        }

        return $prochaine;
    }
}
