<?php
require_once('../inc/init.inc.php');
if(!membreConnecteAdmin())
{
	$contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-danger">Veuillez vous connecter pour accéder au backoffice</p></div></div>';
}
else
{
	header('location:' . RACINE_SITE);
}

require_once('../inc/haut.inc.php');

echo $contenu;

require_once('../inc/bas.inc.php');