<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <title>Inscription</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Contenu/StylesB3.css">
</head>

<body>

    <!-- Overlay pour l'effet arriere flou-->
    <div class="overlay"></div>

    <!-- Div pour le block principale -->
    <div class="block">
        <!-- div pour le block de gauche -->
        <div class="left">

            <div class="button">
                <a href="<?= BASE_URL ?>/connexion" class="btnConnexion">SE CONNECTER</a>
                <a href="<?= BASE_URL ?>/inscription" class="btnInscription">S'INSCRIRE</a>
            </div>

            <img src="<?= BASE_URL ?>/Assets/Images/Logo/Albatros1.png" alt="Logo Albatros" class="logo_Albatros">
        </div>


        <!-- div pour le block de droite -->
        <div class="right">


            <div class="mobile-buttons">
                <a href="<?= BASE_URL ?>/connexion" class="btn-mobile">SE CONNECTER</a>
            </div>

            <h2 class="title">CREATION DE COMPTE</h2>
            <p class="required-note">Les champs marqués d'un <span class="required-asterisk">*</span> sont obligatoires</p>

            <form class="formulaire" id="formulaire-inscription" novalidate>

                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES); ?>">

                <div class="button-back">
                    <button class="back-button-item" id="back-button">
                        <svg height="16" width="16" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 1024 1024">
                            <path d="M874.690416 495.52477c0 11.2973-9.168824 20.466124-20.466124 20.466124l-604.773963 0 188.083679 188.083679c7.992021 7.992021 7.992021 20.947078 0 28.939099-4.001127 3.990894-9.240455 5.996574-14.46955 5.996574-5.239328 0-10.478655-1.995447-14.479783-5.996574l-223.00912-223.00912c-3.837398-3.837398-5.996574-9.046027-5.996574-14.46955 0-5.433756 2.159176-10.632151 5.996574-14.46955l223.019353-223.029586c7.992021-7.992021 20.957311-7.992021 28.949332 0 7.992021 8.002254 7.992021 20.957311 0 28.949332l-188.073446 188.073446 604.753497 0C865.521592 475.058646 874.690416 484.217237 874.690416 495.52477z"></path>
                        </svg>
                        <span>Retour</span>
                    </button>
                </div>

                <div class="checkbox-wrapper-radio">
                    <span class="role-label">Rôle :<span class="required-asterisk">*</span></span>
                    <input id="role-employe" name="role_utilisateur" type="radio" value="3" required checked>
                    <label class="terms-label" for="role-employe">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 200 200" class="checkbox-svg">
                            <mask fill="white" id="mask-employe">
                                <rect height="200" width="200"></rect>
                            </mask>
                            <rect mask="url(#mask-employe)" stroke-width="40" class="checkbox-box" height="200" width="200"></rect>
                            <path stroke-width="15" d="M52 111.018L76.9867 136L149 64" class="checkbox-tick"></path>
                        </svg>
                        <span class="label-text">Employé</span>
                    </label>

                    <input id="role-technicien" name="role_utilisateur" type="radio" value="2" required>
                    <label class="terms-label" for="role-technicien">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 200 200" class="checkbox-svg">
                            <mask fill="white" id="mask-technicien">
                                <rect height="200" width="200"></rect>
                            </mask>
                            <rect mask="url(#mask-technicien)" stroke-width="40" class="checkbox-box" height="200" width="200"></rect>
                            <path stroke-width="15" d="M52 111.018L76.9867 136L149 64" class="checkbox-tick"></path>
                        </svg>
                        <span class="label-text">Technicien</span>
                    </label>
                </div>



                <!-- Nom et Prénom -->
                <div class="formulaire-champs-cote-a-cote">
                    <!-- Nom -->
                    <div class="input-group-custom">
                        <input required="" type="text" id="nom_utilisateur" name="nom_utilisateur" autocomplete="off" class="input-custom" pattern="[a-zA-Z\s\-]+" placeholder=" ">
                        <label class="user-label-custom" for="nom_utilisateur">Nom<span class="required-asterisk">*</span></label>
                    </div>

                    <!-- Prénom -->
                    <div class="input-group-custom">
                        <input required="" type="text" id="prenom_utilisateur" name="prenom_utilisateur" autocomplete="off" class="input-custom" pattern="[a-zA-Z\s\-]+" placeholder=" ">
                        <label class="user-label-custom" for="prenom_utilisateur">Prénom<span class="required-asterisk">*</span></label>
                    </div>
                </div>



                <!-- Bouton qui permet d'afficher la popup pour pouvoir sélectionner les batiments -->
                <div class="formulaire-champs-batiment" id="batiment-section">
                    <label class="label-batiments">Bâtiment(s) :<span class="required-asterisk">*</span></label>
                    <button type="button" class="Bouton-Select-Batiment" onclick="openPopup('batiment-modal')">
                        Sélectionner les bâtiments (<span id="selected-count">0</span>)
                    </button>
                </div>

                <!-- popup batiment -->
                <div id="batiment-modal" class="popup">
                    <!-- div qui permet de gerer le contenu de la popup -->
                    <div class="popup-content">
                        <!-- Bouton pour fermer la popup -->
                        <button class="fermer-popup" type="button" onclick="closePopup()">×</button>
                        <h3>Sélection des bâtiments</h3>
                        <!-- div qui permet de gerer les bouton checkbox -->
                        <div class="checkbox-wrapper-checkbox">
                            <!-- Logique PHP qui permet d'afficher la liste des bâtiments de manière dynamique dans le cas où de nouveaux bâtiments sont ajoutés -->
                            <?php
                            // Vérifie si la liste des bâtiments n'est pas vide
                            if (!empty($batiments)) {

                                // Si des bâtiments existent, on parcourt la liste des bâtiments avec une boucle foreach
                                foreach ($batiments as $index => $batiment) {

                                    // Génère un ID unique pour chaque bâtiment en utilisant l'index
                                    $id = "batiment_" . $index;

                                    // Sécurise et récupère le nom du bâtiment pour l'afficher dans le champ checkbox
                                    $id_batiment = $batiment['Id_batiment']; // Récupérer l'ID
                                    $nom_batiment = htmlspecialchars($batiment['nom_batiment']); // Récupérer le nom

                                    // Création d'un champ de type checkbox pour chaque bâtiment
                                    echo '<input id="' . $id . '" name="batiments_utilisateur[]" type="checkbox" value="' . $id_batiment . '" onchange="updateCounter()">';

                                    // Création d'un label pour chaque checkbox avec un SVG pour personnaliser l'apparence
                                    echo '<label class="terms-label-batiment-choix modal-checkbox-batiment" for="' . $id . '">';

                                    // SVG utilisé pour l'icône de la case à cocher (personnalisée visuellement)
                                    echo '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 200 200" class="checkbox-svg">';
                                    echo '  <mask fill="white" id="mask-' . $id . '">';
                                    echo '    <rect height="200" width="200"></rect>'; // Création du masque de la case
                                    echo '  </mask>';
                                    echo '  <rect mask="url(#mask-' . $id . ')" stroke-width="40" class="checkbox-box" height="200" width="200"></rect>'; // Dessin de la case
                                    echo '  <path stroke-width="15" d="M52 111.018L76.9867 136L149 64" class="checkbox-tick"></path>'; // Chemin de la coche
                                    echo '</svg>';

                                    // Affiche le nom du bâtiment à côté de la case à cocher
                                    echo '<span class="label-text">' . $nom_batiment . '</span>';
                                    echo '</label>';
                                }
                            } else {

                                // Si la liste des bâtiments est vide, affiche un message indiquant qu'il n'y a pas de bâtiments disponibles
                                echo '<p>Aucun bâtiment disponible</p>';
                            }
                            ?>
                        </div>
                        <!-- Bouton pour valider la sélection des bâtiments -->
                        <button type="button" class="btn" onclick="closePopup()">Valider</button>
                    </div>
                </div>



                <!-- Email + Confirmation Email -->
                <div class="formulaire-champs-cote-a-cote">
                    <!-- Email -->
                    <div class="input-group-custom">
                        <input required="" type="email" id="mail_utilisateur" name="mail_utilisateur" autocomplete="off" class="input-custom" placeholder=" ">
                        <label class="user-label-custom" for="mail_utilisateur">Email<span class="required-asterisk">*</span></label>
                    </div>

                    <!-- Confirmation Email -->
                    <div class="input-group-custom">
                        <input required="" type="email" id="confirmer_mail" name="confirmer_mail" autocomplete="off" class="input-custom" placeholder=" ">
                        <label class="user-label-custom" for="confirmer_mail">Confirmer l'email<span class="required-asterisk">*</span></label>
                    </div>
                </div>



                <!-- Mot de passe + Confirmation Mot de passe -->
                <div class="formulaire-champs-cote-a-cote">
                    <!-- Mot de passe -->
                    <div class="input-group-custom">

                        <input required="" type="password" id="mdp_utilisateur" name="mdp_utilisateur" autocomplete="off" class="input-custom" minlength="8" placeholder=" ">
                        <label class="user-label-custom" for="mdp_utilisateur">Mot de passe<span class="required-asterisk">*</span></label>
                        <span class="info-icon" onclick="openPopup('password-popup')">?</span>
                    </div>

                    <!-- Popup conseil pour le mot de passe-->
                    <div id="password-popup" class="popup">
                        <div class="popup-content">
                            <!-- Bouton pour fermer la popup -->
                            <button class="fermer-popup" type="button" onclick="closePopup()">x</button>
                            <h2>Conseils pour créer un mot de passe sécurisé</h2>
                            <h3>Il est essentiel de créer des mot de passe robustes et uniques.</h3>
                            <p>Voici des conseils pour vous aider :</p>
                            <ul>
                                <li>Votre mot de passe doit contenir au moins 8 caractères, incluant au minimum une
                                    lettre minuscule, une lettre majuscule, un chiffre et un caractère spécial.</li>
                                <li>Évitez d'utiliser des informations personnelles évidentes.</li>
                                <li>Assurez-vous que votre mot de passe soit complexe, mais facile à retenir.</li>
                            </ul>
                            <p><strong>Merci !</strong></p>
                        </div>
                    </div>

                    <!-- Confirmation du mot de passe -->
                    <div class="input-group-custom">
                        <input required="" type="password" id="confirmer_mots_de_passe" name="confirmer_mots_de_passe" autocomplete="off" class="input-custom" placeholder=" ">
                        <label class="user-label-custom" for="confirmer_mots_de_passe">Confirmer le mot de passe<span class="required-asterisk">*</span></label>
                    </div>
                </div>

                <!-- Bouton s'inscrire qui valide le formulaire -->
                <div class="zone-bouton-final">
                    <!-- Bouton pour soumettre le formulaire de connexion -->
                    <button class="cssbuttons-io-button" role="button" type="submit">
                        S'inscrire
                        <div class="icon">
                            <!-- Icône pour le bouton de connexion -->
                            <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M16.172 11l-5.364-5.364 1.414-1.414L20 12l-7.778 7.778-1.414-1.414L16.172 13H4v-2z" fill="currentColor"></path>
                            </svg>
                        </div>
                    </button>
                </div>
            </form>
        </div>

        <script>
            // On définit la base URL depuis le PHP pour le JS
            const BASE_URL = <?= json_encode(BASE_URL) ?>;
        </script>
        <script src="<?= BASE_URL ?>/JavaScript/General.js"></script>

</body>

</html>