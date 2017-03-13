<?php
// INCLUSION DU FICHIER INIT
require_once('inc/init.inc.php');

// DEFINITION DU NUMERO DE PAGE
if(isset($_GET['page']) && !empty($_GET['page']))
{
    $npage = $_GET['page'];
}
else
{
    $npage = 1;
}
//echo $npage . '<br>';

// AFFICHAGE DES PRODUITS LIBRES
if(!$_POST)
{
    // SELECTION DE TOUS LES PRODUITS A PARTIR DE LA DATE ACTUELLE DANS LA BASE DE DONNEES
    $requete = $bdd->query("SELECT produit.id_produit, DATE_FORMAT(produit.date_arrivee,'%d/%m/%Y') AS date_arrivee_fr, DATE_FORMAT(produit.date_depart,'%d/%m/%Y') AS date_depart_fr, produit.prix, produit.etat, salle.id_salle, salle.titre, salle.description, salle.photo, salle.pays, salle.ville, salle.adresse, salle.cp, salle.capacite, salle.categorie 
        FROM produit INNER JOIN salle ON produit.id_salle = salle.id_salle 
        WHERE produit.etat = 'libre' AND produit.date_arrivee >= now() ORDER BY produit.date_arrivee ASC");
}

// RECHERCHE NOTE MOYENNE
$requeteNote = $bdd->prepare("SELECT AVG(note) AS noteMoyenne FROM avis WHERE id_salle = :id_salle");

// INCLUSION DU HEADER
require_once("inc/haut.inc.php");
?>

<div class="row">

    <!-- ASIDE TRI DE L'AFFICHAGE -->
    <div class="col-md-3">
        <!--  -->
        <form action="tri_produit.php" id="tri" method="post">
            <?php 
                $requete_categories = $bdd->query("SELECT DISTINCT categorie FROM salle");
            ?>
            <div class="form-group">
                <label for="categorie">Catégorie</label><br>
                <select id="categorie" name="categorie" class="selectpicker form-control" >
                    <option value="choisissez_categorie">Choisissez une catégorie</option>
                    <?php
                    while($categorie = $requete_categories->fetch(PDO::FETCH_ASSOC))
                    {
                        echo '<option value="' . $categorie['categorie'] . '">' . ucfirst($categorie['categorie']) . '</option>';
                    }
                ?>
                </select>
            </div>

            <?php 
                $requete_ville = $bdd->query("SELECT DISTINCT ville FROM salle");
            ?>
            <div class="form-group">
                <label for="ville">Ville</label><br>
                <select id="ville" name="ville" class="selectpicker form-control">
                    <option value="choisissez_ville">Choisissez une ville</option>
                    <?php
                    while($ville = $requete_ville->fetch(PDO::FETCH_ASSOC))
                    {
                        echo '<option value="' . $ville['ville'] . '">' . ucfirst($ville['ville']) . '</option>';
                    }
                ?>
                </select>
            </div>
            <!-- <div class="list-group">
                <a href="?ville=Paris" class="list-group-item">Paris</a>
                <a href="?ville=Lyon" class="list-group-item">Lyon</a>
                <a href="?ville=Marseille" class="list-group-item">Marseille</a>
            </div> -->

                    <p>
                        <label for="capacite">Capacité</label><br>
                        <select name="capacite" id="capacite" class="form-control">
                            <option value="choisissez_capacite">Choisissez la capacité</option>
                            <?php 
                            for($i = 1; $i <= 100; $i++)
                            {
                                echo '<option value="' . $i . '">' . $i . '</option>';
                            }
                            ?>
                        </select>
                    </p>
                    <p>
                        <label for="prix">Prix</label><br>
                        <input type="range" name="prix" id="prix" min="100" max="2000" step="10" value="2000" class="form-control" oninput="prixOutput.value = prix.value">
                        <span><i>maximum </i></span><output name="prixOutput" id="prixOutput"><strong>2000</strong></output><span> €</span>
                    </p>

                    <p>
                        <!-- Période <br> -->
                        <label for="date_arrivee">Date d'arrivée</label><br>
                        <div class="input-group">
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                            <input type="text" name="date_arrivee" id="date_arrivee" class="form-control" placeholder="00/00/0000"><br>
                        </div>
                    </p>
                    <p>
                        <label for="date_depart">Date de départ</label><br>
                        <div class="input-group">
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                            <input type="text" name="date_depart" id="date_depart" class="form-control" placeholder="00/00/0000"><br>
                        </div>
                    </p> 
                </form>
            </div>


            <?php
                // RECUPERER TOUTES LES DIFFERENTES PHOTOS DES PRODUITS DE LA BDD
                $requetePhotos = $bdd->query("SELECT DISTINCT photo, titre FROM salle");
                $resultatPhotos = $requetePhotos->fetchAll(PDO::FETCH_ASSOC);
                //var_dump($resultatPhotos);
                $nombrePhotos = $requetePhotos->rowCount();
            ?>
            <div class="col-md-9">

                <!-- CAROUSEL -->

                <div class="row carousel-holder">

                    <div class="col-md-12">
                        <div id="carousel-salles" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                                <?php
                                    for($i = 0; $i < $nombrePhotos; $i++)
                                    {
                                        if($i === 0)
                                        {
                                            echo '<li data-target="#carousel-salles" data-slide-to="' . $i . '" class="active"></li>';
                                        }
                                        else
                                        {
                                            echo '<li data-target="#carousel-salles" data-slide-to="' . $i . '"></li>';
                                        }
                                    }
                                ?>
                            </ol>
                            <div class="carousel-inner">
                                
                                <?php
                                    for($i2 = 0; $i2 < $nombrePhotos; $i2++)
                                    {
                                        if($i2 === 0)
                                        {
                                            echo '
                                                <div class="item active">
                                                    <img class="slide-image" src="' . $resultatPhotos[$i2]['photo'] . '" alt="photo de la salle ' . $resultatPhotos[$i2]['titre'] . '">
                                                </div>
                                            ';
                                        }
                                        else
                                        {
                                            echo '
                                                <div class="item">
                                                    <img class="slide-image" src="' . $resultatPhotos[$i2]['photo'] . '" alt="photo de la salle ' . $resultatPhotos[$i2]['titre'] . '">
                                                </div>
                                            ';
                                        }
                                    }
                                ?>
                            </div>
                            <a class="left carousel-control" href="#carousel-salles" data-slide="prev">
                                <span class="glyphicon glyphicon-chevron-left"></span>
                            </a>
                            <a class="right carousel-control" href="#carousel-salles" data-slide="next">
                                <span class="glyphicon glyphicon-chevron-right"></span>
                            </a>
                        </div>
                    </div>

                </div>

                <!-- AFFICHAGE DES PRODUITS -->
                <?php
                    
                    $contenu .= '<div class="row" id="fiches">';

                    if(!$_POST)
                    {
                        // Affichage des résultat du tri
                        if($requete->rowCount() == 0)
                        {
                            $contenu .= '<div class="col-xs-12"><p class="text-center">Aucun résultat n\'a été trouvé pour vos critères</p></div><div class="clearfix"></div>';
                        }
                        elseif($requete->rowCount() == 1) 
                        {
                            $contenu .= '<div class="col-xs-12"><p class="text-center">' . $requete->rowCount() . ' résultat</p></div><div class="clearfix"></div>';
                        }
                        else
                        {
                            $contenu .= '<div class="col-xs-12"><p class="text-center">' . $requete->rowCount() . ' résultats</p></div><div class="clearfix"></div>';
                        }

                        // METTRE EN PLACE LE LIEN VOIR PLUS SI PLUS DE 6 AFFICHAGES
                        $nbProduits = $requete->rowCount();
                        if($nbProduits > 6)
                        {
                            $nbAfficher = 6;
                        }
                        else
                        {
                            $nbAfficher = $nbProduits;
                        }

                        $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
                        //var_dump($resultat);
                        for($i = 0; $i < $nbAfficher; $i++)
                        {
                            $requeteNote->execute(array(
                                'id_salle' => $resultat[$i]['id_salle']
                            )); 
                            $resultatNote = $requeteNote->fetch(PDO::FETCH_ASSOC);
                            $rating = round($resultatNote['noteMoyenne']);

                            // Limiter les phrases à max 35 caractères
                            $description35 = substr($resultat[$i]['description'], 0, 35);
                            $positionEspace = strrpos($description35, ' ');
                            $description = substr($resultat[$i]['description'], 0, $positionEspace);

                            $contenu .= '
                                        <div class="col-sm-4 col-lg-4 col-md-4">
                                            <div class="thumbnail">
                                                <a href="fiche_produit.php?id_produit=' . htmlentities($resultat[$i]['id_produit']) . '"><img src="' . htmlentities($resultat[$i]['photo']) . '" alt="salle ' . htmlentities($resultat[$i]['titre']) . '"></a>
                                                <div class="caption">
                                                    <h4 class="pull-right">' . htmlentities($resultat[$i]['prix']) . ' €</h4>
                                                    <h4><a href="fiche_produit.php?id_produit=' . htmlentities($resultat[$i]['id_produit']) . '">' . $resultat[$i]['titre'] . '</a></h4>
                                                    <p>' . htmlentities($description) . '... <br><a href="fiche_produit.php?id_produit=' . htmlentities($resultat[$i]['id_produit']) . '">Lire la suite</a></p>
                                                    <p><span class="glyphicon glyphicon-calendar"></span> ' . htmlentities($resultat[$i]['date_arrivee_fr']) . ' au ' . htmlentities($resultat[$i]['date_depart_fr']) . '</p>
                                                </div>
                                                <div class="ratings">
                                                    <p class="pull-right"><a href="fiche_produit.php?id_produit=' . htmlentities($resultat[$i]['id_produit']) . '"><span class="glyphicon glyphicon-search"></span> Voir</a></p>
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

                        // AFFICHER PLUS DE PRODUITS
                        if(!isset($_GET['action']) && $nbProduits > 6)
                        {
                            $contenu .= '<div class="col-sm-12 text-center"><a href="?action=voir_plus#voir_plus" class="btn btn-outline btn-primary text-center" id="voir_plus">Voir plus<br><span class="glyphicon glyphicon-chevron-down"></span></a></div>';
                        }
                        elseif(isset($_GET['action']) && $nbProduits > 6)
                        {
                            $contenu .= '<div class="col-sm-12 text-center"><a href="' . RACINE_SITE . '#fiches" class="btn btn-outline btn-primary text-center" id="voir_plus">Cacher<br><span class="glyphicon glyphicon-chevron-up"></span></a></div>';
                        }
                        

                        if(isset($_GET['action']) && $_GET['action'] == 'voir_plus')
                        {

                            for($i = 6; $i < $nbProduits; $i++)
                            {
                                $requeteNote->execute(array(
                                    'id_salle' => $resultat[$i]['id_salle']
                                )); 
                                $resultatNote = $requeteNote->fetch(PDO::FETCH_ASSOC);
                                $rating = round($resultatNote['noteMoyenne']);

                                // Limiter les phrases à max 40 caractères
                                $description35 = substr($resultat[$i]['description'], 0, 35);
                                $positionEspace = strrpos($description35, ' ');
                                $description = substr($resultat[$i]['description'], 0, $positionEspace);

                                $contenu .= '
                                            <div class="col-sm-4 col-lg-4 col-md-4">
                                                <div class="thumbnail">
                                                    <a href="fiche_produit.php?id_produit=' . htmlentities($resultat[$i]['id_produit']) . '"><img src="' . htmlentities($resultat[$i]['photo']) . '" alt="salle ' . htmlentities($resultat[$i]['titre']) . '"></a>
                                                    <div class="caption">
                                                        <h4 class="pull-right">' . htmlentities($resultat[$i]['prix']) . ' €</h4>
                                                        <h4><a href="fiche_produit.php?id_produit=' . htmlentities($resultat[$i]['id_produit']) . '">' . $resultat[$i]['titre'] . '</a></h4>
                                                        <p>' . htmlentities($description) . '... <br><a href="fiche_produit.php?id_produit=' . htmlentities($resultat[$i]['id_produit']) . '">Lire la suite</a></p>
                                                        <p><span class="glyphicon glyphicon-calendar"></span> ' . htmlentities($resultat[$i]['date_arrivee_fr']) . ' au ' . htmlentities($resultat[$i]['date_depart_fr']) . '</p>
                                                    </div>
                                                    <div class="ratings">
                                                        <p class="pull-right"><a href="fiche_produit.php?id_produit=' . htmlentities($resultat[$i]['id_produit']) . '"><span class="glyphicon glyphicon-search"></span> Voir</a></p>
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
                        }
                    }
                    
                    $contenu .= '</div>';
                ?>

            </div>

        </div>
        <!-- <div class="row">
            <div class="col-sm-12">

            </div> 
        </div> -->

<?php

echo $contenu;
require_once('inc/bas.inc.php');