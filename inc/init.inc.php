<?php
// CONNEXION À LA BASE
$bdd = new PDO('mysql:host=localhost;dbname=lokisalle;charset=utf8', 'root', 'root', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

// SESSION
session_start();

// CHEMINS
define('RACINE_SITE', '/lokisalle/');
//define('CHEMIN_ACTUEL', $_SERVER['REQUEST_URI']);

// INCLUSION DES FONCTIONS
require_once($_SERVER['DOCUMENT_ROOT'] . RACINE_SITE . 'inc/fonctions.inc.php');

// VARIABLE CONTENU
$contenu = "";
$erreur = "";

?>
