<?php
require_once('inc/init.inc.php');

	// TRIER PRODUITS SELON PLUSIEURS CRITERES
	// Conversion dates
	if(isset($_POST['date_arrivee']) && !empty($_POST['date_arrivee']))
	{
		$date_arrivee = convertDateDb($_POST['date_arrivee'], '09:00');
	}
	if(isset($_POST['date_depart']) && !empty($_POST['date_depart']))
	{
		$date_depart = convertDateDb($_POST['date_depart'], '19:00');
	}

	//RECUPERER LES CRITERES DE TRI ET LES VALEURS DU FORMULAIRE DANS DEUX TABLEAUX
	$criteres = array();
	$valeurs = array();

	if(isset($_POST['categorie']) && $_POST['categorie'] != 'choisissez_categorie')
    {
    	array_push($criteres, "categorie = :categorie");
    	array_push($valeurs, "$_POST[categorie]");
    }
    if(isset($_POST['ville']) && $_POST['ville'] != 'choisissez_ville')
    {
    	array_push($criteres, "ville = :ville");
    	array_push($valeurs, "$_POST[ville]");
    }
    if(isset($_POST['capacite']) && $_POST['capacite'] != 'choisissez_capacite')
    {
    	array_push($criteres, "capacite >= :capacite");
    	array_push($valeurs, "$_POST[capacite]");
    }
    if(isset($_POST['prix']) && $_POST['prix'] < 2000)
    {
    	array_push($criteres, "prix <= :prix");
    	array_push($valeurs, "$_POST[prix]");
    }
    // SI DEFINIE DATE D'ARRIVEE
    if(isset($_POST['date_arrivee']) && !empty($_POST['date_arrivee']))
    {
    	array_push($criteres, "date_depart >= :date_arrivee");
        array_push($valeurs, $date_arrivee);
        /*if(isset($_POST['date_depart']) && !empty($_POST['date_depart']))
        {
            array_push($criteres, "(date_arrivee <= :date_arrivee AND date_arrivee >= now() AND date_depart >= :date_arrivee)");
        }
        else
        {
            array_push($criteres, "date_arrivee <= :date_arrivee AND date_arrivee >= now() AND date_depart >= :date_arrivee");
        }
    	array_push($valeurs, $date_arrivee);*/
    }
    /*else
    {
        $date_arrivee = date('Y-m-d H:i:s');
        array_push($criteres, "date_arrivee >= :date_arrivee");
        array_push($valeurs, $date_arrivee);
    }*/
    
    // SI DEFINIE DATE DE DEPART
    if(isset($_POST['date_depart']) && !empty($_POST['date_depart']))
    {
    	array_push($criteres, "date_arrivee <= :date_depart");
        array_push($valeurs, $date_depart);
        /*array_push($criteres, "date_depart >= :date_depart AND date_arrivee <= :date_depart");
    	array_push($valeurs, $date_depart);*/
    }

    //echo $date_arrivee . '<br><br>';
    //echo $date_depart . '<br><br>';
    //var_dump($criteres);
    //echo '<br><br>';
    //var_dump($valeurs);
    //echo '<br><br>';

    // REQUETE DE BASE AVANT AJOUT DES CRITERES
    $criteres_requete = "SELECT produit.id_produit, DATE_FORMAT(produit.date_arrivee,'%d/%m/%Y') AS date_arrivee_fr, DATE_FORMAT(produit.date_depart,'%d/%m/%Y') AS date_depart_fr, produit.prix, produit.etat, salle.id_salle, salle.titre, salle.description, salle.photo, salle.pays, salle.ville, salle.adresse, salle.cp, salle.capacite, salle.categorie 
    FROM produit INNER JOIN salle ON produit.id_salle = salle.id_salle 
    WHERE  etat = 'libre' AND date_arrivee >= now()";
    // AJOUT DES VALEURS DANS LA REQUETE
    foreach($criteres as $key => $value)
    {
        /*if(preg_match("#^date_depart#", $value))
        {
            $criteres_requete .= ' OR (' . $value . ')';
        }
        else
        {*/
            $criteres_requete .= ' AND ' . $value;
        //}
    }
    $criteres_requete .= " ORDER BY produit.date_arrivee ASC";
    //echo $criteres_requete . '<br><br>';
    //echo '<br><br>';

    // RECUPERER LES INDICES POUR EXECUTER LA REQUETE
    $indice = array();
    foreach($criteres as $key => $value)
    {
    	$indice_espace = strpos($value, ' ');
        $indice_coupe = substr($value, 0, $indice_espace);
        if($indice_coupe == 'date_depart')
        {
            $indice_coupe = 'date_arrivee';
        }
        elseif($indice_coupe == 'date_arrivee')
        {
            $indice_coupe = 'date_depart';
        }
        //echo '____';
        //var_dump($indice_coupe);
    	array_push($indice, $indice_coupe);
    }
    //var_dump($indice);
    //echo '<br>';
    
    $indice_valeur = array_combine($indice, $valeurs);
    //var_dump($indice_valeur);

	$requete = $bdd->prepare($criteres_requete);
	$requete->execute($indice_valeur);

    // RECHERCHE NOTE MOYENNE
    $requeteNote = $bdd->prepare("SELECT AVG(note) AS noteMoyenne FROM avis WHERE id_salle = :id_salle");

	// Affichage des résultat du tri
    if($requete->rowCount() == 0)
    {
        $contenu .= '<div class="row"><div class="col-sm-12"><p class="text-center">Aucun résultat n\'a été trouvé pour vos critères</p></div></div>';
    }
    elseif($requete->rowCount() == 1) 
    {
        $contenu .= '<div class="row"><div class="col-sm-12"><p class="text-center">' . $requete->rowCount() . ' résultat</p></div></div>';
    }
    else
    {
        $contenu .= '<div class="row"><div class="col-sm-12"><p class="text-center">' . $requete->rowCount() . ' résultats</p></div></div>';
    }
    while($resultat = $requete->fetch(PDO::FETCH_ASSOC))
    {
        $requeteNote->execute(array(
            'id_salle' => $resultat['id_salle']
        )); 
        $resultatNote = $requeteNote->fetch(PDO::FETCH_ASSOC);
        $rating = round($resultatNote['noteMoyenne']);

        // Limiter les phrases à max 40 caractères
        $description35 = substr($resultat['description'], 0, 35);
        $positionEspace = strrpos($description35, ' ');
        $description = substr($resultat['description'], 0, $positionEspace);
                            
        //var_dump($resultat);
        $contenu .= ' 
                    <div class="col-sm-4 col-lg-4 col-md-4">
                        <div class="thumbnail">
                            <a href="fiche_produit.php?id_produit=' . htmlentities($resultat['id_produit']) . '"><img src="' . htmlentities($resultat['photo']) . '" alt="salle ' . htmlentities($resultat['titre']) . '"></a>
                            <div class="caption">
                                <h4 class="pull-right">' . htmlentities($resultat['prix']) . ' €</h4>
                                <h4><a href="fiche_produit.php?id_produit=' . htmlentities($resultat['id_produit']) . '">' . $resultat['titre'] . '</a></h4>
                                <p>' . htmlentities($description) . '... <br><a target="_blank" href="fiche_produit.php?id_produit=' . htmlentities($resultat['id_produit']) . '">Lire la suite</a></p>
                                <p><span class="glyphicon glyphicon-calendar"></span> ' . htmlentities($resultat['date_arrivee_fr']) . ' au ' . htmlentities($resultat['date_depart_fr']) . '</p>
                            </div>
                            <div class="ratings">
                                <p class="pull-right"><a href="fiche_produit.php?id_produit=' . htmlentities($resultat['id_produit']) . '"><span class="glyphicon glyphicon-search"></span> Voir</a></p>
                                <p>';

                                    if($requeteNote->rowCount() == 0)
                                    {
                                        for($inote = 1; $inote <= 5; $inote++)
                                        {
                                            $contenu .= ' <span class="glyphicon glyphicon-star-empty"></span> ';
                                        }
                                    }
                                    else
                                    {
                                        for($inote = 1; $inote <= $rating; $inote++)
                                        {
                                            $contenu .= ' <span class="glyphicon glyphicon-star"></span> ';
                                        }
                                        $noteVide = 5 - $rating;
                                        for($noteV = 1; $noteV <= $noteVide; $noteV++)
                                        {
                                            $contenu .= ' <span class="glyphicon glyphicon-star-empty"></span> ';
                                        }
                                    }

        $contenu .= '
                                </p>
                            </div>
                        </div>
                    </div>';
    }

    echo $contenu;
	//$resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
	//var_dump($resultat);
	// $resultatTri = json_encode($resultat);
	// echo $resultatTri;
?>