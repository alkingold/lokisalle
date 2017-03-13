<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Lokisalle</title>
	<link rel="stylesheet" href="<?php echo RACINE_SITE; ?>inc/css/style.css">
	<link rel="stylesheet" href="<?php echo RACINE_SITE; ?>inc/bootstrap-3.3.7-dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo RACINE_SITE; ?>inc/bootstrap-3.3.7-dist/css/shop-homepage.css">
	<link rel="stylesheet" href="<?php echo RACINE_SITE; ?>inc/bootstrap-3.3.7-dist/css/sb-admin-2.min.css">
	<link rel="stylesheet" href="<?php echo RACINE_SITE; ?>inc/js/charts/vendor/morrisjs/morris.css">
	<link rel="stylesheet" href="<?php echo RACINE_SITE; ?>inc/js/charts/vendor/font-awesome/css/font-awesome.css">
	<link rel="stylesheet" href="<?php echo RACINE_SITE; ?>inc/jquery-ui-1.12.1-3.custom/jquery-ui.min.css">
	<link rel="stylesheet" href="<?php echo RACINE_SITE; ?>inc/jQuery-Timepicker-Addon-master/dist/jquery-ui-timepicker-addon.min.css">
	<link rel="stylesheet" href="<?php echo RACINE_SITE; ?>inc/js/source/jquery.fancybox.css">

	
</head>
<body>
	<header>
		<nav class="navbar navbar-default navbar-fixed-top navbar-inverse bg-info">
			<div class="container">

				<!-- Brand and toggle get grouped for better mobile display -->
			    <div class="navbar-header">
			      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
			        <span class="sr-only">Toggle navigation</span>
			        <span class="icon-bar"></span>
			        <span class="icon-bar"></span>
			        <span class="icon-bar"></span>
			      </button>
			      <a class="navbar-brand" href="<?php echo RACINE_SITE; ?>index.php">Lokisalle</a>
			    </div>

				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
					<ul class="nav navbar-nav navbar-left">
						<li><a href="<?php echo RACINE_SITE . 'contact.php'; ?>">Contact</a></li>
						<li><a href="<?php echo RACINE_SITE . 'qui_sommes_nous.php'; ?>">Qui sommes nous ?</a></li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;
						<?php
							if(isset($_SESSION['membre']['prenom']) && !empty($_SESSION['membre']['prenom']) && isset($_SESSION['membre']['nom']) && !empty($_SESSION['membre']['nom']))
							{
								echo $_SESSION['membre']['prenom'] . ' ' . $_SESSION['membre']['nom'];
							}
							else
							{
								echo 'Espace Membre';
							}
							?>

						 <span class="caret"></span></a>
							<ul class="dropdown-menu" aria-labelledby="dropdownMenu3">
								<?php
									// SI LE MEMBRE N'EST PAS CONNECTE
									if(!isset($_SESSION['membre']))
									{
										echo '<li><a href="#" data-toggle="modal" data-target="#inscription">Inscription</a></li>';
										echo '<li><a href="#" data-toggle="modal" data-target="#connexion">Connexion</a></li>';
									}
									else
									{
										echo '<li><a href="' . RACINE_SITE . 'profil.php">Profil</a></li>';
										echo '<li><a href="' . RACINE_SITE . 'deconnexion.php">Déconnexion</a></li>';
									}
								?>
							</ul>
						</li>
						<?php 
							if(isset($_SESSION['membre']['statut']) && $_SESSION['membre']['statut'] == 1)
							{
								?>
									<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">Espace Admin<span class="caret"></span></a>
										<ul class="dropdown-menu">
											<li><a href="<?php echo RACINE_SITE; ?>admin/gestion_commandes.php">Gestion des commandes</a></li>
											<li><a href="<?php echo RACINE_SITE; ?>admin/gestion_produits.php">Gestion des produits</a></li>
											<li><a href="<?php echo RACINE_SITE; ?>admin/gestion_salles.php">Gestion des salles</a></li>
											<li><a href="<?php echo RACINE_SITE; ?>admin/gestion_membres.php">Gestion des membres</a></li>
											<li><a href="<?php echo RACINE_SITE; ?>admin/gestion_avis.php">Gestion des avis</a></li>
											<li><a href="<?php echo RACINE_SITE; ?>admin/statistiques.php">Statistiques</a></li>
										</ul>
									</li>
							<?php
							}
						?>

					</ul>
				</div>
			</div>
		</nav>
	</header>
	<div class="container conteneur">
		
		<!-- Modal Inscription -->
		<div id="inscription" class="modal fade" role="dialog">
			<div class="modal-dialog modal-sm">
				<div class="modal-content">
					<div class="modal-body">
						<button type="button" class="close" 
	                   data-dismiss="modal">&times;</button>
						<h2 class="modal-title">S'inscrire</h2>
						<form method="post" id="inscription2" action="<?php echo RACINE_SITE; ?>inscription.php" data-redirection="<?php echo $_SERVER['REQUEST_URI'];?>">
							<div class="form-group">
								<input type="text" name="pseudo" class="form-control" placeholder="Votre pseudo">
							</div>

							<div class="form-group">
								<input type="password" name="mdp" class="form-control" placeholder="Votre mot de passe">
							</div>

							<div class="form-group">
								<input type="password" name="mdp2" class="form-control" placeholder="Confirmez votre mot de passe">
							</div>

							<div class="form-group">
								<input type="text" name="nom" class="form-control" placeholder="Votre nom">
							</div>

							<div class="form-group">
								<input type="text" name="prenom" class="form-control" placeholder="Votre prenom">
							</div>

							<div class="form-group">
								<input type="email" name="email" class="form-control" placeholder="Votre email">
							</div>

							<div class="form-group">
								<select name="civilite" class="form-control">
									<option value="m">Homme</option>
									<option value="f">Femme</option>
								</select>
							</div>

							<input type="submit" name="inscription" value="Inscription" class="btn btn-outline btn-danger">
						</form>
						<div id="erreur_inscription"></div>
					</div>
				</div>
			</div>
		</div>


		<!-- Modal Connexion -->
		<div id="connexion" class="modal fade" role="dialog">
			<div class="modal-dialog modal-sm">
				<div class="modal-content">
					<div class="modal-body">
						<button type="button" class="close" 
	                   data-dismiss="modal">&times;</button>
						<h2 class="modal-title">Se connecter</h2>
						<form method="post" id="connexion1" action="<?php echo RACINE_SITE; ?>connexion.php" data-redirection="<?php echo $_SERVER['REQUEST_URI'];?>">
							<div class="form-group">
								<input type="text" name="pseudo" class="form-control" placeholder="Votre pseudo"><br>
							</div>

							<div class="form-group">
								<input type="password" name="mdp" class="form-control" placeholder="Votre mot de passe"><br>
							</div>

							<input type="submit" name="connexion" value="Connexion" class="btn btn-outline btn-danger">
						</form>
						<div id="erreur_connexion"></div>
						
					</div>
				</div>
			</div>
		</div>
		

