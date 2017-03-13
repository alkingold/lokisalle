<?php
require_once('../inc/init.inc.php');
if(!membreConnecteAdmin())
{
	header('location:' . RACINE_SITE);
}

// SUPPRIMER UNE COMMANDE
if(isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id_commande']) && !empty($_GET['id_commande']))
{
	// CHANGER L'ETAT DE PRODUI POUR LIBRE
	$requeteEtat = $bdd->prepare("SELECT * FROM commande WHERE id_commande = :id_commande");
	$requeteEtat->execute(array(
		'id_commande' => $_GET['id_commande']
	));
	$resultatEtat = $requeteEtat->fetch(PDO::FETCH_ASSOC);
	//echo $resultatEtat['id_produit'];
	$requeteEtatUpdate = $bdd->prepare("UPDATE produit SET etat = 'libre' WHERE id_produit = :id_produit");
	$requeteEtatUpdate->execute(array(
		'id_produit' => $resultatEtat['id_produit']
	));

	// SUPPRIMER DE LA TABLE GESTION DES COMMANDES
	$requeteDelete = $bdd->prepare("DELETE FROM commande WHERE id_commande = :id_commande");
	$requeteDelete->execute(array(
		'id_commande' => $_GET['id_commande']
	));

	// SUPPRIMER DE LA SESSION

	if(isset($_SESSION['commande']))
	{
		$indiceSupprimer = array_search($_GET['id_commande'], $_SESSION['commande']);
		array_splice($_SESSION['commande'], $indiceSupprimer, 1);
	}
	if($requeteDelete->rowCount() != 0)
	{
		$nb_lignes = $requeteDelete->rowCount();
		if($nb_lignes == 1)
		{
			$_SESSION['confirmation']['message'] = $nb_lignes . ' commande a été supprimée avec succès';
		}
		elseif($nb_lignes > 1)
		{
			$_SESSION['confirmation']['message'] = $nb_lignes . ' commandes ont été supprimées avec succès';
		}
	}
	else
	{
		$erreur .= 'La commande n\'a pas pu être supprimée'; 
	}
	header('location:' . RACINE_SITE . 'admin/gestion_commandes.php');
}

require_once('../inc/haut.inc.php');

// AFFICHAGE DES MESSAGES
if(isset($_SESSION['confirmation']['message']))
{
	$contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-success">' . $_SESSION['confirmation']['message'] . '</p></div></div>';
}
// messages erreur
if($erreur != "")
{
	$contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-danger">' . $erreur . '</p></div></div>';
}

// AFFICHAGE DE LA LISTE DES COMMANDES
// Récupérer les noms des colonnes
$requeteColonnes = $bdd->query("SELECT * FROM commande");
$contenu .= '<h1>Gestion des commandes</h1>';
$contenu .= '<div class="row"><div class="col-xs-12 col-sm-12 col-lg-12"><div class="table-responsive"><table class="table table-striped table-bordered table-hover"><tr>';
$nombre_colonnes = $requeteColonnes->columnCount();
for($i=0; $i < $nombre_colonnes; $i++)
{
	$champs = $requeteColonnes->getColumnMeta($i);
	if($champs['name'] == 'id_produit')
	{
		$contenu .= '<th>' . $champs['name'] . '</th><th>prix</th>';
	}
	else
	{
		$contenu .= '<th>' . $champs['name'] . '</th>';
	}
}
$contenu .= '<th>actions</th>';
$contenu .= '</tr>';

// AFFICHAGE DU CONTENU DU TABLEAU
$requete = $bdd->query("SELECT commande.id_commande, commande.id_membre, commande.id_produit, DATE_FORMAT(commande.date_enregistrement, '%d/%m/%Y %H:%m') AS date_enreg_fr, membre.id_membre, membre.email, produit.id_produit, DATE_FORMAT(produit.date_arrivee, '%d/%m/%Y') AS date_arrivee_fr, DATE_FORMAT(produit.date_depart, '%d/%m/%Y') AS date_depart_fr, produit.prix, salle.id_salle, salle.titre 
	FROM commande INNER JOIN membre ON commande.id_membre = membre.id_membre
	INNER JOIN (produit INNER JOIN salle ON produit.id_salle = salle.id_salle) ON commande.id_produit = produit.id_produit");


// Récupérer le contenu
// Afficher le contenu de la table
while($ligne = $requete->fetch(PDO::FETCH_ASSOC))
{
	$contenu .= '<tr>';
		$contenu .= '<td>' . $ligne['id_commande'] . '</td>';
		$contenu .= '<td>' . $ligne['id_membre'] . ' - ' . $ligne['email'] . '</td>';
		$contenu .= '<td>' . $ligne['id_produit'] . ' - <i>salle ' . $ligne['titre'] . '</i><br>
			<i>' . $ligne['date_arrivee_fr'] . ' au ' . $ligne['date_depart_fr'] . '</i></td>';
		$contenu .= '<td>' . $ligne['prix'] . ' €</td>';
		$contenu .= '<td>' . $ligne['date_enreg_fr'] . '</td>';
		// rajputer des liens de suppression et de modification
	$contenu .= '<td>';
	$contenu .= '<a href="' . RACINE_SITE . 'profil.php?id_membre=' . $ligne['id_membre'] . '#commandes"><span class="glyphicon glyphicon-search"></span></a>';
	$contenu .= ' <a href="?action=supprimer&id_commande=' . $ligne['id_commande'] . '" OnClick="return(confirm(\'Vous voulez supprimer cette commande ? \'))"><span class="glyphicon glyphicon-trash"></span></a>';
	$contenu .= '</td>';
	$contenu .= '</tr>';
}

$contenu .= '</table></div></div></div>';


echo $contenu;
// EFFACER LES MESSAGES
if(isset($_SESSION['confirmation']['message']) && !isset($_GET['action']))
{
	unset($_SESSION['confirmation']['message']);
}
require_once('../inc/bas.inc.php');