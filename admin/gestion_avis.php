<?php
require_once('../inc/init.inc.php');
if(!membreConnecteAdmin())
{
	header('location:' . RACINE_SITE);
	exit();
}

// SUPPRIMER UN AVIS
if(isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id_avis']) && !empty($_GET['id_avis']))
{
	$id_avis = htmlentities($_GET['id_avis']);
	$requete = $bdd->prepare("DELETE FROM avis WHERE id_avis = :id_avis");
	$requete->execute(array(
		'id_avis' => $id_avis
	));
	$nb_lignes = $requete->rowCount(); 
	//echo $nb_lignes;
	if($requete->rowCount() != 0)
	{
		$nb_lignes = $requete->rowCount();
		if($nb_lignes == 1)
		{
			$_SESSION['confirmation']['message'] = $nb_lignes . ' ligne a été supprimée avec succès';
		}
		elseif($nb_lignes > 1)
		{
			$_SESSION['confirmation']['message'] = $nb_lignes . ' lignes ont été supprimées avec succès';
		}
	}
	else
	{
		$erreur .= 'L\' avis n\'a pas pu être supprimé'; 
	}
	//var_dump($_SESSION['confirmation']['message']);
	header('location:' . RACINE_SITE . 'admin/gestion_avis.php');
}


// AFFICHAGE DES AVIS
// Récupérer les noms des colonnes
$requeteColonnes = $bdd->query("SELECT * FROM avis");

// AFFICHAGE DE LA LISTE DES AVIS
$requete = $bdd->query("SELECT avis.id_avis, avis.id_membre, avis.id_salle, avis.commentaire, avis.note, DATE_FORMAT(avis.date_enregistrement, '%d/%m/%Y %H:%m') AS date_format_fr, membre.email, salle.titre 
	FROM avis INNER JOIN membre ON avis.id_membre = membre.id_membre
	INNER JOIN salle ON avis.id_salle = salle.id_salle");

// ALLER SUR LE PROFIL DU MEMBRE QUI A POSTE L'AVIS
//$requeteSearch = $bdd->prepare("SELECT * FROM avis WHERE id_membre = :id_membre");

require_once('../inc/haut.inc.php');
//var_dump($_SESSION);

$contenu .= '<h1>Gestion des avis </h1>';
if(isset($_SESSION['confirmation']['message']))
{
	$contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-success">' . $_SESSION['confirmation']['message'] . '</p></div></div>';
}
$contenu .= '<div class="row"><div class="col-xs-12 col-sm-12 col-lg-12"><div class="table-responsive"><table class="table table-striped table-bordered table-hover"><tr>';
$nombre_colonnes = $requeteColonnes->columnCount();
for($i=0; $i < $nombre_colonnes; $i++)
{
	$champs = $requeteColonnes->getColumnMeta($i);
	$contenu .= '<th>' . $champs['name'] . '</th>';
}
$contenu .= '<th>actions</th>';
$contenu .= '</tr>';

// Récupérer le contenu
// Afficher le contenu de la table
while($ligne = $requete->fetch(PDO::FETCH_ASSOC))
{
	$contenu .= '<tr>';
		$contenu .= '<td>' . $ligne['id_avis'] . '</td>';
		$contenu .= '<td>' . $ligne['id_membre'] . ' - ' . $ligne['email'] . '</td>';
		$contenu .= '<td>' . $ligne['id_salle'] . ' - salle ' . $ligne['titre'] . '<br></td>';
		$contenu .= '<td>' . htmlentities($ligne['commentaire']) . '</td>';
		// note afficher nombre d'étoiles remplies
		$contenu .= '<td>';
		for($i = 1; $i <= $ligne['note']; $i++)
		{
			$contenu .= ' <span class="glyphicon glyphicon-star"></span> ';
		}
		$contenu .= '</td>';
		$contenu .= '<td>' . $ligne['date_format_fr'] . '</td>';
		// rajputer des liens de suppression et de modification
	$contenu .= '<td>';
	$contenu .= '<a href="' . RACINE_SITE . 'profil.php?id_membre=' . $ligne['id_membre'] . '#avis" target="_blank"><span class="glyphicon glyphicon-search"></span></a>';
	$contenu .= ' <a href="?action=supprimer&id_avis=' . $ligne['id_avis'] . '" OnClick="return(confirm(\'Vous voulez supprimer cet avis ? \'))"><span class="glyphicon glyphicon-trash"></span></a>';
	$contenu .= '</td>';
	$contenu .= '</tr>';
}

$contenu .= '</table></div></div></div>';
echo $contenu;
if(isset($_SESSION['confirmation']['message']) && ((!isset($_GET['action'])) || ($_GET['action'] != 'supprimer')))
{
	unset($_SESSION['confirmation']['message']);
}
require_once('../inc/bas.inc.php');