<?php

//Permet de "calculer" dynamiquement l'URL de base du projet
//Http ou Https, permet de gérer le cas où l'application est sur un serveur local ou en ligne
//scriptPath recup le chemin jusqu'a la racine du projet
//  coupe tout après "/Controller" ou "/View" pour revenir à la racine du projet, ça dépend du final voulu.
function getBaseUrl()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptPath = explode('/Controller', $_SERVER['SCRIPT_NAME'])[0];
    $scriptPath = rtrim($scriptPath, '/');
    return $protocol . '://' . $host . $scriptPath;
}
