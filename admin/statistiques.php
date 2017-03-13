<?php
require_once('../inc/init.inc.php');
if(!membreConnecteAdmin())
{
    header('location:' . RACINE_SITE);
}

// TOP 5 SALLE LES MIEUX NOTEES
$requeteSallesNote = $bdd->query("SELECT avis.id_salle, AVG(avis.note) AS noteMoyenne, salle.titre 
	FROM avis INNER JOIN salle ON avis.id_salle = salle.id_salle GROUP BY id_salle ORDER BY noteMoyenne DESC, titre ASC LIMIT 0,5");
$resultatSallesNote = $requeteSallesNote->fetchAll(PDO::FETCH_ASSOC);

// TOP 5 SALLES LES PLUS COMMANDEES
$requeteSalleCommande = $bdd->query("SELECT commande.id_commande, commande.id_produit, produit.id_produit, produit.id_salle, COUNT(produit.id_salle) AS commandesSalle, salle.id_salle, salle.titre 
	FROM commande INNER JOIN (produit INNER JOIN salle ON produit.id_salle = salle.id_salle) ON commande.id_produit = produit.id_produit
	GROUP BY produit.id_salle ORDER BY commandesSalle DESC, titre ASC LIMIT 0,5");
$resultatSalleCommande = $requeteSalleCommande->fetchAll(PDO::FETCH_ASSOC);

// TOP 5 MEMBRES QUI ACHETENT LE PLUS (quantité)
$requeteMembreCommande = $bdd->query("SELECT commande.id_commande, commande.id_membre, COUNT(commande.id_membre) AS commandesMembre, membre.id_membre, membre.email
	FROM commande INNER JOIN membre ON commande.id_membre = membre.id_membre GROUP BY membre.id_membre ORDER BY commandesMembre DESC, email ASC LIMIT 0,5");
$resultatMembreCommande = $requeteMembreCommande->fetchAll(PDO::FETCH_ASSOC);

// TOP 5 MEMBRES QUI ACHETENT LE PLUS (prix)
$requeteMembrePrix = $bdd->query("SELECT commande.id_membre, membre.email, SUM(produit.prix) AS commandesPrix 
	FROM commande 
	INNER JOIN produit ON commande.id_produit = produit.id_produit 
	INNER JOIN membre ON commande.id_membre = membre.id_membre 
	GROUP BY membre.id_membre ORDER BY commandesPrix DESC, email ASC LIMIT 0,5");
$resultatMembrePrix = $requeteMembrePrix->fetchAll(PDO::FETCH_ASSOC);

require_once("../inc/haut.inc.php");
//var_dump($resultatSallesNote);

//echo '<br><br>';

//var_dump($resultatSalleCommande);

//echo '<br><br>';

//var_dump($resultatMembreCommande);

//echo '<br><br>';

//var_dump($resultatMembrePrix);

//echo $contenu;

?>

<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Statistiques</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-12 text-right">
                                    <div class="huge">Salles les mieux notées</div>
                                    <!-- <div>New Comments!</div> -->
                                </div>
                            </div>
                        </div>
                        <?php 
                            $keyNote = array();
                            $valueNote = array();
                            for($inote = 0; $inote  < 5; $inote++)
                            {
                                $noteMoyenne = round($resultatSallesNote[$inote]['noteMoyenne']);
                                array_push($keyNote, 'Salle ' . $resultatSallesNote[$inote]['titre']);
                                array_push($valueNote, $noteMoyenne);
                                ?>
                                <a href="#">
                                    <div class="panel-footer">
                                        <span id="salle<?php echo $inote; ?>" data-content="Salle <?php echo $resultatSallesNote[$inote]['titre']; ?>" class="pull-left"><?php echo 'Salle ' . $resultatSallesNote[$inote]['titre']; ?></span>
                                        <span id="note<?php echo $inote; ?>" data-content="<?php echo round($resultatSallesNote[$inote]['noteMoyenne'], 2); ?>" class="pull-right"><?php echo round($resultatSallesNote[$inote]['noteMoyenne'], 2); ?></span>
                                        <div class="clearfix"></div>
                                    </div>
                                </a>
                            <?php
                            }
                            //var_dump($keyNote);
                            //var_dump($valueNote);
                            $arrayNote = array_combine($keyNote, $valueNote);
                            //var_dump($arrayNote);
                            //echo json_encode($arrayNote);
                            ?>
                        
                        
                    </div>
                </div>
                <div class="col-sm-6">
                    <div id="noteChart">

                    </div>

                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="panel panel-green">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-12 text-right">
                                    <div class="huge">Salles les plus commandées</div>
                                    <!-- <div>New Tasks!</div> -->
                                </div>
                            </div>
                        </div>
                        <?php 
                            for($icomm = 0; $icomm < 5; $icomm++)
                            {?>
                                <a href="#">
                                    <div class="panel-footer">
                                        <span id="salleC<?php echo $icomm; ?>" data-content="Salle <?php echo $resultatSalleCommande[$icomm]['titre']; ?>" class="pull-left"><?php echo 'Salle ' . $resultatSalleCommande[$icomm]['titre']; ?></span>
                                        <span id="nbr<?php echo $icomm; ?>" data-content="<?php echo $resultatSalleCommande[$icomm]['commandesSalle']; ?>" class="pull-right"><?php echo $resultatSalleCommande[$icomm]['commandesSalle']; ?></span>
                                        
                                        <!-- <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span> -->
                                        <div class="clearfix"></div>
                                    </div>
                                </a>
                            <?php
                            }
                            ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div id="salleCommandeChart">
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="panel panel-yellow">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-12 text-right">
                                    <div class="huge">Membres qui achètent le plus</div>
                                </div>
                            </div>
                        </div>
                        <?php 
                            for($iplus = 0; $iplus < 5; $iplus++)
                            {?>
                                <a href="#">
                                    <div class="panel-footer">
                                        <span id="membreC<?php echo $iplus; ?>" data-content="<?php echo $resultatMembreCommande[$iplus]['email']; ?>" class="pull-left"><?php echo $resultatMembreCommande[$iplus]['email']; ?></span>
                                        <span id="nbrCom<?php echo $iplus; ?>" data-content="<?php echo $resultatMembreCommande[$iplus]['commandesMembre']; ?>" class="pull-right"><?php echo $resultatMembreCommande[$iplus]['commandesMembre']; ?></span>
                                        <div class="clearfix"></div>
                                    </div>
                                </a>
                            <?php
                            }
                            ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div id="membreQuantChart">
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="panel panel-red">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-12 text-right">
                                    <div class="huge">Membres qui achètent le plus cher</div>
                                </div>
                            </div>
                        </div>
                        <?php 
                            for($iprix = 0; $iprix < 5; $iprix++)
                            {?>
                                <a href="#">
                                    <div class="panel-footer">
                                        <span id="membreP<?php echo $iprix; ?>" data-content="<?php echo $resultatMembrePrix[$iprix]['email']; ?>" class="pull-left"><?php echo $resultatMembrePrix[$iprix]['email']; ?></span>
                                        <span id="prix<?php echo $iprix; ?>" data-content="<?php echo $resultatMembrePrix[$iprix]['commmandesPrix']; ?>" class="pull-right"><?php echo $resultatMembrePrix[$iprix]['commandesPrix']; ?> €</span>
                                        <div class="clearfix"></div>
                                    </div>
                                </a>
                            <?php
                            }
                            ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="flot-chart">
                        <div class="flot-chart-content" id="membrePrixChart"></div>
                    </div>
                </div>
            </div>
            <hr>
            <!-- /.row -->

            <div class="row">
                

            </div>

<?php

require_once('../inc/bas.inc.php');