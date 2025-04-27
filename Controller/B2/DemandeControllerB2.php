<?php
// ===============================
// Contrôleur : DemandeController.php
// Gère la réception des demandes d'intervention via formulaire AJAX
// ===============================

require_once(__DIR__ . "/../../Secure/B2/session_secureB2.php");
require_once(__DIR__ . '/../../Model/B2/DemandeB2.php');

/**
 * Gère l’upload des fichiers envoyés via le formulaire
 * @return array Résultat avec tableau de fichiers ou message d’erreur
 */ function gererUpload(array $files): array
 {
    $resultats = [];

    if (!isset($files['piece_jointe']) || empty($files['piece_jointe']['name'][0])) {
        return ['erreur' => false, 'filess' => []];
    }

    $tailleMax = 30 * 1024 * 1024;

    if (!isset($files['piece_jointe']['name'], $files['piece_jointe']['tmp_name'], $files['piece_jointe']['error'], $files['piece_jointe']['size'])) {
        return ['erreur' => true, 'message' => "Données de fichier manquantes."];
    }

    foreach ($files['piece_jointe']['name'] as $index => $nom) {
        if (!isset($files['piece_jointe']['tmp_name'][$index], $files['piece_jointe']['error'][$index], $files['piece_jointe']['size'][$index])) {
            return ['erreur' => true, 'message' => "Données de fichier manquantes pour l'index $index."];
        }

        $tmp_name = $files['piece_jointe']['tmp_name'][$index];
        $error = $files['piece_jointe']['error'][$index];
        $size = $files['piece_jointe']['size'][$index];

        if ($error !== UPLOAD_ERR_OK) {
            return ['erreur' => true, 'message' => "Erreur upload file $nom (code $error)"];
        }

        if ($size > $tailleMax) {
            return ['erreur' => true, 'message' => "Le fichier $nom dépasse la taille autorisée de " . ($tailleMax / (1024 * 1024)) . " Mo"];
        }

        $ext = pathinfo($nom, PATHINFO_EXTENSION);
        $nomUnique = basename($nom); // Utilise basename pour s'assurer qu'on n'ajoute pas un chemin complet
        $cheminFinal = __DIR__ . '/../../Public/Uploads/' . $nomUnique;
        $cheminTest = __DIR__ . '/../../Test/Uploads/' . $nomUnique;

        // Vérifie si le code est exécuté dans un contexte de test (PHPUnit)
        if (defined('PHPUNIT_RUNNING')) {

            // Vérifie si le fichier temporaire existe bien avant de tenter de le déplacer
            if (!file_exists($tmp_name)) {
                // Si le fichier temporaire n'existe pas, retourne une erreur spécifique pour les tests
                return ['erreur' => true, 'message' => "Fichier temporaire inexistant pour le test : $tmp_name"];
            }

            // Vérifie si le répertoire de destination pour les tests existe, sinon le crée
            if (!is_dir(dirname($cheminTest))) {
                mkdir(dirname($cheminTest), 0777, true); // Crée le dossier en autorisant aussi les dossiers parents
            }
            // Déplace le fichier temporaire vers le répertoire de test (Test/Uploads)
            rename($tmp_name, $cheminTest);
        } else {
            // Sinon, on est en situation réelle (pas en test)
            // Vérifie si le répertoire de destination réel existe (Public/Uploads), sinon le crée
            if (!is_dir(dirname($cheminFinal))) {
                mkdir(dirname($cheminFinal), 0777, true); // Crée le dossier en autorisant aussi les dossiers parents
            }
            // Déplace le fichier temporaire vers le répertoire public avec move_uploaded_file (sécurisé)
            move_uploaded_file($tmp_name, $cheminFinal);
        }


        // Retourner le chemin correctement formaté sans double '/Public/Uploads/'
        $resultats[] = [
            'nomOriginal' => $nom,
            'chemin' => $nomUnique
        ];
    }

    return ['erreur' => false, 'files' => $resultats];
}
/**
 * Fonction principale pour traiter une soumission de formulaire
 */
