<?php
require_once('../inc/init.inc.php');
if(!membreConnecteAdmin())
{
	header('location:' . RACINE_SITE);
}

// AFFICHAGE DES PRODUITS
// PAGINATION 
$requetePagination = $bdd->query("SELECT * FROM produit");
$nbProduits = $requetePagination->rowCount();
$nbProduitsPage = 6;
//echo $nbProduits . '<br>';
$nbPages = ceil($nbProduits/$nbProduitsPage);
//echo $nbPages . '<br>';


// DEFINITION DU NUMERO DE PAGE
if(isset($_GET['page']) && !empty($_GET['page']) && ((int) $_GET['page'] != 0) && (int) $_GET['page'] <= $nbPages)
{
	$npage = $_GET['page'];
}
elseif(isset($_GET['page']) && !empty($_GET['page']) && $_GET['page'] > $nbPages)
{
	$npage = $nbPages;
}
else
{
	$npage = 1;
}
//echo $npage . '<br>';


$offset = $npage * $nbProduitsPage - $nbProduitsPage;
//echo $offset;

// AJOUTER OU MODIFIER UN PRODUIT

// SI LE FORMULAIRE EST POSTE
$erreur = "";
if($_POST)
{
	// CONTROLES FORMULAIRE
	if(empty($_POST['date_arrivee']))
	{
		$erreur .= 'Veuillez renseigner la date d\'arrivée<br>';
	}

	if(empty($_POST['date_depart']))
	{
		$erreur .= 'Veuillez renseigner la date de départ<br>';
	}

	if(empty($_POST['heure_arrivee']))
	{
		$erreur .= 'Veuillez renseigner l\'heure d\'arrivée<br>';
	}

	if(empty($_POST['heure_depart']))
	{
		$erreur .= 'Veuillez renseigner l\'heure de départ<br>';
	}

	if($_POST['salle'] == 'choisissez_salle')
	{
		$erreur .= 'Veuillez choisir une salle';
	}

	if(empty($_POST['tarif']))
	{
		$erreur .= 'Veuillez renseigner le tarif du produit<br>';
	}
	if(!preg_match("#^[0-9]{1,5}$#", $_POST['tarif']))
	{
		$erreur .= 'Le champ prix doit contenir un nombre<br>';
	}

	// CONVERSION DATE EN FORMAT SQL
	if(isset($_POST['date_arrivee']) && !empty($_POST['date_arrivee']) && isset($_POST['date_depart']) && !empty($_POST['date_depart']) 
		&& isset($_POST['heure_arrivee']) && !empty($_POST['heure_arrivee']) && isset($_POST['heure_depart']) && !empty($_POST['heure_depart']))
	{
		//echo 'heure arrivee ' . $_POST['heure_arrivee'];
		//echo 'heure depart ' . $_POST['heure_depart'];
		$datetime_arrivee = convertDateDb($_POST['date_arrivee'], $_POST['heure_arrivee']);
		//echo $datetime_arrivee;
		$datetime_depart = convertDateDb($_POST['date_depart'], $_POST['heure_depart']);
		//echo $datetime_depart;
	}
	
	// RECUPERER ID SALLE DU POST SALLE
	if(isset($_POST['salle']) && !empty($_POST['salle']))
	{
		$id_salle = (int) $_POST['salle'];
		//echo $id_salle;
	}

	if(isset($_POST['date_arrivee']) && !empty($_POST['date_arrivee']) )
	{
		/*if(new DateTime() > new DateTime($_POST['date_arrivee']))
		{
			$erreur .= 'Impossible d\'ajouter un produit à une date inférieure au jour d\'aujourd\'hui<br>';
		}*/
		if($datetime_arrivee <= date('Y-m-d H:i:s'))
		{
			$erreur .= 'Impossible d\'ajouter ou modifier un produit à une date inférieure au jour d\'aujourd\'hui<br>';
		}
	}

	// VOIR SI UN PRODUIT POUR CES DATES EXISTE (AVEC LE MEME ID SALLE)
	if(isset($_POST['date_arrivee']) && !empty($_POST['date_arrivee']) && isset($_POST['date_depart']) && !empty($_POST['date_depart']) && isset($_POST['salle']) && !empty($_POST['salle']))
	{
		// si modification
		if(isset($_GET['action']) && $_GET['action'] == 'modifier')
		{
			$requeteProduitExiste = $bdd->prepare("SELECT * FROM produit 
				WHERE id_salle = :id_salle AND id_produit != :id_produit AND (date_arrivee BETWEEN :date_arrivee AND :date_depart 
				OR date_depart BETWEEN :date_arrivee AND :date_depart) OR (date_arrivee > :date_arrivee AND date_depart < :date_depart)");
			$requeteProduitExiste->execute(array(
				'date_arrivee' => $datetime_arrivee,
				'date_depart' => $datetime_depart,
				'id_salle' => $id_salle,
				'id_produit' => $_GET['id_produit']
			));
		}
		else
		{
			// si ajout
			$requeteProduitExiste = $bdd->prepare("SELECT * FROM produit 
				WHERE id_salle = :id_salle AND (date_arrivee BETWEEN :date_arrivee AND :date_depart 
				OR date_depart BETWEEN :date_arrivee AND :date_depart) OR (date_arrivee > :date_arrivee AND date_depart < :date_depart)");
			$requeteProduitExiste->execute(array(
				'date_arrivee' => $datetime_arrivee,
				'date_depart' => $datetime_depart,
				'id_salle' => $id_salle
			));
		}
		
		$resultatProduitExiste = $requeteProduitExiste->fetchAll(PDO::FETCH_ASSOC);
		//echo $requeteProduitExiste->rowCount();
		if($requeteProduitExiste->rowCount() != 0)
		{
			$erreur .= 'Le produit pour cette salle avec ces dates existe déjà<br>';
		}
	}

	// MODIFIER LES INFORMATIONS PRODUIT
	if(isset($_GET['action']) && $_GET['action'] == 'modifier' && isset($_GET['id_produit']) && !empty($_GET['id_produit']) && $erreur == "")
	{
		$requete_modifier = $bdd->prepare("UPDATE produit 
			SET date_arrivee = :date_arrivee, date_depart = :date_depart, id_salle = :id_salle, prix = :prix 
			WHERE id_produit = :id_produit");
		$requete_modifier->execute(array(
			'date_arrivee' => $datetime_arrivee,
			'date_depart' => $datetime_depart,
			'id_salle' => $id_salle,
			'prix' => $_POST['tarif'],
			'id_produit' => $_GET['id_produit']
		));
		$nb_lignes = $requete_modifier->rowCount(); 
		//echo $nb_lignes;
		if($requete_modifier->rowCount() != 0)
		{
			$nb_lignes = $requete_modifier->rowCount();
			if($nb_lignes == 1)
			{
				$_SESSION['confirmation']['message'] = $nb_lignes . ' produit a été mis à jour avec succès';
			}
			elseif($nb_lignes > 1)
			{
				$_SESSION['confirmation']['message'] = $nb_lignes . ' produits ont été mis à jour avec succès';
			}
		}
		else
		{
			$_SESSION['confirmation']['erreur'] = 'Le produit n\'a pas pu être mis à jour, probablement il n\'éxiste pas'; 
		}
		if(isset($_GET['tri']) && !empty($_GET['tri']))
		{
			header('location:' . RACINE_SITE . 'admin/gestion_produits.php?page=' . $npage . '&tri=' . $_GET['tri']);
			exit;
		}
		else
		{
			header('location:' . RACINE_SITE . 'admin/gestion_produits.php?page=' . $npage);
			exit;
		}
	}

	// AJOUTER UN PRODUIT
	if(isset($_GET['action']) && $_GET['action'] == 'ajouter' && $erreur == "")
	{
		$requete = $bdd->prepare("INSERT INTO produit(id_salle, date_arrivee, date_depart, prix) 
			VALUES(:id_salle, :date_arrivee, :date_depart, :prix)");
		$requete->execute(array(
			'id_salle' => $id_salle,
			'date_arrivee' => $datetime_arrivee,
			'date_depart' => $datetime_depart,
			'prix' => $_POST['tarif']
		));
		$nb_lignes = $requete->rowCount(); 
		//echo $nb_lignes;
		if($requete->rowCount() != 0)
		{
			$nb_lignes = $requete->rowCount();
			if($nb_lignes == 1)
			{
				$_SESSION['confirmation']['message'] = $nb_lignes . ' produit a été ajouté avec succès';
			}
			elseif($nb_lignes > 1)
			{
				$_SESSION['confirmation']['message'] = $nb_lignes . ' produits ont été ajouté avec succès';
			}
		}
		else
		{
			$_SESSION['confirmation']['erreur'] = 'Le produit n\'a pas pu être ajouté'; 
		}
		if(isset($_GET['tri']) && !empty($_GET['tri']))
		{
			header('location:' . RACINE_SITE . 'admin/gestion_produits.php?page=' . $npage . '&tri=' . $_GET['tri']);
			exit;
		}
		else
		{
			header('location:' . RACINE_SITE . 'admin/gestion_produits.php?page=' . $npage);
			exit;
		}
	}
}

// SUPPRIMER UN PRODUIT
if(isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id_produit']) && !empty($_GET['id_produit']))
{
	// VERIFIER SI EXISTENT COMMANDES AVEC CE PRODUIT
	$requeteProduitCommande = $bdd->prepare("SELECT * FROM commande WHERE id_produit = :id_produit");
	$requeteProduitCommande->execute(array(
		'id_produit' => $_GET['id_produit']
	));
	if($requeteProduitCommande->rowCount() > 0)
	{
		$_SESSION['confirmation']['erreur'] = 'Impossible de supprimer un produit qui a été commandé'; 
		if(isset($_GET['tri']) && !empty($_GET['tri']))
		{
			header('location:' . RACINE_SITE . 'admin/gestion_produits.php?page=' . $npage . '&tri=' . htmlentities($_GET['tri']));
			exit;
		}
		else
		{
			header('location:' . RACINE_SITE . 'admin/gestion_produits.php?page=' . $npage);
			exit;
		}
	}
	else
	{
		$requete = $bdd->prepare("DELETE FROM produit WHERE id_produit = :id_produit");
		$requete->execute(array(
			'id_produit' => $_GET['id_produit']
		));
		$nb_lignes = $requete->rowCount(); 
		//echo $nb_lignes;
		if($requete->rowCount() != 0)
		{
			$nb_lignes = $requete->rowCount();
			if($nb_lignes == 1)
			{
				$_SESSION['confirmation']['message'] = $nb_lignes . ' produit a été supprimé avec succès';
			}
			elseif($nb_lignes > 1)
			{
				$_SESSION['confirmation']['message'] = $nb_lignes . ' produits ont été supprimés avec succès';
			}
		}
		else
		{
			$_SESSION['confirmation']['erreur'] = 'Le produit n\'a pas pu être supprimé, probablement il n\'éxiste pas'; 
		}
		if(isset($_GET['tri']) && !empty($_GET['tri']))
		{
			header('location:' . RACINE_SITE . 'admin/gestion_produits.php?page=' . $npage . '&tri=' . htmlentities($_GET['tri']));
			exit;
		}
		else
		{
			header('location:' . RACINE_SITE . 'admin/gestion_produits.php?page=' . $npage);
			exit;
		}
	}
	
}

// TRI DES PRODUITS

//$criteresPages = "date_arrivee";
if(isset($_GET['tri']) && !empty($_GET['tri']) && ($_GET['tri'] == 'titre' || $_GET['tri'] == 'date_arrivee' || $_GET['tri'] == 'id_produit' || $_GET['tri'] == 'prix' || $_GET['tri'] == 'etat'))
{
	$criteresPages = $_GET['tri'];
}
else
{
	$criteresPages = "date_arrivee";
}
$requetePages = $bdd->prepare("SELECT produit.id_produit, produit.date_arrivee, DATE_FORMAT(produit.date_arrivee,'%d/%m/%Y %H:%i') AS date_arrivee_fr, DATE_FORMAT(produit.date_depart,'%d/%m/%Y %H:%i') AS date_depart_fr, salle.id_salle, salle.titre, salle.photo, salle.adresse, salle.cp, salle.ville, salle.capacite, produit.prix, produit.etat 
	FROM produit 
	INNER JOIN salle ON produit.id_salle = salle.id_salle ORDER BY $criteresPages, date_arrivee ASC LIMIT :offset, :nb");
$requetePages -> bindParam (':offset', $offset, PDO::PARAM_INT);
$requetePages -> bindParam (':nb', $nbProduitsPage, PDO::PARAM_INT);

// 

$requetePages->execute();

// INCLUSION DU HEADER
require_once('../inc/haut.inc.php');
//var_dump($resultatProduitExiste);

//echo date('Y-m-d H:i:s');

$contenu .= '<h1>Gestion des produits</h1>';

if(isset($_SESSION['confirmation']['message']))
{
	$contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-success">' . $_SESSION['confirmation']['message'] . '</p></div></div>';
}
if(isset($_SESSION['confirmation']['erreur']))
{
	$contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-danger">' . $_SESSION['confirmation']['erreur'] . '</p></div></div>';
}

// AFFICHAGE DES BOUTONS DE TRI
$contenu .= '<div class="row"><div class="col-sm-12"><p class="text-center">';

$contenu .= '<a href="?tri=titre" class="btn btn-outline btn-danger">Trier par salle</a>&nbsp&nbsp';
$contenu .= '<a href="?tri=date_arrivee" class="btn btn-outline btn-danger">Trier par date d\'arrivée</a>&nbsp&nbsp';
$contenu .= '<a href="?tri=id_produit" class="btn btn-outline btn-danger">Trier par id produit</a>&nbsp&nbsp';
$contenu .= '<a href="?tri=prix" class="btn btn-outline btn-danger">Trier par prix</a>&nbsp&nbsp';
$contenu .= '<a href="?tri=etat" class="btn btn-outline btn-danger">Trier par état</a>&nbsp&nbsp';

$contenu .= '</p></div></div>';


// TABLE DES PRODUITS
// ligne d'en-tête
$contenu .= '<div class="table-responsive"><table class="table table-striped table-bordered table-hover"><thead class="thead-inverse"><tr>';
$contenu .= '<th>id produit</th>';
$contenu .= '<th>date d\'arrivée</th>';
$contenu .= '<th>date de départ</th>';
$contenu .= '<th>id salle</th>';
$contenu .= '<th>prix</th>';
$contenu .= '<th>état</th>';
$contenu .= '<th>actions</th>';
$contenu .= '</tr></thead>';

/*$requete = $bdd->query("SELECT produit.id_produit, DATE_FORMAT(produit.date_arrivee,'%d/%m/%Y %H:%i') AS date_arrivee_fr, DATE_FORMAT(produit.date_depart,'%d/%m/%Y %H:%i') AS date_depart_fr, salle.id_salle, salle.titre, salle.photo, salle.adresse, salle.cp, salle.ville, salle.capacite, produit.prix, produit.etat 
	FROM produit 
	INNER JOIN salle ON produit.id_salle = salle.id_salle ORDER BY date_arrivee");*/
// Afficher le contenu de la table
while($ligne = $requetePages->fetch(PDO::FETCH_ASSOC))
{
	$contenu .= '<tr>';
	$contenu .= '<td>' . htmlentities($ligne['id_produit']) . '</td>';
	$contenu .= '<td>' . htmlentities($ligne['date_arrivee_fr']) . '</td>';
	$contenu .= '<td>' . htmlentities($ligne['date_depart_fr']) . '</td>';
	$contenu .= '<td>' . htmlentities($ligne['id_salle']) . ' - Salle ' . htmlentities($ligne['titre']) . '<br>
					<a href="' . htmlentities($ligne['photo']) . '" class="fancybox" title="Salle ' . htmlentities($ligne['titre']) . '"><img src="' . htmlentities($ligne['photo']) . '" class="appercu_salle"></a>
				</td>';
	$contenu .= '<td>' . htmlentities($ligne['prix']) . ' €</td>';
	$contenu .= '<td>' . htmlentities($ligne['etat']) . '</td>';

	// rajouter des liens de suppression et de modification
	$contenu .= '<td>';
	$contenu .= '<a href="' . RACINE_SITE . 'fiche_produit.php?id_produit=' . $ligne['id_produit'] . '" target="_blank"><span class="glyphicon glyphicon-search"></span></a>';
	
	if(isset($_GET['tri']) && !empty($_GET['tri']))
	{
		$contenu .= ' <a href="?action=modifier&id_produit=' . htmlentities($ligne['id_produit']) . '&page=' . $npage .  '&tri=' . htmlentities($_GET['tri']) . '#form_modif" ><span class="glyphicon glyphicon-edit"></span></a> ';
		
	}
	else
	{
		$contenu .= ' <a href="?action=modifier&id_produit=' . htmlentities($ligne['id_produit']) . '&page=' . $npage . '#form_modif" ><span class="glyphicon glyphicon-edit"></span></a> ';
		
	}

	if(isset($_GET['tri']) && !empty($_GET['tri']))
	{
		$contenu .= ' <a href="?action=supprimer&id_produit=' . htmlentities($ligne['id_produit']) . '&page=' . $npage .  '&tri=' . htmlentities($_GET['tri']) . '#form_modif" OnClick="return(confirm(\'Vous voulez supprimer ce produit ? \'))"><span class="glyphicon glyphicon-trash"></span></a> ';
		
	}
	else
	{
		$contenu .= ' <a href="?action=supprimer&id_produit=' . htmlentities($ligne['id_produit']) . '&page=' . $npage . '#form_modif" OnClick="return(confirm(\'Vous voulez supprimer ce produit ? \'))"><span class="glyphicon glyphicon-trash"></span></a> ';
		
	}
	
	//$contenu .= ' <a href="?action=supprimer&id_produit=' . htmlentities($ligne['id_produit']) . '" OnClick="return(confirm(\'Vous voulez supprimer ce produit ? \'))"><span class="glyphicon glyphicon-trash"></span></a>';
	$contenu .= '</td>';
	$contenu .= '</tr>';
}

$contenu .= '</table></div>';

// AFFICHAGE DES LIENS VERS LES PAGES
if(isset($nbPages) && $nbPages > 1)
{
	$contenu .= '<div class="text-center">';
	$contenu .= '<ul class="pagination">';
	for($i = 1; $i <= $nbPages; $i++)
	{
		if(isset($_GET['tri']) && !empty($_GET['tri']))
		{
			if($i == $npage)
			{
				$contenu .= '<li class="active"><a href="' . RACINE_SITE . 'admin/gestion_produits.php?page=' . $i . '&tri=' . htmlentities($_GET['tri']) . '">' . $i . '</a></li>';
			}
			else
			{
				$contenu .= '<li><a href="' . RACINE_SITE . 'admin/gestion_produits.php?page=' . $i . '&tri=' . htmlentities($_GET['tri']) . '">' . $i . '</a></li>';
			}
			
		}
		else
		{
			if($i == $npage)
			{
				$contenu .= '<li class="active"><a href="' . RACINE_SITE . 'admin/gestion_produits.php?page=' . $i . '">' . $i . '</a></li>';
			}
			else
			{
				$contenu .= '<li><a href="' . RACINE_SITE . 'admin/gestion_produits.php?page=' . $i . '">' . $i . '</a></li>';
			}
		}
	}
	$contenu .= '</ul>';	
	$contenu .= '</div>';		
}

// INFOS DES SALLES POUR LE FORMULAIRE DE MODIFICATION
$requete_salles = $bdd->query("SELECT * FROM salle");


if($erreur != "")
{
	$contenu .= '<br><div class="row"><div class="col-sm-12"><div class="alert alert-danger">' . $erreur . '</div></div></div>';
}


// AFFICHAGE DES FORMULAIRES
// FORMULAIRES RAJOUT OU MISE À JOUR DES SALLES
if(!isset($_GET['action']) || $_GET['action'] != 'ajouter')
{
	//$contenu .= '<div class="row"><div class="col-sm-12"><p class="text-center"><a href="?action=ajouter#form_ajout" class="btn btn-outline btn-danger">Ajouter un produit</a></p></div><div>';
	if(isset($_GET['tri']) && !empty($_GET['tri']))
	{
		$contenu .= '<div class="row"><div class="col-sm-12">
				<p class="text-center">
					<a href="?action=ajouter&page=' . $npage . '&tri=' . $_GET['tri'] . '#form_ajout" class="btn btn-outline btn-danger">Ajouter un nouveau produit</a>
				</p>
			</div>
		<div>';
	}
	else
	{
		$contenu .= '<div class="row"><div class="col-sm-12">
				<p class="text-center">
					<a href="?action=ajouter&page=' . $npage . '#form_ajout" class="btn btn-outline btn-danger">Ajouter un nouveau produit</a>
				</p>
			</div>
		<div>';
	}
}
else
{
	if(isset($_GET['tri']) && !empty($_GET['tri']))
	{
		$contenu .= '<div class="row"><div class="col-sm-12">
				<p class="text-center">
					<a href="' . RACINE_SITE . 'admin/gestion_produits.php?page=' . $npage . '&tri=' . $_GET['tri'] . '" class="btn btn-outline btn-danger">Fermer le formulaire</a>
				</p>
			</div>
		<div>';
	}
	else
	{
		$contenu .= '<div class="row"><div class="col-sm-12">
				<p class="text-center">
					<a href="' . RACINE_SITE . 'admin/gestion_produits.php?page=' . $npage . '" class="btn btn-outline btn-danger">Fermer le formulaire</a>
				</p>
			</div>
		<div>';
	}

}

// MODIFIER UN PRODUIT
if(isset($_GET['action']) && $_GET['action'] == 'modifier' && isset($_GET['id_produit']) && !empty($_GET['id_produit']))
{
	$requete_produit_modifier = $bdd->prepare("SELECT id_produit, id_salle, DATE_FORMAT(date_arrivee,'%d/%m/%Y') AS date_arrivee_fr, DATE_FORMAT(date_arrivee,'%H:%i') AS heure_arrivee_fr, DATE_FORMAT(date_depart,'%d/%m/%Y') AS date_depart_fr, DATE_FORMAT(date_depart,'%H:%i') AS heure_depart_fr, prix, etat FROM produit WHERE id_produit = :id_produit");
	$requete_produit_modifier->execute(array(
		'id_produit' => $_GET['id_produit']
	));
	$resultat_produit = $requete_produit_modifier->fetch(PDO::FETCH_ASSOC);	
		$contenu .= '
			<form method="post" id="form_modif">
				<div class="row">
					<div class="col-sm-5">

						<div class="col-sm-6">

							<div class="form-group">
								<label for="date_arrivee">Date d\'arrivée</label><br>
								<div class="input-group">
									<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
									<input type="date" name="date_arrivee" id="date_arrivee" class="form-control" placeholder="00/00/0000" value="' . htmlentities($resultat_produit['date_arrivee_fr']) . '"><br>
								</div>
							</div>

							<div class="form-group">
								<label for="date_depart">Date de départ</label><br>
								<div class="input-group">
									<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
									<input type="date" name="date_depart" id="date_depart" class="form-control" placeholder="00/00/0000" value="' . htmlentities($resultat_produit['date_depart_fr']) . '"><br>
								</div>
							</div>

						</div>

						<div class="col-sm-6">

							<div class="form-group">
								<label for="heure_arrivee">Horaire d\'arrivée</label><br>
								<div class="input-group">
									<span class="input-group-addon"><span class="glyphicon glyphicon-time"></span></span>
									<input type="text" name="heure_arrivee" id="heure_arrivee" class="form-control" placeholder="00:00" value="' . htmlentities($resultat_produit['heure_arrivee_fr']) . '"><br>
								</div>
							</div>

							<div class="form-group">
								<label for="heure_depart">Horaire de départ</label><br>
								<div class="input-group">
									<span class="input-group-addon"><span class="glyphicon glyphicon-time"></span></span>
									<input type="date" name="heure_depart" id="heure_depart" class="form-control" placeholder="00:00" value="' . htmlentities($resultat_produit['heure_depart_fr']) . '"><br>
								</div>
							</div>

						</div>
						

					</div>

					<div class="col-sm-offset-2 col-sm-5">

						<div class="form-group">
							<label for="salle">Salle</label><br>
							<select name="salle" class="form-control" id="salle">';
								while($ligne = $requete_salles->fetch(PDO::FETCH_ASSOC))
								{
									if($ligne['id_salle'] == $resultat_produit['id_salle'])
									{
										$contenu .= '<option name="salle" value="choisissez_salle">Choisissez une salle</option>';
										$contenu .= '<option name="salle" value="' . htmlentities($ligne['id_salle']) . '" selected>'
										 . htmlentities($ligne['id_salle']) . ' - Salle ' . htmlentities($ligne['titre']) . ' - ' . htmlentities($ligne['adresse']) . 
										 ', ' . htmlentities($ligne['cp']) . ', ' . htmlentities($ligne['ville']) . ' - ' . htmlentities($ligne['capacite']) .  
										 ' pers</option>';
									}
									else
									{
										$contenu .= '<option name="salle" value="' . htmlentities($ligne['id_salle']) . '">'
										 . htmlentities($ligne['id_salle']) . ' - Salle ' . htmlentities($ligne['titre']) . ' - ' . htmlentities($ligne['adresse']) . 
										 ', ' . htmlentities($ligne['cp']) . ', ' . htmlentities($ligne['ville']) . ' - ' . htmlentities($ligne['capacite']) .  
										 ' pers</option>';
										}	
								}
							$contenu .= '
							</select>
						</div>

						<div class="form-group">
							<label for="tarif">Tarif</label><br>
							<div class="input-group">
								<span class="input-group-addon"><span class="glyphicon glyphicon-euro"></span></span>
								<input type="text" name="tarif" id="tarif" class="form-control" placeholder="prix en euros" value="' . htmlentities($resultat_produit['prix']) . '"><br>
							</div>
						</div>

						<input type="submit" class="btn btn-outline btn-danger" value="Enregistrer">

					</div>

				</div>
			</form>
		';
}
// AJOUTER UN PRODUIT
elseif(isset($_GET['action']) && $_GET['action'] == 'ajouter')
{
	$contenu .= '
				<form method="post" id="form_ajout">
					<div class="row">
						<div class="col-sm-5">

							<div class="col-sm-6">

								<div class="form-group">
									<label for="date_arrivee">Date d\'arrivée</label><br>
									<div class="input-group">
										<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
										<input type="date" name="date_arrivee" id="date_arrivee" class="form-control" placeholder="00/00/0000"><br>
									</div>
								</div>

								<div class="form-group">
									<label for="date_depart">Date de départ</label><br>
									<div class="input-group">
										<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
										<input type="date" name="date_depart" id="date_depart" class="form-control" placeholder="00/00/0000"><br>
									</div>
								</div>

							</div>

							<div class="col-sm-6">

								<div class="form-group">
									<label for="heure_arrivee">Horaire d\'arrivée</label><br>
									<div class="input-group">
										<span class="input-group-addon"><span class="glyphicon glyphicon-time"></span></span>
										<input type="text" name="heure_arrivee" id="heure_arrivee" class="form-control" placeholder="00:00"><br>
									</div>
								</div>

								<div class="form-group">
									<label for="heure_depart">Horaire de départ</label><br>
									<div class="input-group">
										<span class="input-group-addon"><span class="glyphicon glyphicon-time"></span></span>
										<input type="date" name="heure_depart" id="heure_depart" class="form-control" placeholder="00:00"><br>
									</div>
								</div>

							</div>
							

						</div>

						<div class="col-sm-offset-2 col-sm-5">

							<div class="form-group">
								<label for="salle">Salle</label><br>
								<select name="salle" class="form-control" id="salle">';
									$contenu .= '<option>Choisissez une salle</option>';
									while($ligne = $requete_salles->fetch(PDO::FETCH_ASSOC))
									{
										$contenu .= '<option name="salle" value="' . $ligne['id_salle'] . '">'
										. $ligne['id_salle'] . ' - Salle ' . htmlentities($ligne['titre']) . ' - ' . htmlentities($ligne['adresse']) . 
										', ' . htmlentities($ligne['cp']) . ', ' . htmlentities($ligne['ville']) . ' - ' . htmlentities($ligne['capacite']) .  
										' pers</option>';
									}
								$contenu .= '
								</select>
							</div>

							<div class="form-group">
								<label for="tarif">Tarif</label><br>
								<div class="input-group">
									<span class="input-group-addon"><span class="glyphicon glyphicon-euro"></span></span>
									<input type="text" name="tarif" id="tarif" class="form-control" placeholder="prix en euros"><br>
								</div>
							</div>

							<input type="submit" class="btn btn-outline btn-danger" value="Enregistrer">

						</div>

					</div>
				</form>
	';
}

echo $contenu;

// EFFACER LES MESSAGES
if(isset($_SESSION['confirmation']) && !isset($_GET['action']))
{
	unset($_SESSION['confirmation']);
}

// INCLUSION DU FOOTER
require_once('../inc/bas.inc.php');