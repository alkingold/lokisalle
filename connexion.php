<?php
require_once('inc/init.inc.php');
if($_POST)
{
	$pseudo = htmlentities($_POST['pseudo']);
	$mdp = htmlentities($_POST['mdp']);

	$erreur = '';
	// VERIFICATION DU CHAMP PSEUDO
	if(empty($pseudo))
	{
		$erreur .= 'Veuillez renseigner votre pseudo<br>';
	}

	// VERIFICATION CHAMP MOT DE PASSE
	if(empty($mdp))
	{
		$erreur .= 'Veuillez renseigner votre mot de passe<br>';
	}

	if($erreur == "")
	{
		$requete = $bdd->prepare('SELECT * FROM membre WHERE pseudo = :pseudo');
		$requete->execute(array(
			'pseudo' => $pseudo
		)); 
		$resultat = $requete->fetch();
		if(password_verify($mdp, $resultat['mdp']))
		{
			foreach($resultat as $key => $value)
			{
				if($key != 'mdp')
				{
					$_SESSION['membre'][$key] = $value;
				}
			}
		}
		else
		{
			$erreur .= 'Erreur de pseudo ou de mot de passe<br>';
		}
	}
	echo $erreur;

}