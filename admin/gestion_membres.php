<?php
require_once('../inc/init.inc.php');
if(!membreConnecteAdmin())
{
	header('location:' . RACINE_SITE);
}

// INSERTION OU MODIFIOCATION
$erreur = "";

// VERIFICATIONS DU FORMULAIRE
// si le formulaire est envoyé
if($_POST)
{
	// Protection contre la faille XSS
	$pseudo = htmlentities($_POST['pseudo']);
	if(isset($_POST['mdp']))
	{
		$mdp = htmlentities($_POST['mdp']);
	}
	$nom = htmlentities($_POST['nom']);
	$prenom = htmlentities($_POST['prenom']);
	$email = htmlentities($_POST['email']);
	$civilite = htmlentities($_POST['civilite']);
	$statut = htmlentities($_POST['statut']);

	// VERIFICATION PSEUDO
	// si le pseudo est vide
	if(empty($_POST['pseudo']))
	{
		// erreur : remplir le champ pseudo
		$erreur .= 'Veuillez renseigner votre pseudo.<br>';
	}
	// si le pseudo est moins de 3 caractères ou plus de 20 caractères
	if(strlen($pseudo) < 3 || strlen($pseudo) > 20)
	{
		$erreur .= 'Votre pseudo doit contenir entre 3 et 20 caractères.<br>';
	}
	if(!preg_match("#^[a-zA-Z0-9_.-]{3,20}$#", $_POST['pseudo']))
	{
		$erreur .= 'Votre pseudo peut contenir des lettres, des chiffres et des caractères suivants : " . "  " _ "  " - "<br>';
	}

	// VERIFICATION MOT DE PASSE
	// CAS AJOUT
	if($_GET['action'] == 'ajouter')
	{
		// si le mot de passe est vide
		if(empty($mdp))
		{
			$erreur .= 'Veuillez renseigner votre mot de passe.<br>';
		}
		// si la longueur est moins de 6 caractères ou plus de 10 caractères
		if(strlen($mdp) < 5 || strlen($mdp) > 10)
		{
			$erreur .= 'Votre mot de passe doit contenir entre 5 et 10 caractères.<br>';
		}
		// limitation des caractères aux lettres et chiffres
		if(!preg_match("#^[a-zA-Z0-9]{5,10}$#", $_POST['mdp']))
		{
			$erreur .= 'Votre mot de passe peut contenir des lettres et des chiffres.<br>';
		}
	}
	elseif($_GET['action'] == 'modifier')
	{
		// si renseigné mdp en cas de modification
		if(isset($mdp) && !empty($mdp))
		{
			// si la longueur est moins de 6 caractères ou plus de 10 caractères
			if(strlen($mdp) < 5 || strlen($mdp) > 10)
			{
				$erreur .= 'Votre mot de passe doit contenir entre 5 et 10 caractères.<br>';
			}
			// limitation des caractères aux lettres et chiffres
			if(!preg_match("#^[a-zA-Z0-9]{5,10}$#", $_POST['mdp']))
			{
				$erreur .= 'Votre mot de passe peut contenir des lettres et des chiffres.<br>';
			}
		}
	}
	

	// VERIFICATION DU NOM
	// si le nom est vide
	if(empty($_POST['nom']))
	{
		$erreur .= 'Veuillez renseigner votre nom.<br>';
	}
	// la longueur du nom doit être entre 2 et 20
	if(strlen($nom) < 2 || strlen($nom) > 40)
	{
		$erreur .= 'Votre nom peut contenir entre 2 et 40 caractères.<br>';
	}
	// limitation de caractères pour le nom
	if(!preg_match("#^[a-zàâäéèêëîïçôöœüûA-ZÀÄÂÉÈÊËÎÏÇÔÖŒÛÜ' -]{2,40}$#", $_POST['nom']))
	{
		$erreur .= 'Votre nom peut contenir des lettres, des espaces, "\'" et " - " .<br>';
	}

	// VERIFICATION DU PRENOM
	// si le prénom est vide
	if(empty($_POST['prenom']))
	{
		$erreur .= 'Veuillez renseginer votre prénom.<br>';
	}
	// la longueur du prénom doit être entre 2 et 40
	if(strlen($prenom) < 2 || strlen($prenom) > 40)
	{
		$erreur .= 'Votre prénom peut contenir entre 2 et 40 caractères.<br>';
	}
	// limitation de caractères pour le prénom
	if(!preg_match("#^[a-zàâäéèêëîïçôöœüûA-ZÀÄÂÉÈÊËÎÏÇÔÖŒÛÜ' -]{2,40}$#", $_POST['prenom']))
	{
		$erreur .= 'Votre prénom peut contenir des lettres, des espaces, " - " et \'.<br>';
	}

	// VERIFICATION DE L'EMAIL
	// si l'email est vide
	if(empty($_POST['email']))
	{
		$erreur .= 'Veuillez renseginer votre email.<br>';
	}
	// limitation de caractères pour l'email
	if(!preg_match("#^[a-z0-9]+[a-z0-9._-]*@[a-z0-9]+[a-z0-9._-]+\.[a-z]{2,4}$#", $_POST['email']))
	{
		$erreur .= 'Veullez rentrer une adresse email au format valide.<br>';
	}

	// MODIFIER UN MEMBRE
	if(isset($_GET['action']) && $_GET['action'] == 'modifier' && isset($_GET['id_membre']) && !empty($_GET['id_membre']) && $erreur == "")
	{
		$id_membre = htmlentities($_GET['id_membre']);

		// vérifier que le pseudo est disponible (en cas de modification)
		$requete = $bdd->prepare("SELECT * FROM membre WHERE pseudo = :pseudo AND id_membre != :id_membre");
		$requete->execute(array(
			'pseudo' => $pseudo,
			'id_membre' => $id_membre
		));

		// MODIFIER LES INFORMATIONS DU MEMBRE
		if($requete->rowCount() == 0)
		{
			// hachage du mot de passe
			if(isset($mdp) && !empty($mdp))
			{
				$mdpHash = password_hash($mdp, PASSWORD_DEFAULT);
				$requete = $bdd->prepare("UPDATE membre SET pseudo = :pseudo, mdp = :mdp, nom = :nom, prenom = :prenom, email = :email, civilite = :civilite, statut = :statut WHERE id_membre = :id_membre");
				$requete->execute(array(
					'pseudo' => $pseudo,
					'mdp' => $mdpHash,
					'nom' => $nom,
					'prenom' => $prenom,
					'email' => $email,
					'civilite' => $civilite,
					'statut' => $statut, 
					'id_membre' => $id_membre
				));
			}
			else
			{
				$requete = $bdd->prepare("UPDATE membre SET pseudo = :pseudo, nom = :nom, prenom = :prenom, email = :email, civilite = :civilite, statut = :statut WHERE id_membre = :id_membre");
				$requete->execute(array(
					'pseudo' => $pseudo,
					'nom' => $nom,
					'prenom' => $prenom,
					'email' => $email,
					'civilite' => $civilite,
					'statut' => $statut, 
					'id_membre' => $id_membre
				));
			}
			$nb_lignes = $requete->rowCount(); 
			//echo $nb_lignes;
			if($requete->rowCount() != 0)
			{
				$nb_lignes = $requete->rowCount();
				if($nb_lignes == 1)
				{
					$_SESSION['confirmation']['message'] = $nb_lignes . ' membre a été mis à jour avec succès';
				}
				elseif($nb_lignes > 1)
				{
					$_SESSION['confirmation']['message'] = $nb_lignes . ' membres ont été mis à jour avec succès';
				}
			}
			else
			{
				$_SESSION['confirmation']['erreur'] = 'Le membre n\'a pas pu être mis à jour, probablement il n\'éxiste pas'; 
			}
			header('location:' . RACINE_SITE . 'admin/gestion_membres.php');
			exit;
		}
		else
		{
			$erreur .= 'Ce pseudo existe déjà, veuillez choisir un autre.<br>';
		}
	}
	
	// AJOUTER UN NOUVEAU MEMBRE
	if(isset($_GET['action']) && $_GET['action'] == 'ajouter' && $erreur == "")
	{
		// vérifier que le pseudo est disponible
		$requete = $bdd->prepare("SELECT * FROM membre WHERE pseudo = :pseudo");
		$resultat = $requete->execute(array(
			'pseudo' => $pseudo
		));

		// SI PSEUDO LIBRE, AJOUTER LE MEMBRE
		if($requete->rowCount() == 0)
		{
			// Hashage de mot de passe avant insertion
			$mdpHash = password_hash($mdp, PASSWORD_DEFAULT);
			// insertions des données dans la base
			$requete = $bdd->prepare("INSERT INTO membre(pseudo, mdp, nom, prenom, email, civilite, statut, date_enregistrement)
				VALUES(:pseudo, :mdp, :nom, :prenom, :email, :civilite, :statut, now())");
			$resultat = $requete->execute(array(
				'pseudo' => $pseudo,
				'mdp' => $mdpHash,
				'nom' => $nom,
				'prenom' => $prenom,
				'email' => $email,
				'civilite' => $civilite,
				'statut' => $statut
			));
		}
		$nb_lignes = $requete->rowCount(); 
		//echo $nb_lignes;
		if($requete->rowCount() != 0)
		{
			$nb_lignes = $requete->rowCount();
			if($nb_lignes == 1)
			{
				$_SESSION['confirmation']['message'] = $nb_lignes . ' membre a été rajouté avec succès';
			}
			elseif($nb_lignes > 1)
			{
				$_SESSION['confirmation']['message'] = $nb_lignes . ' membres ont été rajoutés avec succès';
			}
		}
		else
		{
			$_SESSION['confirmation']['erreur'] = 'Le membre n\'a pas pu être rajouté'; 
		}
		header('location:' . RACINE_SITE . 'admin/gestion_membres.php');
		exit;
	}

	
}

// SUPPRIMER UN MEMBRE
if(isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id_membre']) && !empty($_GET['id_membre']))
{
	// impossible de supprimer un membre qui a des commandes
	$requeteMembreCommande = $bdd->prepare("SELECT * FROM commande WHERE id_membre = :id_membre");
	$requeteMembreCommande->execute(array(
		'id_membre' => $_GET['id_membre']
	));
	if($requeteMembreCommande->rowCount() > 0)
	{
		//$erreur .= 'Impossible de supprimer un membre qui a des commandes';
		$_SESSION['confirmation']['erreur'] = 'Impossible de supprimer un membre qui a des commandes'; 
		header('location:' . RACINE_SITE . 'admin/gestion_membres.php');
		exit;
	}
	else
	{
		$id_membre = htmlentities($_GET['id_membre']);
		$requete = $bdd->prepare("DELETE FROM membre WHERE id_membre = :id_membre");
		$requete->execute(array(
			'id_membre' => $id_membre
		));
		$nb_lignes = $requete->rowCount(); 
			//echo $nb_lignes;
			if($requete->rowCount() != 0)
			{
				$nb_lignes = $requete->rowCount();
				if($nb_lignes == 1)
				{
					$_SESSION['confirmation']['message'] = $nb_lignes . ' membre a été supprimé avec succès';
				}
				elseif($nb_lignes > 1)
				{
					$_SESSION['confirmation']['message'] = $nb_lignes . ' membres ont été supprimés avec succès';
				}
			}
			else
			{
				$_SESSION['confirmation']['erreur'] = 'Le membre n\'a pas pu être supprimé'; 
			}
		header('location:' . RACINE_SITE . 'admin/gestion_membres.php');
		exit;
	}
	
}

require_once('../inc/haut.inc.php');


//echo $requeteMembreCommande->rowCount();

// messages erreur
if($erreur != "")
{
	$contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-danger">' . $erreur . '</p></div></div>';
}
if(isset($_SESSION['confirmation']['message']))
{
	$contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-success">' . $_SESSION['confirmation']['message'] . '</p></div></div>';
}
if(isset($_SESSION['confirmation']['erreur']))
{
	$contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-danger">' . $_SESSION['confirmation']['erreur'] . '</p></div></div>';
}

// AFFICHAGE DE LA LISTE DES MEMBRES
$requete = $bdd->query("SELECT id_membre, pseudo, nom, prenom, email, civilite, statut, DATE_FORMAT(date_enregistrement, '%d/%m/%Y %H:%m') AS date_format_fr FROM membre");
// Récupérer les noms des colonnes
$contenu .= '<h1>Gestion des membres </h1>';
$contenu .= '<div class="row"><div class="col-xs-12 col-sm-12 col-lg-12"><div class="table-responsive"><table class="table table-striped table-bordered table-hover"><tr>';
$nombre_colonnes = $requete->columnCount();
for($i=0; $i < $nombre_colonnes; $i++)
{
	$champs = $requete->getColumnMeta($i);
	if($champs['name'] != 'mdp')
	{
		$contenu .= '<th>' . $champs['name'] . '</th>';
	}
}
$contenu .= '<th>actions</th>';
$contenu .= '</tr>';

// Afficher le contenu de la table
while($ligne = $requete->fetch(PDO::FETCH_ASSOC))
{
	$contenu .= '<tr>';
	foreach($ligne as $cle=>$info)
	{
		if($cle != 'mdp')
		{
			if($cle == 'civilite')
			{
				if($ligne['civilite'] == 'm')
				{
					$contenu .= '<td>Homme</td>';
				} 
				else if($ligne['civilite'] == 'f')
				{
					$contenu .= '<td>Femme</td>';
				}			
			} 
			else if($cle == 'statut')
			{
				if($ligne['statut'] == '0')
				{
					$contenu .= '<td>membre</td>';
				}
				else if($ligne['statut'] == '1')
				{
					$contenu .= '<td>admin</td>';
				}
			}
			else
			{
				$contenu .= '<td>' . $info . '</td>';
			}
		}
	}
	
	// rajputer des liens de suppression et de modification
	$contenu .= '<td>';
	$contenu .= '<a href="' . RACINE_SITE . 'profil.php?id_membre=' . $ligne['id_membre'] . '" target="_blank"><span class="glyphicon glyphicon-search"></span></a>';
	$contenu .= ' <a href="?action=modifier&id_membre=' . $ligne['id_membre'] . '"><span class="glyphicon glyphicon-edit"></span></a> ';
	$contenu .= ' <a href="?action=supprimer&id_membre=' . $ligne['id_membre'] . '" OnClick="return(confirm(\'Vous voulez supprimer ce membre ? \'))"><span class="glyphicon glyphicon-trash"></span></a>';
	$contenu .= '</td>';
	$contenu .= '</tr>';
}
$contenu .= '</table></div></div></div>';
// fin de la table


// FORMULAIRE RAJOUT OU MISE À JOUR DES MEMBRES
if(!isset($_GET['action']) || $_GET['action'] != 'ajouter')
{
	$contenu .= '<div class="row"><div class="col-sm-12"><p class="text-center"><a href="?action=ajouter" class="btn btn-outline btn-danger">Ajouter un nouveau membre</a></p></div><div>';
}
else
{
	$contenu .= '<div class="row"><div class="col-sm-12"><p class="text-center"><a href="' . RACINE_SITE . 'admin/gestion_membres.php" class="btn btn-outline btn-danger">Fermer le formulaire</a></p></div><div>';
}


if(isset($_GET['action']) && $_GET['action'] == 'modifier' && isset($_GET['id_membre']) && !empty($_GET['id_membre']))
{
	$id_membre = htmlentities($_GET['id_membre']);
	$requete = $bdd->query("SELECT * FROM membre WHERE id_membre = $id_membre");
	$resultat = $requete->fetch(PDO::FETCH_ASSOC);
	$contenu .= '
		<form method="post">
			
			<div class="row">
				<div class="col-sm-5">
			
					<div class="form-group">
						<label for="pseudo">Pseudo</label><br>
						<div class="input-group">
							<span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>
							<input type="text" name="pseudo" id="pseudo" class="form-control" placeholder="pseudo" value="' . $resultat['pseudo'] .'"><br>
						</div>
					</div>

					<div class="form-group">
						<label for="mdp">Mot de Passe</label><br>
						<div class="input-group">
							<span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
							<input type="password" name="mdp" id="mdp" class="form-control" placeholder="nouveau mot de passe"><br>
						</div>
					</div>

					<div class="form-group">
						<label for="nom">Nom</label><br>
						<div class="input-group">
							<span class="input-group-addon"><span class="glyphicon glyphicon-pencil"></span></span>
							<input type="text" name="nom" id="nom" class="form-control" placeholder="votre nom" value="' . $resultat['nom'] .'"><br>
						</div>
					</div>

					<div class="form-group">
						<label for="prenom">Prénom</label><br>
						<div class="input-group">
							<span class="input-group-addon"><span class="glyphicon glyphicon-pencil"></span></span>
							<input type="text" name="prenom" id="prenom" class="form-control" placeholder="votre prénom" value="' . $resultat['prenom'] .'"><br>
						</div>
					</div>
				</div>

				<div class="col-sm-offset-2 col-sm-5">

					<div class="form-group">
						<label for="email">Email</label><br>
						<div class="input-group">
							<span class="input-group-addon"><span class="glyphicon glyphicon-envelope"></span></span>
							<input type="email" name="email" id="email" class="form-control" placeholder="votre email" value="' . $resultat['email'] .'"><br>
						</div>
					</div>

					<div class="form-group">
						<label for="civilite">Civilité</label><br>
						<select name="civilite" class="form-control" id="civilite">';
							if($resultat['civilite'] == 'm')
							{
								$contenu .= '<option name="civilite" value="m" selected>Homme</option>';
							}
							else
							{
								$contenu .= '<option name="civilite" value="m">Homme</option>';
							}
							if($resultat['civilite'] == 'f')
							{
								$contenu .= '<option name="civilite" value="f" selected>Femme</option>';
							}
							else
							{
								$contenu .= '<option name="civilite" value="f">Femme</option>';
							}
							
						$contenu .= '</select>
					</div>

					<div class="form-group">
						<label for="statut">Statut</label><br>
							<select name="statut" class="form-control" id="statut">';
								if($resultat['statut'] == '1')
								{
									$contenu .= '<option name="statut" value="1" selected>Admin</option>';
								}
								else
								{
									$contenu .= '<option name="statut" value="1">Admin</option>';
								}
								if($resultat['statut'] == '0')
								{
									$contenu .= '<option name="statut" value="0" selected>Non Admin</option>';
								}
								else
								{
									$contenu .= '<option name="statut" value="0">Non Admin</option>';
								}
							$contenu .= '</select><br>
					</div>

					<input type="submit" class="btn btn-outline btn-danger" value="Enregistrer">
				</div>
		</form>
	';
}
elseif(isset($_GET['action']) && $_GET['action'] == 'ajouter')
{
	$contenu .= '
		<form method="post">

			<div class="row">
				<div class="col-sm-5">

					<div class="form-group">
						<label for="pseudo">Pseudo</label><br>
						<div class="input-group">
							<span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>
							<input type="text" name="pseudo" id="pseudo" class="form-control" placeholder="pseudo"><br>
						</div>
					</div>

					<div class="form-group">
						<label for="mdp">Mot de Passe</label><br>
						<div class="input-group">
							<span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
							<input type="password" name="mdp" id="mdp" class="form-control" placeholder="mot de passe"><br>
						</div>
					</div>

					<div class="form-group">
						<label for="nom">Nom</label><br>
						<div class="input-group">
							<span class="input-group-addon"><span class="glyphicon glyphicon-pencil"></span></span>
							<input type="text" name="nom" id="nom" class="form-control" placeholder="votre nom"><br>
						</div>
					</div>

					<div class="form-group">
						<label for="prenom">Prénom</label><br>
						<div class="input-group">
							<span class="input-group-addon"><span class="glyphicon glyphicon-pencil"></span></span>
							<input type="text" name="prenom" id="prenom" class="form-control" placeholder="votre prénom"><br>
						</div>
					</div>
				</div>

				<div class="col-sm-offset-2 col-sm-5">

					<div class="form-group">
						<label for="email">Email</label><br>
						<div class="input-group">
							<span class="input-group-addon"><span class="glyphicon glyphicon-envelope"></span></span>
							<input type="email" name="email" id="email" class="form-control" placeholder="votre email"><br>
						</div>
					</div>

					<div class="form-group">
						<label for="civilite">Civilité</label><br>
						<select name="civilite" class="form-control" id="civilite">
							<option name="civilite" value="m">Homme</option>
							<option name="civilite" value="f">Femme</option>
						</select>
					</div>

					<div class="form-group">
						<label for="statut">Statut</label><br>
						<select name="statut" class="form-control" id="statut">
							<option name="statut" value="1">Admin</option>
							<option name="statut" value="0">Non Admin</option>
						</select><br>
					</div>

					<input type="submit" class="btn btn-outline btn-danger" value="Enregistrer">
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
	
require_once('../inc/bas.inc.php');