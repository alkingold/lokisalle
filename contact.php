<?php
require_once('inc/init.inc.php');

// TRAITEMENT FORMULAIRE CONTACT
// Sécurité
if($_POST)
{
	$erreur = "";
	// VERIFICATION DU NOM
	// si le nom est vide
	if(empty($_POST['nom']))
	{
		$erreur .= 'Veuillez renseginer votre nom.<br>';
	}
	// la longueur du nom doit être entre 2 et 20
	if(strlen($_POST['nom']) < 2 || strlen($_POST['nom']) > 40)
	{
		$erreur .= 'Votre nom peut contenir entre 2 et 20 caractères.<br>';
	}
	// limitation de caractères pour le nom
	if(!preg_match("#^[a-zàâäéèêëîïçôöœüûA-ZÀÄÂÉÈÊËÎÏÇÔÖŒÛÜ' -]{2,40}$#", $_POST['nom']))
	{
		$erreur .= 'Votre nom peut contenir des lettres, " - " et \'.<br>';
	}

	// VERIFICATION DU PRENOM
	// si le prénom est vide
	if(empty($_POST['prenom']))
	{
		$erreur .= 'Veuillez renseginer votre prénom.<br>';
	}
	// la longueur du prénom doit être entre 2 et 20
	if(strlen($_POST['prenom']) < 2 || strlen($_POST['prenom']) > 40)
	{
		$erreur .= 'Votre prénom peut contenir entre 2 et 20 caractères.<br>';
	}
	// limitation de caractères pour le prénom
	if(!preg_match("#^[a-zàâäéèêëîïçôöœüûA-ZÀÄÂÉÈÊËÎÏÇÔÖŒÛÜ' -]{2,40}$#", $_POST['prenom']))
	{
		$erreur .= 'Votre prénom peut contenir des lettres, " - " et \'.<br>';
	}

	// VERIFICATION DE L'EMAIL
	// si l'email est vide
	if(empty($_POST['email']))
	{
		$erreur .= 'Veuillez renseginer votre email.<br>';
	}
	// limitation de caractères pour le prénom
	if(!preg_match("#^[a-z0-9]+[a-z0-9._-]*@[a-z0-9]+[a-z0-9._-]+\.[a-z]{2,4}$#", $_POST['email']))
	{
		$erreur .= 'Veullez rentrer une adresse email au format valide.<br>';
	}

	// VERIFICATION DU SUJET
	// si le SUJET est vide
	if(empty($_POST['sujet']))
	{
		$erreur .= 'Veuillez renseginer un sujet.<br>';
	}
	// limitation de caractères pour le sujet
	if(!preg_match("#^[0-9a-zàâäéèêëîïçôöœüûA-ZÀÄÂÉÈÊËÎÏÇÔÖŒÛÜ() .,;:!?/\"'-]{2,}$#", $_POST['sujet']))
	{
		$erreur .= 'Votre sujet contient des caractères interdits.<br>';
	}

	// VERIFICATION MESSAGE
	// si le message est vide
	if(empty($_POST['message']))
    {
        $erreur .= 'Veuillez entrer un message<br>';
    }
    // limitation de caractères pour le commentaire
    if(!preg_match("#^[0-9a-zàâäéèêëîïçôöœüûA-ZÀÄÂÉÈÊËÎÏÇÔÖŒÛÜ() .,;:!?/\"'-]{2,}$#", $_POST['message']))
    {
        $erreur .= 'Votre message contient des caractères interdits.<br>';
    }

    // récupérer les adresses mail des admins
	$requeteAdmin = $bdd->query("SELECT * FROM membre WHERE statut = '1'");

	//$destinataire = 'alexandra.kaichev@gmail.com';
	$destinataire = "";
	while($resulatAdmin = $requeteAdmin->fetch(PDO::FETCH_ASSOC))
	{
		$destinataireVirgules .= $resulatAdmin['email'] . ',';
	}
	$posVirgule = strrpos($destinataireVirgules, ',');
	$destinataire = substr($destinataireVirgules, 0, $posVirgule);

    if($erreur == "")
    {
    	$expediteur = $_POST['email'];

    	$objet = $_POST['sujet'];

    	$headers  = 'MIME-Version: 1.0' . "\r\n";
    	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    	$headers .= 'Reply-To: '.$expediteur."\r\n";
    	$headers .= 'From: "' . $_POST['nom'] . ' ' . $_POST['prenom'] . ' "<'.$expediteur.'>'."\r\n";
    	$headers .= 'Delivered-to: '.$destinataire."\r\n";

    	//$message = '<html><head><title>Contact via le formulaire Lokisalle</title</head><body>';
    	$message = '<table border="1" cellpadding="15" style="border-collapse:collapse; margin: 0 auto;">
    				<tr>
    					<th>Nom</th>
    					<td>' . $_POST['nom'] . '</td>
    				</tr>
    				<tr>
    					<th>Prénom</th>
    					<td>' . $_POST['prenom'] . '</td>
    				</tr>
    				<tr>
    					<th>Email</th>
    					<td>' . $_POST['email'] . '</td>
    				</tr>
    				<tr>
    					<th>Sujet</th>
    					<td>' . $_POST['sujet'] . '</td>
    				</tr>
    				<tr>
    					<th>Message</th>
    					<td>' . $_POST['message'] . '</td>
    				</tr>
    				</table>';

    	//$message .= '</body></html>';
    	//$_POST['message'];
    	if (mail($destinataire, $objet, $message, $headers)) // Envoi du message
		{
		    $confirmation = 'Votre message a bien été envoyé ';
		}
		else // Non envoyé
		{
		    $erreur = "Votre message n'a pas pu être envoyé";
		}
	}
}

require_once("inc/haut.inc.php");

?>
<div class="row"><div class="col-sm-12"><h1 class="text-center">Contact</h1></div></div>

<div class="row">
	<div class="col-sm-offset-3 col-sm-6 col-xs-12">
	<?php
		if(isset($confirmation) && $confirmation != "")
		{
			echo '<p class="alert alert-info">' . $confirmation . '</p>';
		}
		if(isset($erreur) && $erreur != "")
		{
			echo '<p class="alert alert-danger">' . $erreur . '</p>';
		}
	?>

		<form method="post">
	
			<div class="form-group">
				<label for="nom">Nom</label><br>
				<input type="text" name="nom" id="nom" class="form-control">
			</div>

			<div class="form-group">
				<label for="prenom">Prénom</label><br>
				<input type="text" name="prenom" id="prenom" class="form-control">
			</div>

			<div class="form-group">
				<label for="email">Email</label><br>
				<input type="email" name="email" id="email" class="form-control">
			</div>

			<div class="form-group">
				<label for="sujet">Sujet</label><br>
				<input type="text" name="sujet" id="sujet" class="form-control">
			</div>
			
			<div class="form-group">
				<label for="message">Message</label><br>
				<textarea name="message" id="message" class="form-control"></textarea>
			</div>

			<input type="submit" value="Envoyer" class="btn btn-outline btn-danger">
		</form>

	</div>
	
</div>

<?php
require_once('inc/bas.inc.php');