function traiter(array $post, array $files, int $userId, DemandeModel $model): array
{
    $sujet = nettoyerChaine($post['sujet'] ?? '');
    $description = nettoyerChaine($post['description'] ?? '');
    $site = nettoyerChaine($post['site'] ?? '');
    $batiment = nettoyerChaine($post['batiment'] ?? '');
    $lieu = nettoyerChaine($post['lieu'] ?? '');

    if (!validerChaine($sujet)) {
        return ['success' => false, 'message' => "Seuls les lettres, espaces, apostrophes, tirets, points, virgules et accents sont autorisés dans le sujet."];
    }

    if (!validerChaine($description)) {
        return ['success' => false, 'message' => "Seuls les lettres, espaces, apostrophes, tirets, points, virgules et accents sont autorisés dans la description."];
    }

    if (!champsObligatoiresRemplis(compact('sujet', 'site', 'batiment', 'lieu'))) {
        return ['success' => false, 'message' => "Champs obligatoires manquants."];
    }

    if (!defined('PHPUNIT_RUNNING') || getenv('FORCE_CSRF_TEST') === 'true') {
        $tokenEnvoye = $post['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!verifierCsrf($tokenEnvoye)) {
            error_log("Échec vérification CSRF : token reçu = $tokenEnvoye, session = " . ($_SESSION['csrf_token'] ?? 'null'));
            return ['success' => false, 'message' => 'Votre session a expiré ou est invalide. Veuillez actualiser la page.'];
        }
        usleep(200000);
    }

    $upload = gererUpload($files);
    if ($upload['erreur']) {
        error_log("Erreur upload : " . $upload['message']);
        return ['success' => false, 'message' => "Une erreur est survenue lors de l’envoi de votre demande. Veuillez réessayer ou contacter le support."];
    }

    $piecesJointes = isset($upload['files']) && is_array($upload['files']) ? $upload['files'] : [];

    $demande = new Demande([
        'sujet' => $sujet,
        'description' => $description,
        'site' => $site,
        'batiment' => $batiment,
        'lieu' => $lieu,
        'idUtilisateur' => $userId,
        'piecesJointes' => $piecesJointes
    ]);

    $ticket = $model->ajouterDemande($demande);

    return is_string($ticket)
        ? ['success' => true, 'message' => "Demande envoyée (Ticket : $ticket)"]
        : ['success' => false, 'message' => $ticket['message'] ?? "Erreur inconnue"];
}

// ===============================
// Fonctions 
// ===============================

function validerChaine(string $texte): bool {
    return (bool) preg_match("/^[a-zA-Zàâäéèêëíîïóôöùûüç' .,-]*$/u", trim($texte));
}

function champsObligatoiresRemplis(array $post): bool {
    return !empty(trim($post['sujet'] ?? '')) &&
           !empty(trim($post['site'] ?? '')) &&
           !empty(trim($post['batiment'] ?? '')) &&
           !empty(trim($post['lieu'] ?? ''));
}

function verifierCsrf(string $tokenEnvoye): bool {
    return isset($_SESSION['csrf_token'], $_SESSION['csrf_token_expire'])
        && time() <= $_SESSION['csrf_token_expire']
        && hash_equals($_SESSION['csrf_token'], $tokenEnvoye);
}


function nettoyerChaine(?string $valeur): string {
    return trim($valeur ?? '');
}

// ===============================
// Point d’entrée POST réel
// ===============================
if (!defined('PHPUNIT_RUNNING') && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $model = new DemandeModel();
    $reponse = traiter($_POST, $_FILES, $_SESSION['user_id'], $model);
    if ($reponse['success'] ?? false) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_expire'] = time() + 360;
        $reponse['nouveau_token'] = $_SESSION['csrf_token'];
    }
    echo json_encode($reponse);
}
