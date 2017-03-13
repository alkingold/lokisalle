<?php
// INCLUSION DU FICHIER INIT
require_once('../inc/init.inc.php');
if(!membreConnecteAdmin())
{
	header('location:' . RACINE_SITE);
	exit;
}

$erreur = '';
// AJOUT D'UNE NOUVELLE SALLE
if($_POST)
{
	// VÉRIFICATION DU TITRE
	// si le titre est vide
	if(empty($_POST['titre']))
	{
		$erreur .= 'Veuillez renseigner le titre de la salle<br>';
	}
	// si le titre est moins de 3 ou plus de 200 caractères
	if(strlen($_POST['titre']) < 3 || strlen($_POST['titre']) > 200)
	{
		$erreur .= 'Le titre de la salle doit contenir entre 3 et 200 caractères<br>';
	}
	// limitation caractères : lettres, chiffres, espaces trait d'union
	if(!preg_match("#^[a-zàâäéèêëîïçôöœüûA-ZÀÄÂÉÈÊËÎÏÇÔÖŒÛÜ0-9 -]{2,200}$#", $_POST['titre']))
	{
		$erreur .= 'Le titre peut contenir des lettres, des chiffres, des espaces et " - "<br>';
	}

	// VERIFICATION DE LA DESCRIPTION
	// si la description est vide
	if(empty($_POST['description']))
	{
		$erreur .= 'Veuillez renseginer la description de la salle<br>';
	}
	// la longueur de la description entre 3 et 200 caractères
	if(strlen($_POST['description']) < 3 || strlen($_POST['description']) > 200)
	{
		$erreur .= 'La description doit contenir entre 3 et 200 caractères<br>';
	}
	// limitation de caractères : lettres, chiffres, signes ponctuation
	if(!preg_match("#^[a-zàâäéèêëîïçôöœüûA-ZÀÄÂÉÈÊËÎÏÇÔÖŒÛÜ0-9.,':; ?!_-]{2,200}$#", $_POST['description']))
	{
		$erreur .= 'Votre description contient des caractères interdits<br>';
	}

	// GESTION PHOTO
	if(isset($_FILES['photo']) && !empty($_FILES['photo']))
	{
		$tmp_name = $_FILES['photo']['tmp_name'];
		$photo_name = $_FILES['photo']['name'];
		// Définir le chemin de la photo pour la placer sur le serveur
		$chemin_photo = RACINE_SITE . 'photo/' . $photo_name;
		// Vérifier si le chemin de la photo est unique
		$requete = $bdd->prepare('SELECT * FROM salle WHERE photo = :photo');
		$requete->execute(array(
			'photo' => $chemin_photo
		));
		/*if(file_exists('/photo/' . $photo_name))
		{
			$erreur .= 'Une photo avec ce nom existe déjà, merci de renommer votre fichier<br>';
		}*/
		if($requete->rowCount() != 0)
		{
			$erreur .= 'Une photo avec ce nom existe déjà, merci de renommer votre fichier<br>';
		}
		// si l'url est trop court ou trop long
		if(strlen($chemin_photo) < 3 || strlen($chemin_photo) > 200)
		{
			$erreur .= 'Le chemin vers la photo doit contenir entre 3 et 200 caractères<br>';
		}
		// caractères dans l'url
		if(!preg_match("#^[a-zA-Z0-9._/-]{3,200}$#", $chemin_photo))
		{
			$erreur .= 'Veuillez entrer un url valide<br>';
		}
	}
	else
	{
		$erreur .= 'Veuillez télécharger une photo de la salle<br>';
	}

	// VERIFICATION CHAMP ADRESSE
	// si le champ adresse est vide
	if(empty($_POST['adresse']))
	{
		$erreur .= 'Veuillez renseginer l\'adresse de la salle<br>';
	}
	// si l'adresse est trop courte ou trop longue
	if(strlen($_POST['adresse']) < 3 || strlen($_POST['adresse']) > 50)
	{
		$erreur .= 'L\'adresse doit contenir entre 3 et 50 caractères<br>';
	}
	// limitation de caractères adresse : lettres, chiffres, signes ponctuation
	if(!preg_match("#^[a-zàâäéèêëîïçôöœüûA-ZÀÄÂÉÈÊËÎÏÇÔÖŒÛÜ0-9.,:'; ?!_-]{3,50}$#", $_POST['adresse']))
	{
		$erreur .= 'L\'adresse peut contenir des lettres, des chiffres et des signes de ponctuation<br>';
	}

	// VERIFICATION CHAMP CODE POSTAL
	// si le champ code postal est vide
	if(empty($_POST['cp']))
	{
		$erreur .= 'Veuillez renseginer le code postal de la salle<br>';
	}
	// vérification des caractères et du nombre
	if(!preg_match("#^[0-9]{5}$#", $_POST['cp']))
	{
		$erreur .= 'Le code postal doit contenir 5 chiffres<br>';
	}

	// MODIFIER LES INFORMATIONS SALLE

	if(isset($_GET['action']) && $_GET['action'] == 'modifier' && isset($_GET['id_salle']) && !empty($_GET['id_salle']) && $erreur == "")
	{
		$id_salle = htmlentities($_GET['id_salle']);

		// Vérifier si le titre de la salle est unique
		$requete = $bdd->prepare("SELECT * FROM salle WHERE titre = :titre AND id_salle != :id_salle");
		$requete->execute(array(
			'titre' => $_POST['titre'],
			'id_salle' => $_GET['id_salle']
		));
		//var_dump($erreur);
		//die(__LINE__);
		// Si LE TITRE EST UNIQUE, MODIFIER LES INFORMATIONS DE LA SALLE
		if($requete->rowCount() == 0)
		{
			//die(__line__);
			$requete->closeCursor();
			// Si modification de la photo
			if(isset($_FILES['photo']['name']) && !empty($_FILES['photo']['name']))
			{

				// Chercher l'ancienne photo
				$requete_photo = $bdd->prepare("SELECT * FROM salle WHERE id_salle = :id_salle");
				$requete_photo->execute(array(
					'id_salle' => $id_salle
				));
				$resultat_photo = $requete_photo->fetch(PDO::FETCH_ASSOC);

				// modifier tous les champs y compris le chemin vers la photo dans la BDD
				$requete = $bdd->prepare("UPDATE salle 
					SET titre = :titre, description = :description, photo = :photo, pays = :pays, ville = :ville, adresse = :adresse, cp = :cp, capacite = :capacite, categorie = :categorie 
					WHERE id_salle = :id_salle");
				$requete->execute(array(
					'titre' => $_POST['titre'],
					'description' => $_POST['description'],
					'photo' => $chemin_photo,
					'pays' => $_POST['pays'],
					'ville' => $_POST['ville'],
					'adresse' => $_POST['adresse'],
					'cp' => $_POST['cp'],
					'capacite' => $_POST['capacite'],
					'categorie' => $_POST['categorie'],
					'id_salle' => $id_salle
				));

				// si la requete a fonctionné
				if($requete->rowCount() > 0)
				{
					// supprimer l'ancienne photo
					if(isset($resultat_photo['photo']))
					{
						$photo_supprimer = $_SERVER['DOCUMENT_ROOT'] . $resultat_photo['photo'];
						// Supprimer l'ancienne photo
						unlink($photo_supprimer);
					}
					// et charger la nouvelle
					if(!move_uploaded_file($tmp_name, $_SERVER['DOCUMENT_ROOT'] . $chemin_photo))
					{
						$erreur .= 'Erreur d\'insertion de la photo'; //die('erreur');
					}
				}
				// ENREGISTRER LA NOUVELLE PHOTO DANS UN DOSSIER SUR LE SERVER
			}
			else
			{
				// il n'y a pas de photo posté
				$requete = $bdd->prepare("UPDATE salle 
					SET titre = :titre, description = :description, pays = :pays, ville = :ville, adresse = :adresse, cp = :cp, capacite = :capacite, categorie = :categorie 
					WHERE id_salle = :id_salle");
				$requete->execute(array(
					'titre' => $_POST['titre'],
					'description' => $_POST['description'],
					'pays' => $_POST['pays'],
					'ville' => $_POST['ville'],
					'adresse' => $_POST['adresse'],
					'cp' => $_POST['cp'],
					'capacite' => $_POST['capacite'],
					'categorie' => $_POST['categorie'],
					'id_salle' => $id_salle
				));
			}

			$nb_lignes = $requete->rowCount(); 
			//echo $nb_lignes;
			if($requete->rowCount() != 0)
			{
				$nb_lignes = $requete->rowCount();
				if($nb_lignes == 1)
				{
					$_SESSION['confirmation']['message'] = $nb_lignes . ' salle a été mise à jour avec succès';
				}
				elseif($nb_lignes > 1)
				{
					$_SESSION['confirmation']['message'] = $nb_lignes . ' salles ont été mise à jour avec succès';
				}
			}
			else
			{
				$_SESSION['confirmation']['erreur'] = 'La salle n\'a pas pu être mise à jour, probablement elle n\'éxiste pas'; 
			}

			header('location:' . RACINE_SITE . 'admin/gestion_salles.php');
			exit;
		}
		else
		{
			$erreur .= 'Ce titre de salle existe déjà.<br>';
		}
	}

	// AJOUTER UNE SALLE
	if(isset($_GET['action']) && $_GET['action'] == 'ajouter' && $erreur == "")
	{
		// Vérifier si le titre de la salle est unique
		$requete = $bdd->prepare("SELECT * FROM salle WHERE titre = :titre");
		$requete->execute(array(
			'titre' => $_POST['titre']
		));

		// Si TITRE UNIQUE, AJOUTER LA SALLE
		if($requete->rowCount() == 0)
		{
			$requete = $bdd->prepare("INSERT INTO salle(titre, description, photo, pays, ville, adresse, cp, capacite, categorie) 
				VALUES(:titre, :description, :photo, :pays, :ville, :adresse, :cp, :capacite, :categorie)");
			$requete->execute(array(
				'titre' => $_POST['titre'],
				'description' => $_POST['description'],
				'photo' => $chemin_photo,
				'pays' => $_POST['pays'],
				'ville' => $_POST['ville'],
				'adresse' => $_POST['adresse'],
				'cp' => $_POST['cp'],
				'capacite' => $_POST['capacite'],
				'categorie' => $_POST['categorie']
			));

			// SI REQUETE A FONCTIONNE INSERER LA PHOTO
			if($requete->rowCount() > 0)
			{
				// ENREGISTRER LA PHOTO DANS UN DOSSIER SUR LE SERVER
				if(!move_uploaded_file($tmp_name, $_SERVER['DOCUMENT_ROOT'] . $chemin_photo))
				{
					$erreur .= 'Erreur d\'insertion de la photo<br>'; 
				}
			}
			

			$nb_lignes = $requete->rowCount(); 
			//echo $nb_lignes;
			if($requete->rowCount() != 0)
			{
				$nb_lignes = $requete->rowCount();
				if($nb_lignes == 1)
				{
					$_SESSION['confirmation']['message'] = $nb_lignes . ' salle a été ajoutée avec succès';
				}
				elseif($nb_lignes > 1)
				{
					$_SESSION['confirmation']['message'] = $nb_lignes . ' salles ont été ajoutées avec succès';
				}
			}
			else
			{
				$_SESSION['confirmation']['erreur'] = 'La salle n\'a pas pu être ajoutée'; 
			}
		}
		else
		{
			$erreur .= 'Ce titre de salle existe déjà.<br>';
		}
	}
}

// SUPPRIMER UNE SALLE
if(isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id_salle']) && !empty($_GET['id_salle']))
{
	// RECHERCHE SI COMMANDES RATTACHEES A CETTE SALLE
	$requeteSalleCommande = $bdd->prepare("SELECT * FROM commande 
		INNER JOIN (produit INNER JOIN salle ON produit.id_salle = salle.id_salle) 
		ON commande.id_produit = produit.id_produit WHERE salle.id_salle = :id_salle");
	$requeteSalleCommande->execute(array(
		'id_salle' => $_GET['id_salle']
	));
	if($requeteSalleCommande->rowCount() > 0)
	{
		$_SESSION['confirmation']['erreur'] = 'Impossible de supprimer une salle qui a des commandes rattachées'; 
		header('location:' . RACINE_SITE . 'admin/gestion_salles.php');
		exit;
	}
	else
	{
		$id_salle = htmlentities($_GET['id_salle']);
		// Supprimer la photo
		// Chercher l'ancienne photo
		$requete_photo = $bdd->prepare("SELECT * FROM salle WHERE id_salle = :id_salle");
		$requete_photo->execute(array(
			'id_salle' => $id_salle
		));
		$resultat_photo = $requete_photo->fetch(PDO::FETCH_ASSOC);

		$requete = $bdd->prepare("DELETE FROM salle WHERE id_salle = :id_salle");
		$requete->execute(array(
			'id_salle' => $_GET['id_salle']
		));
		// si la requete a fonctionné - supprimer la photo
		if($requete->rowCount() > 0)
		{
			if($resultat_photo['photo'])
			{
				$photo_supprimer = $_SERVER['DOCUMENT_ROOT'] . $resultat_photo['photo'];
				// Supprimer l'ancienne photo
				unlink($photo_supprimer);
			}
		}

		$nb_lignes = $requete->rowCount(); 
		//echo $nb_lignes;
		if($requete->rowCount() != 0)
		{
			$nb_lignes = $requete->rowCount();
			if($nb_lignes == 1)
			{
				$_SESSION['confirmation']['message'] = $nb_lignes . ' salle a été supprimée avec succès';
			}
			elseif($nb_lignes > 1)
			{
				$_SESSION['confirmation']['message'] = $nb_lignes . ' salles ont été supprimées avec succès';
			}
		}
		else
		{
			$_SESSION['confirmation']['erreur'] = 'La salle n\'a pas pu être supprimée, probablement elle n\'éxiste pas'; 
		}
		
		header('location:' . RACINE_SITE . 'admin/gestion_salles.php');
		exit;
	}

	
}

// FICHE PRODUIT POUR SEARCH
$requeteSearch = $bdd->prepare("SELECT produit.id_produit FROM produit INNER JOIN salle ON produit.id_salle = salle.id_salle WHERE produit.date_arrivee > now() AND salle.id_salle = :id_salle ORDER BY produit.date_arrivee ASC");
if($requeteSearch->rowCount() == 0)
{
	$requeteSearch = $bdd->prepare("SELECT produit.id_produit FROM produit INNER JOIN salle ON produit.id_salle = salle.id_salle WHERE salle.id_salle = :id_salle ORDER BY produit.date_arrivee DESC");
}

// INCLUSION DU HEADER
require_once("../inc/haut.inc.php");

//var_dump($_SERVER['DOCUMENT_ROOT']);

// AFFICHAGE DE LA LISTE DES SALLES
$requete = $bdd->query('SELECT * FROM salle');

$contenu .= '<h1>Gestion des salles</h1>';

if(isset($_SESSION['confirmation']['message']))
{
	$contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-success">' . $_SESSION['confirmation']['message'] . '</p></div></div>';
}
if(isset($_SESSION['confirmation']['erreur']))
{
	$contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-danger">' . $_SESSION['confirmation']['erreur'] . '</p></div></div>';
}

// Récupérer les noms des colonnes
$contenu .= '<div class="table-responsive"><table class="table table-striped table-bordered table-hover"><tr>';
$nombre_colonnes = $requete->columnCount();
for($i=0; $i < $nombre_colonnes; $i++)
{
	$champs = $requete->getColumnMeta($i);
	$contenu .= '<th>' . $champs['name'] . '</th>';
}
$contenu .= '<th>actions</th>';
$contenu .= '</tr>';

// Afficher le contenu de la table
while($ligne = $requete->fetch(PDO::FETCH_ASSOC))
{
	$requeteSearch->execute(array(
		'id_salle' => $ligne['id_salle']
	));
	$resultatSearch = $requeteSearch->fetch(PDO::FETCH_ASSOC);
	$contenu .= '<tr>';
	foreach($ligne as $cle=>$info)
	{
		if($cle == 'photo')
		{
			$contenu .= '<td><a href="' . htmlentities($info) . '" class="fancybox" title="Salle ' . htmlentities($ligne['titre']) . '"><img src="' . htmlentities($info) . '" class="appercu_salle"></a></td>';
		}
		else
		{
			$contenu .= '<td>' . $info . '</td>';
		} 
	}
	// rajouter des liens de suppression et de modification
	$contenu .= '<td>';
	$contenu .= '<a href="' . RACINE_SITE . 'fiche_produit.php?id_produit=' . $resultatSearch['id_produit'] . '"><span class="glyphicon glyphicon-search"></span></a>';
	$contenu .= ' <a href="?action=modifier&id_salle=' . $ligne['id_salle'] . '#form-modif"><span class="glyphicon glyphicon-edit"></span></a> ';
	$contenu .= ' <a href="?action=supprimer&id_salle=' . $ligne['id_salle'] . '" OnClick="return(confirm(\'Êtes vous sûr de vouloir supprimer cette salle ? \nAttention, tous les produits rattachés à la salle vont être supprimés \'))"><span class="glyphicon glyphicon-trash"></span></a>';
	$contenu .= '</td>';
	$contenu .= '</tr>';
}

$contenu .= '</table></div>';

// AFFICHAGE DES ERREURS
if($erreur != "")
{
	$contenu .= '<div class="row"><div class="col-sm-12"><p id=erreur class="alert alert-danger">' . $erreur . '</p></div><div>';
}


// FORMULAIRES RAJOUT OU MISE À JOUR DES SALLES
if(!isset($_GET['action']) || $_GET['action'] != 'ajouter')
{
	$contenu .= '<div class="row"><div class="col-sm-12"><p class="text-center"><a href="?action=ajouter#form_ajout" class="btn btn-outline btn-danger">Ajouter une salle</a></p></div><div>';
}
else
{
	$contenu .= '<div class="row"><div class="col-sm-12"><p class="text-center"><a href="' . RACINE_SITE . 'admin/gestion_salles.php" class="btn btn-outline btn-danger">Fermer le formulaire</a></p></div><div>';
}

// FORMULAIRE MODIFICATION DES SALLES
if(isset($_GET['action']) && $_GET['action'] == 'modifier' && isset($_GET['id_salle']) && !empty($_GET['id_salle']))
{
	$id_salle = htmlentities($_GET['id_salle']);
	$requete = $bdd->query("SELECT * FROM salle WHERE id_salle = $id_salle");
	$resultat = $requete->fetch(PDO::FETCH_ASSOC);
	//echo $resultat['categorie'];
	$contenu .= '
	<form method="post"  enctype="multipart/form-data" id="form-modif">
	
		<div class="row">
			<div class="col-sm-5">

				<div class="form-group">
					<label for="titre">Titre</label><br>
					<input type="text" name="titre" id="titre" class="form-control" placeholder="Titre de la salle" value="' . htmlentities($resultat['titre']) . '"><br>
				</div>

				<div class="form-group">
					<label for="description">Description</label><br>
					<textarea name="description" id="description" class="form-control" placeholder="Description de la salle">' . htmlentities($resultat['description']) . '</textarea><br>
				</div>

				<div class="form-group">
					<label for="photo">Photo</label><br>
					<a href="' . htmlentities($resultat['photo']) . '" class="fancybox" title="Salle ' . htmlentities($resultat['titre']) . '">
						<img src="' . htmlentities($resultat['photo']) . '" class="appercu_salle">
					</a><br><br>
					<input type="file" name="photo" class="form-control" id="photo"><br>
				</div>

				<div class="form-group">
					<label for="capacite">Capacité</label><br>
					<select name="capacite" class="form-control" id="capacite">';
							for($i = 1; $i <= 100; $i++)
							{
								if(!empty($resultat['capacite']))
								{
									if($i == $resultat['capacite'])
									{
										$contenu .= '<option name="capacite" value="' . $i . '" selected>' . $i . '</option>';
									}
									else
									{
										$contenu .= '<option name="capacite" value="' . $i . '">' . $i . '</option>';
									}
								}
								else
								{
									for($i = 1; $i <= 100; $i++)
									{
										$contenu .= '<option name="capacite" value="' . $i . '">' . $i . '</option>';
									}
								}
							}
					$contenu .= 
					'</select><br>
				</div>

				<div class="form-group">
					<label for="categorie">Catégorie</label><br>
					<select name="categorie" class="form-control" id="categorie">';
						switch($resultat['categorie'])
						{
							case 'réunion':
							$contenu .= '<option name="categorie" value="reunion" selected>Réunion</option>
										<option name="categorie" value="bureau">Bureau</option>
										<option name="categorie" value="formation">Formation</option>';
							break;
							case 'bureau':
							$contenu .= '<option name="categorie" value="reunion">Réunion</option>
										<option name="categorie" value="bureau" selected>Bureau</option>
										<option name="categorie" value="formation">Formation</option>';
							break;
							case 'formation':
							$contenu .= '<option name="categorie" value="reunion">Réunion</option>
										<option name="categorie" value="bureau">Bureau</option>
										<option name="categorie" value="formation" selected>Formation</option>';
							break;
							default :
							$contenu .= '<option name="categorie" value="reunion">Réunion</option>
										<option name="categorie" value="bureau">Bureau</option>
										<option name="categorie" value="formation">Formation</option>';
						}
					$contenu .= ' 
					</select><br>
				</div>

			</div>

			<div class="col-sm-offset-2 col-sm-5">

				<div class="form-group">
					<label for="pays">Pays</label><br>
					<select name="pays" class="form-control" id="pays">
						<option name="pays" value="France">France</option>
					</select><br>
				</div>

				<div class="form-group">
					<label for="ville">Ville</label><br>
					<select name="ville" class="form-control" id="ville">';
						switch($resultat['ville'])
						{
							case 'Paris':
							$contenu .= '<option name="ville" value="Paris" selected>Paris</option>
										<option name="ville" value="Lyon">Lyon</option>
										<option name="ville" value="Marseille">Marseille</option>';
							break;
							case 'Lyon':
							$contenu .= '<option name="ville" value="Paris">Paris</option>
										<option name="ville" value="Lyon" selected>Lyon</option>
										<option name="ville" value="Marseille">Marseille</option>';
							break;
							case 'Marseille':
							$contenu .= '<option name="ville" value="Paris">Paris</option>
										<option name="ville" value="Lyon">Lyon</option>
										<option name="ville" value="Marseille" selected>Marseille</option>';
							break;
							default:
							$contenu .= '<option name="ville" value="Paris">Paris</option>
										<option name="ville" value="Lyon">Lyon</option>
										<option name="ville" value="Marseille">Marseille</option>';
						}
					$contenu .= '
					</select><br>
				</div>

				<div class="form-group">
					<label for="adresse">Adresse</label><br>
					<textarea name="adresse" id="adresse" class="form-control" placeholder="Adresse de la salle">' . htmlentities($resultat['adresse']) . '</textarea><br>
				</div>

				<div class="form-group">
					<label for="cp">Code Postal</label><br>
					<input type="text" name="cp" id="cp" class="form-control" placeholder="Code postal de la salle" value="' . htmlentities($resultat['cp']) . '"><br>
				</div>

				<input type="submit" class="btn btn-outline btn-danger" value="Enregistrer">

			</div>

		</div>
	</form>';
}
// FORMULAIRE AJOUT D'UNE SALLE
elseif(isset($_GET['action']) && $_GET['action'] == 'ajouter')
{
	$contenu .= '
		<form method="post" enctype="multipart/form-data" id="form_ajout">
	
			<div class="row">
				<div class="col-sm-5">

					<div class="form-group">
						<label for="titre">Titre</label><br>
						<input type="text" name="titre" id="titre" class="form-control" placeholder="Titre de la salle"><br>
					</div>

					<div class="form-group">
						<label for="description">Description</label><br>
						<textarea name="description" id="description" class="form-control" placeholder="Description de la salle"></textarea><br>
					</div>

					<div class="form-group">
						<label for="photo">Photo</label><br>
						<input type="file" name="photo" class="form-control" id="photo"><br>
					</div>

					<div class="form-group">
						<label for="capacite">Capacité</label><br>
						<select name="capacite" class="form-control" id="capacite">';
								for($i = 1; $i <= 100; $i++)
								{
									$contenu .= '<option name="capacite" value="' . $i . '">' . $i . '</option>';
								}
					$contenu .= '
						</select><br>
					</div>

					<div class="form-group">
						<label for="categorie">Catégorie</label><br>
						<select name="categorie" class="form-control" id="categorie">
							<option name="categorie" value="reunion">Réunion</option>
							<option name="categorie" value="bureau">Bureau</option>
							<option name="categorie" value="formation">Formation</option>
						</select><br>
					</div>

				</div>

				<div class="col-sm-offset-2 col-sm-5">

					<div class="form-group">
						<label for="pays">Pays</label><br>
						<select name="pays" class="form-control" id="pays">
							<option name="pays" value="France">France</option>
						</select><br>
					</div>

					<div class="form-group">
						<label for="ville">Ville</label><br>
						<select name="ville" class="form-control" id="ville">
							<option name="ville" value="Paris">Paris</option>
							<option name="ville" value="Lyon">Lyon</option>
							<option name="ville" value="Marseille">Marseille</option>
						</select><br>
					</div>

					<div class="form-group">
						<label for="adresse">Adresse</label><br>
						<textarea name="adresse" id="adresse" class="form-control" placeholder="Adresse de la salle"></textarea><br>
					</div>

					<div class="form-group">
						<label for="cp">Code Postal</label><br>
						<input type="text" name="cp" id="cp" class="form-control" placeholder="Code postal de la salle"><br>
					</div>

					<input type="submit" class="btn btn-outline btn-danger" value="Enregistrer">

				</div>

			</div>
		</form>	';
}
echo $contenu;

// EFFACER LES MESSAGES
if(isset($_SESSION['confirmation']) && !isset($_GET['action']))
{
	unset($_SESSION['confirmation']);
}

// INCLUSION DU FOOTER
require_once('../inc/bas.inc.php');
?>