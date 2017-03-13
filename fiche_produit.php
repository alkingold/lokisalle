<?php
require_once('inc/init.inc.php');

$erreur = "";

// VERIFIE Si EXISTE PRODUIT AVEC UN ID DONNE DANS L'URL 
if(isset($_GET['id_produit']) && !empty($_GET['id_produit']))
{
    $requeteExisteIdProduit = $bdd->prepare("SELECT * FROM produit WHERE id_produit = :id_produit");
    $requeteExisteIdProduit->execute(array(
        'id_produit' => $_GET['id_produit']
    ));

    if($requeteExisteIdProduit->rowCount() == 0)
    {
        require_once("inc/haut.inc.php");
        echo '<div class="row"><div class="col-sm-12"><p class="alert alert-danger">Le produit que vous recherchez n\'existe pas</p></div></div>';
        echo '<div class="row"><div class="col-sm-12"><a href="' . RACINE_SITE . '" class="btn btn-outline btn-danger"><span class="glyphicon glyphicon-chevron-left"></span>Retour à l\'accueil</a></div></div>';
    }
    else
    {
        // SELECTION DES INFORMATIONS DU PRODUIT DE LA BASE DE DONNEES
        $requete = $bdd->query("SELECT produit.id_produit, produit.date_arrivee, produit.date_depart, DATE_FORMAT(produit.date_arrivee,'%d/%m/%Y') AS date_arrivee_fr, DATE_FORMAT(produit.date_depart,'%d/%m/%Y') AS date_depart_fr, produit.prix, produit.etat, salle.id_salle, salle.titre, salle.description, salle.photo, salle.pays, salle.ville, salle.adresse, salle.cp, salle.capacite, salle.categorie 
            FROM produit INNER JOIN salle ON produit.id_salle = salle.id_salle 
            WHERE id_produit = $_GET[id_produit]");
        $resultat = $requete->fetch(PDO::FETCH_ASSOC);

        // ADRESSE POUR LE MAP DYNAMIQUE
        $adresse_explode = explode(' ', $resultat['adresse']);
        $adresse_implode = implode('+', $adresse_explode);
        $adresse = $adresse_implode . $resultat['cp'] . '+' . $resultat['ville'];
        //echo $adresse . '<br>';
        //echo 'https://maps.google.com/maps/place?key=AIzaSyBpZ01e1xjipodFFExMR6p4JThRfiloH80&amp;q=' . $adresse;

        // VERIFIE SI LA SALLE EST DEJA COMMANDEE
        $requeteCommande = $bdd->prepare("SELECT * FROM commande WHERE id_produit = :id_produit");
        $requeteCommande->execute(array(
            'id_produit' => $_GET['id_produit']
        ));

        // RESERVATION D'UNE SALLE
        if(isset($_GET['action']) && $_GET['action'] == 'reserver' && isset($_GET['id_produit']) && !empty($_GET['id_produit']))
        {
            // VERIFIER QUE LE PRODUIT N'A PAS DEJA ETE COMMANDE (ID PRODUIT DOIT ETRE UNIQUE DANS LES COMMANDES)
            $requeteIdProduit = $bdd->prepare("SELECT * FROM commande WHERE id_produit = :id_produit");
            $requeteIdProduit->execute(array(
                'id_produit' => $_GET['id_produit']
            ));
            if($requeteIdProduit->rowCount() == 0)
            {
                // MISE A JOUR DE L'ETAT DU PRODUIT (indisponible)
                $requeteEtat = $bdd->prepare("UPDATE produit SET etat = 'reservation' WHERE id_produit = :id_produit");
                $requeteEtat->execute(array(
                    'id_produit' => $_GET['id_produit']
                ));

                // INSERTION DES INFORMATIONS DANS LA TABLE DES COMMANDES
                $requeteCommandeInsert = $bdd->prepare("INSERT INTO commande(id_membre, id_produit, date_enregistrement) VALUES(:id_membre, :id_produit, now())");
                $requeteCommandeInsert->execute(array(
                    'id_membre' => $_SESSION['membre']['id_membre'],
                    'id_produit' => $_GET['id_produit']
                ));

                $dernierId = $bdd->lastInsertId();

                // AJOUT DES PRODUITS DANS LA SESSION DU MEMBRE
                if(!isset($_SESSION['commande'])) $_SESSION['commande'] = array();
                $_SESSION['commande'][] = $dernierId;
 
                //echo $nb_lignes;
                if($requeteCommandeInsert->rowCount() != 0 && $requeteEtat->rowCount() != 0)
                {
                    $nb_lignes = $requeteCommandeInsert->rowCount();
                    if($nb_lignes == 1)
                    {
                        $_SESSION['confirmation']['message'] = $nb_lignes . ' commande a été ajoutée avec succès';
                    }
                    elseif($nb_lignes > 1)
                    {
                        $_SESSION['confirmation']['message'] = $nb_lignes . ' commandes ont été ajoutées avec succès';
                    }
                }
                else
                {
                    $_SESSION['confirmation']['erreur'] = 'La commande n\'a pas pu être ajoutée'; 
                }

                //var_dump($_SESSION['commande']);
                header('location:' . RACINE_SITE . '/fiche_produit.php?id_produit=' . $_GET['id_produit']);
                exit;
            }
            else
            {
                $erreur .= 'Ce produit a déjà ete commandé. <a href="' . RACINE_SITE . 'profil.php">Voir mes commandes</a>';
            }
                        
        }


        // chercher les infos sur la salle
        $requeteSalle = $bdd->prepare("SELECT * FROM produit WHERE id_produit = :id_produit");
        $requeteSalle->execute(array(
            'id_produit' => $_GET['id_produit']
        ));

        // TRAITEMENT DU FORMULAIRE AVIS
        $erreurAvis = '';

        // SI POSTE FORMULAIRE COMMENTAIRE
        if($_POST)
        {
            // VERIFICATIONS FORMULAIRE
            // si le champ commentaire est vide
            if(empty($_POST['commentaire']))
            {
                $erreurAvis .= 'Veuillez entrer le commentaire<br>';
            }
            // limitation de caractères pour le commentaire
            if(!preg_match("#^[0-9a-zàâäéèêëîïçôöœüûA-ZÀÄÂÉÈÊËÎÏÇÔÖŒÛÜ() .,;:!?/\"'-]{2,}$#", $_POST['commentaire']))
            {
                $erreurAvis .= 'Votre message contient des caractères interdits.<br>';
            }
            if($erreurAvis == '')
            {
                // s'il n'y a pas d'erreurs
                $resultatSalle = $requeteSalle->fetch(PDO::FETCH_ASSOC);
                // insérer les informations dans la base
                $reqInsertComment = $bdd->prepare("INSERT INTO avis(id_membre, id_salle, commentaire, note, date_enregistrement) 
                    VALUES(:id_membre, :id_salle, :commentaire, :note, now())");
                $reqInsertComment->execute(array(
                    'id_membre' => $_SESSION['membre']['id_membre'],
                    'id_salle' => $resultatSalle['id_salle'],
                    'commentaire' => $_POST['commentaire'],
                    'note' => $_POST['note']
                ));

                $nb_lignes = $requete->rowCount(); 
                //echo $nb_lignes;
                if($requete->rowCount() != 0)
                {
                    $nb_lignes = $requete->rowCount();
                    if($nb_lignes == 1)
                    {
                        $_SESSION['confirmation']['message'] = $nb_lignes . ' avis a été ajouté avec succès';
                    }
                    elseif($nb_lignes > 1)
                    {
                        $_SESSION['confirmation']['message'] = $nb_lignes . ' avis ont été rajoutés avec succès';
                    }
                }
                else
                {
                    $_SESSION['confirmation']['erreur'] = 'L\'avis n\'a pas pu être rajouté'; 
                }

                header('location:' . RACINE_SITE . 'fiche_produit.php?id_produit=' . $_GET['id_produit']);
                exit;
            }
        }


        // REQUETE POUR LES AVIS ET LE RATING
        $resultatSalle = $requeteSalle->fetch(PDO::FETCH_ASSOC);

        // Afficher la note de la salle
        $requeteAvisNote = $bdd->prepare("SELECT avis.id_avis, avis.id_membre, avis.id_salle, avis.commentaire, avis.note, AVG(note) AS noteMoyenne, DATE_FORMAT(avis.date_enregistrement, 'le %d/%m/%Y à %H:%m') AS date_enreg_fr, membre.pseudo
            FROM avis INNER JOIN membre ON avis.id_membre = membre.id_membre WHERE id_salle = :id_salle");
        $requeteAvisNote->execute(array(
            'id_salle' => $resultatSalle['id_salle']
        ));
        // calcul de la note moyenne
        $resultatAvisNote = $requeteAvisNote->fetchAll(PDO::FETCH_ASSOC);
        $rating = round($resultatAvisNote[0]['noteMoyenne']);


        // REQUETE AUTRES PRODUITS SALLE (dates)
        $requeteSalleDispo = $bdd->prepare("SELECT id_produit, DATE_FORMAT(date_arrivee,'%d/%m/%Y') AS date_arrivee_fr, DATE_FORMAT(date_depart,'%d/%m/%Y') AS date_depart_fr 
            FROM produit 
            WHERE date_arrivee > now() AND etat = 'libre' AND id_salle = :id_salle AND id_produit != :id_produit
            ORDER BY date_arrivee");
        $requeteSalleDispo->execute(array(
            'id_salle' => $resultatSalle['id_salle'],
            'id_produit' => $_GET['id_produit']
        ));

        require_once("inc/haut.inc.php");

        if(isset($_SESSION['confirmation']['message']))
        {?>
            <div class="row"><div class="col-sm-12"><p class="alert alert-success"><?php echo $_SESSION['confirmation']['message']; ?></p></div></div>
        <?php
        }
        if(isset($_SESSION['confirmation']['erreur']))
        {?>
            <div class="row"><div class="col-sm-12"><p class="alert alert-danger"><?php echo $_SESSION['confirmation']['erreur']; ?></p></div></div>
        <?php
        }

        ?>
        <!-- TITRE DE LA SALLE -->

                <div class="row">
                    <div class="col-sm-10">
                        <h1 class="page-header">Salle <?php echo $resultat['titre'];?> &nbsp; 
                            <?php
                                for($inote = 1; $inote <= $rating; $inote++)
                                {
                                    echo ' <small><span class="glyphicon glyphicon-star"></span></small> ';
                                }
                            ?>
                        </h1>
                    </div>
                    <div class="col-sm-2">
                        <?php 
                            if(isset($_SESSION['membre']))
                            {
                                if($requeteCommande->rowCount() != 0)
                                {
                                ?>
                                   <p><a href="<?php echo '?id_produit=' . htmlentities($_GET['id_produit']) . '&action=reserver'; ?>" class="btn btn-outline btn-danger pull-right disabled">Réservé</a> </p>
                                <?php
                                }
                                elseif($resultat['date_arrivee'] < date('Y-m-d H:i:s'))
                                {
                                   ?>
                                   <p><a href="<?php echo '?id_produit=' . htmlentities($_GET['id_produit']) . '&action=reserver'; ?>" class="btn btn-outline btn-danger pull-right disabled">Indisponible</a> </p>
                                <?php 
                                }
                                else
                                {?>
                                    <p><a href="<?php echo '?id_produit=' . htmlentities($_GET['id_produit']) . '&action=reserver'; ?>" class="btn btn-outline btn-danger pull-right">Réserver</a></p>
                                <?php
                                }
                                ?>

                                    <br><br><p><a href="<?php echo RACINE_SITE . 'profil.php'; ?>" class="btn btn-outline btn-danger pull-right">Voir vos réservations</a></p>
                                <?php
                            }
                            else
                            {?>
                                
                                <p><a href="#" class="btn btn-outline btn-danger pull-right" data-toggle="modal" data-target="#connexion">Se connecter pour réserver</a></p><br><br>
                                <p><a href="#" class="btn btn-outline btn-danger pull-right" data-toggle="modal" data-target="#inscription">S'inscrire pour réserver</a></p>
                            <?php
                            }
                            ?>
                        
                        
                    </div>
                </div>
                <!-- /.row -->

                <!-- PHOTO, DESCRIPTION, LOCALISATION -->
                <div class="row">

                    <div class="col-md-8">
                        <img class="img-responsive" src="<?php echo htmlentities($resultat['photo']); ?>" alt="photo de la salle <?php htmlentities($resultat['titre']); ?>">
                    </div>

                    <div class="col-md-4">

                        <h3 class="loca">Localisation</h3>
                        <div id="map">
                            <!--<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2784.141033533551!2d4.832475615566976!3d45.748318479105365!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47f4ea491f219e49%3A0xf5ee3a2d4cbc165c!2s28+Quai+Claude+Bernard%2C+69007+Lyon!5e0!3m2!1sfr!2sfr!4v1486218397625" width="600" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>-->

                            <iframe
                                src="https://www.google.com/maps/embed/v1/place?key=AIzaSyBpZ01e1xjipodFFExMR6p4JThRfiloH80&amp;q=<?php echo $adresse; ?>">
                                
                            </iframe>
                        </div>

                        

                    </div>

                </div>

                <div class="row">
                    <div class="col-sm-8">
                        <h3>Description</h3>
                        <p>
                            <?php echo $resultat['description']; ?>
                        </p>
                    </div>
                    <div class="col-sm-4">
                        <h3>Autres disponibilités</h3>
                        <p>
                            <?php 
                                if($requeteSalleDispo->rowCount() == 0)
                                {
                                    echo 'Pas d\'autres disponibilités actuellement';
                                }
                                else
                                {
                                    while($resultatSalleDispo = $requeteSalleDispo->fetch())
                                    {
                                    ?>
                                        
                                        <a href="<?php echo RACINE_SITE . 'fiche_produit.php?id_produit=' . $resultatSalleDispo['id_produit']; ?>">
                                            <span class="glyphicon glyphicon-calendar"></span> <?php echo $resultatSalleDispo['date_arrivee_fr'] . ' - ' . $resultatSalleDispo['date_depart_fr']; ?>
                                        </a><br>
                                    <?php
                                    }
                                }
                                
                            ?>
                            
                        </p>
                    </div>
                </div>

                <div class="row">
                    <h3 class="col-sm-12">Informations complémentaires</h3>
                </div>

                <div class="row">
                    <div class="col-sm-4">
                        <p><span class="glyphicon glyphicon-calendar"></span> Arrivée : <?php echo $resultat['date_arrivee_fr']; ?></p>
                        <p><span class="glyphicon glyphicon-calendar"></span></span> Départ : <?php echo $resultat['date_depart_fr']; ?></p>
                    </div>
                    <div class="col-sm-4">
                        <p><span class="glyphicon glyphicon-user"></span></span> Capacité : <?php echo $resultat['capacite']; ?></p>
                        <p><span class="glyphicon glyphicon-inbox"></span></span> Catégorie : <?php echo $resultat['categorie']; ?></p>
                    </div>
                    <div class="col-sm-4">
                        <p><span class="glyphicon glyphicon-map-marker"></span></span> Adresse : <?php echo $resultat['adresse'] . ', ' . $resultat['cp'] . ', ' . $resultat['ville']; ?></p>
                        <p><span class="glyphicon glyphicon-euro"></span></span> Tarif : <?php echo $resultat['prix']; ?>€</p>
                    </div>
                </div>

        <?php

        // AFFICHAGE DE 4 PRODUITS DISPONIBLES AUX MEMES DATES
        $requeteProduits = $bdd->query("SELECT produit.id_produit, produit.id_salle, salle.photo, salle.id_salle 
            FROM produit INNER JOIN salle ON produit.id_salle = salle.id_salle WHERE produit.etat = 'libre' AND produit.date_arrivee <= '$resultat[date_arrivee]' AND produit.date_depart >= '$resultat[date_depart]' AND produit.id_produit != $resultat[id_produit] LIMIT 0, 4");

        $contenu .= '<div class="row"><div class="col-sm-12"><h3>Autres produits disponibles aux mêmes dates</h3></div></div>';
        $contenu .= '<div class="row">';
        while($resultatProduits = $requeteProduits->fetch(PDO::FETCH_ASSOC))
        {
            $contenu .= '<div class="col-xs-3"><a href="' . RACINE_SITE . 'fiche_produit.php?id_produit=' . $resultatProduits['id_produit'] . '"><img src="' . $resultatProduits['photo'] . '" class="img-responsive"></div>';
        }
        $contenu .= '</div>';

        if($requeteProduits->rowCount() == 0)
        {
            $contenu .= '<div class="row"><div class="col-sm-12">Il n\'y a pas d\'autres produits disponibles aux mêmes dates</div></div>';
        }

        $contenu .= '<div class="row">
                        <hr>';

                        if(isset($_SESSION['membre']))
                        {
                            $contenu .= '<div class="col-sm-6"><p><a class="btn btn-outline btn-primary" href="?id_produit=' . htmlentities($_GET['id_produit']) . '&action=commentaire#commentaire"><span class="glyphicon glyphicon-chevron-left"></span> Déposer un commentaire</a></p></div>';
                        }
                        else
                        {
                            $contenu .= '<div class="col-sm-6"><p><a class="btn btn-outline btn-primary" data-toggle="modal" data-target="#connexion" href="#"><span class="glyphicon glyphicon-chevron-left"></span> Se connecter pour déposer un commentaire</a></p></div>';
                        }

                        
        $contenu .=     '<div class="col-sm-6"><p class="pull-right"><a class="btn btn-outline btn-primary" href="' . RACINE_SITE . '">Retour vers le catalogue <span class="glyphicon glyphicon-chevron-right"></span></a></p></div>
                    </div>';


        // AFFICHAGE DU FORMULAIRE COMMENTAIRES
        if(isset($_GET['action']) && $_GET['action'] == 'commentaire')
        {
            $contenu .= '<div class="row">
                            <div class="col-sm-6">
                                <form method="post" id="commentaire">
                                    <div class="form-group">
                                        <label for="commentaire">Commentaire</label>
                                        <textarea class="form-control" name="commentaire" id="commentaire"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="note">Note sur 5</label>
                                        <select class="form-control" name="note" id="note">';
                                            for($i = 1; $i <= 5; $i++)
                                            {
                                                $contenu .= '<option name="note" value="' . $i . '">' . $i . '</option>';
                                            }
            $contenu .= 
                                        '</select>
                                    </div>
                                    <input type="submit" class="btn btn-outline btn-danger" value="Envoyer">

                                </form>';

                                if($erreur != '')
                                        {
                                            // Sinon aficher les erreurs
                                            $contenu .= '<div class="alert-danger"><p>' . $erreur . '</p></div>';
                                        }
            $contenu .=
                        '</div></div>
            ';  
        }

        // AFFICHAGE DES AVIS SUR LA SALLE
        // Récupérer l'id salle

        //echo $resultatSalle['id_salle'];

        $requeteAvis = $bdd->prepare("SELECT avis.id_avis, avis.id_membre, avis.id_salle, avis.commentaire, avis.note, DATE_FORMAT(avis.date_enregistrement, 'le %d/%m/%Y à %H:%m') AS date_enreg_fr, membre.pseudo
            FROM avis INNER JOIN membre ON avis.id_membre = membre.id_membre WHERE id_salle = :id_salle");
        $requeteAvis->execute(array(
            'id_salle' => $resultatSalle['id_salle']
        ));

        $contenu .= '<div class="row"><div class="col-sm-12"><h3>Avis des clients</h3></div></div>';
        // 
        if($requeteAvis->rowCount() == 0)
        {
            $contenu .= '<div class="row"><div class="col-sm-12"><p>Soyez le premier à déposer un avis.</p></div></div>';
        }


        while($resultatAvis = $requeteAvis->fetch(PDO::FETCH_ASSOC))
        {
            //var_dump($resultatAvis);
            $contenu .= '<div class="row">';
            $contenu .= '<div class="col-sm-6">';
            $contenu .= '<p>';
            for($i = 1; $i <= $resultatAvis['note']; $i++)
            {
                $contenu .= ' <span class="glyphicon glyphicon-star"></span> ';
            }
            $contenu .= '<br>';

            $contenu .= 'Posté par <b>' . $resultatAvis['pseudo'] . '</b><br>';
            $contenu .= '<i>' . $resultatAvis['date_enreg_fr'] . '</i><br>';
            $contenu .= $resultatAvis['commentaire'];
            $contenu .= '</p>';
            $contenu .= '</div>';
            $contenu .= '</div>';
        }

        echo $contenu;
    }
}
else
{
    require_once("inc/haut.inc.php");
        echo '<div class="row"><div class="col-sm-12"><p class="alert alert-danger">Le produit que vous recherchez n\'existe pas</p></div></div>';
        echo '<div class="row"><div class="col-sm-12"><a href="' . RACINE_SITE . '" class="btn btn-outline btn-danger"><span class="glyphicon glyphicon-chevron-left"></span>Retour à l\'accueil</a></div></div>';
}

if(isset($_SESSION['confirmation']) && !isset($_GET['action']))
{
    unset($_SESSION['confirmation']);
}

require_once('inc/bas.inc.php');