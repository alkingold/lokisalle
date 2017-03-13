<?php
require_once('inc/init.inc.php');
// SI LE FORMULAIRE EST POSTÉ
if($_POST)
						{
							$pseudo = htmlentities($_POST['pseudo']);
							$mdp = htmlentities($_POST['mdp']);
							$mdp2 = htmlentities($_POST['mdp2']);
							$nom = htmlentities($_POST['nom']);
							$prenom = htmlentities($_POST['prenom']);
							$email = htmlentities($_POST['email']);
							$civilite = htmlentities($_POST['civilite']);

							$erreur = '';
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
								// vérifie les caractères du pseudo
								if(!preg_match("#^[a-zA-Z0-9_.-]{3,20}$#", $_POST['pseudo']))
								{
									$erreur .= 'Votre pseudo peut contenir des lettres, des chiffres et des caractères suivants : " . "  " _ "  " - "<br>';
								}
								// vérifie si le pseudo est unique
								if($erreur == '')
								{
									$requete = $bdd->prepare('SELECT * FROM membre WHERE pseudo = :pseudo');
									$requete->execute(array(
										'pseudo' => $pseudo
									));
									if($requete->rowCount() != 0)
									{
										$erreur .= 'Ce pseudo existe déjà.<br>';
									}
								}

								// VERIFICATION MOT DE PASSE
								// si le mot de passe est vide
								if(empty($mdp))
								{
									$erreur .= 'Veuillez renseigner votre mot de passe.<br>';
								}
								// si la longueur est moins de 5 caractères ou plus de 10 caractères
								if(strlen($mdp) < 5 || strlen($mdp) > 10)
								{
									$erreur .= 'Votre mot de passe doit contenir entre 5 et 10 caractères.<br>';
								}
								// limitation des caractères aux lettres et chiffres
								if(!preg_match("#^[a-zA-Z0-9]{5,10}$#", $_POST['mdp']))
								{
									$erreur .= 'Votre mot de passe peut contenir des lettres et des chiffres.<br>';
								}

								// VERIFICATION MOT DE PASSE 2
								// si le mot de passe est vide
								if(empty($mdp2))
								{
									$erreur .= 'Veuillez répéter votre mot de passe.<br>';
								}
								else // si le mot de passe ne correspond pas au premier
								{
									if($mdp2 != $mdp)
									{
										$erreur .= 'Votre confirmation de mot de passe n\'est pas identique.<br>';									
									}
								}

								// VERIFICATION DU NOM
								// si le nom est vide
								if(empty($nom))
								{
									$erreur .= 'Veuillez renseginer votre nom.<br>';
								}
								// la longueur du nom doit être entre 2 et 20
								if(strlen($nom) < 2 || strlen($nom) > 40)
								{
									$erreur .= 'Votre nom peut contenir entre 2 et 40 caractères.<br>';
								}
								// limitation de caractères pour le nom
								if(!preg_match("#^[a-zàâäéèêëîïçôöœüûA-ZÀÄÂÉÈÊËÎÏÇÔÖŒÛÜ' -]{2,40}$#", $_POST['nom']))
								{
									$erreur .= 'Votre nom peut contenir des lettres, " - " et \'.<br>';
								}

								// VERIFICATION DU PRENOM
								// si le prénom est vide
								if(empty($prenom))
								{
									$erreur .= 'Veuillez renseginer votre prénom.<br>';
								}
								// la longueur du prénom doit être entre 2 et 20
								if(strlen($prenom) < 2 || strlen($prenom) > 40)
								{
									$erreur .= 'Votre prénom peut contenir entre 2 et 40 caractères.<br>';
								}
								// limitation de caractères pour le prénom
								if(!preg_match("#^[a-zàâäéèêëîïçôöœüûA-ZÀÄÂÉÈÊËÎÏÇÔÖŒÛÜ' -]{2,40}$#", $_POST['prenom']))
								{
									$erreur .= 'Votre prénom peut contenir des lettres, " - " et \'.<br>';
								}

								// VERIFICATION DE L'EMAIL
								// si l'email est vide
								if(empty($email))
								{
									$erreur .= 'Veuillez renseginer votre email.<br>';
								}
								// limitation de caractères pour le email
								if(!preg_match("#^[a-z0-9]+[a-z0-9._-]*@[a-z0-9]+[a-z0-9._-]+\.[a-z]{2,4}$#", $_POST['email']))
								{
									$erreur .= 'Veuillez rentrer une adresse email au format valide.<br>';
								}

								if($erreur == "")
								{
									// hachage de mdp avant l'insertion dans la base
									$mdpHash = password_hash($mdp, PASSWORD_DEFAULT);
									// insertion dans la base de données
									$requete = $bdd->prepare("INSERT INTO membre(pseudo, mdp, nom, prenom, email, civilite, date_enregistrement)
									VALUES(:pseudo, :mdp, :nom, :prenom, :email, :civilite, now())");
									$resultat = $requete->execute(array(
										'pseudo' => $pseudo,
										'mdp' => $mdpHash,
										'nom' => $nom,
										'prenom' => $prenom,
										'email' => $email,
										'civilite' => $civilite
									));

									// CREATION DE LA SESSION MEMBRE
									$requeteSession = $bdd->prepare("SELECT * FROM membre WHERE pseudo = :pseudo");
									$requeteSession->execute(array(
										'pseudo' => $pseudo
									));
									$resultatSession = $requeteSession->fetch(PDO::FETCH_ASSOC);
									if($requeteSession->rowCount() != 0)
									{
										foreach($resultatSession as $key => $value)
										{
											if($key != 'mdp')
											{
												$_SESSION['membre'][$key] = $value;
											}
										}
									}

									if($resultat != true)
									{
										echo '<br>Une erreur est survenue<br>';
									}
								}
								else
								{
									echo $erreur;
								}
						}