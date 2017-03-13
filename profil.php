<?php
require_once('inc/init.inc.php');

// ACCES A LA PAGE UNIQUEMENT AUX MEMBRES CONNECTES
if(!membreConnecte())
{
    header('location:' . RACINE_SITE);
    exit;
}

$erreurAvis = "";
$erreurCommande = "";

$affichageAdmin = membreConnecteAdmin();

// POUR VOIR UN PROFIL D'UN MEMBRE COTE ADMIN
// Si défini id membre dans l'url (pour voir le profil d'un autre membre)
if(isset($_GET['id_membre']) && !empty($_GET['id_membre']))
{
    // les profils des autres membres accessibles que pour admin
    if(!$affichageAdmin)
    {
        $erreur .= 'Seul l\'administrateur a accès aux profils des autres membres';
        $erreur .= '<div class="row"><div class="col-sm-12"><a href="' . RACINE_SITE . '" class="btn btn-outline btn-danger"><span class="glyphicon glyphicon-chevron-left"></span>Retour à l\'accueil</a></div></div>';
        $id_membre = $_SESSION['membre']['id_membre'];
    }
    else
    {
        $id_membre = $_GET['id_membre'];
        // vérifie si existe id_membre renseigné dans l'url
        $requeteIdExiste = $bdd->prepare("SELECT * FROM membre WHERE id_membre = :id_membre");
        $requeteIdExiste->execute(array(
            'id_membre' => $id_membre
        ));
        if($requeteIdExiste->rowCount() == 0)
        {
            $id_membre = $_SESSION['membre']['id_membre'];
            $erreur .= 'Le membre que vous recherchez n\'éxiste pas';
            $erreur .= '<div class="row"><div class="col-sm-12"><a href="' . RACINE_SITE . '" class="btn btn-outline btn-danger"><span class="glyphicon glyphicon-chevron-left"></span>Retour à l\'accueil</a></div></div>';
        }

    }
}
// SINON AFFICHAGE DES DONNEES DU MEMBRE DE LA SESSION
else
{
    $id_membre = $_SESSION['membre']['id_membre'];
    //$affichageAdmin = false;
}

// RECHERCHER LES INFOS DU PROFIL DU MEMBRE
$requeteProfil = $bdd->prepare('SELECT * FROM membre WHERE id_membre = :id_membre');
$requeteProfil->execute(array(
    'id_membre' => $id_membre
));
$resultatProfil = $requeteProfil->fetch(PDO::FETCH_ASSOC);
if($resultatProfil['civilite'] == 'f')
{
    $civilite = 'Femme';
}
else
{
    $civilite = 'Homme';
}

// RECHERCHER LES COMMANDES DU MEMBRE
//var_dump($_SESSION['membre']);
$requete = $bdd->prepare("SELECT commande.id_commande, commande.id_produit, DATE_FORMAT(commande.date_enregistrement,'%d/%m/%Y %H:%i') AS date_enreg_fr, produit.id_produit, produit.id_salle, produit.date_arrivee, DATE_FORMAT(produit.date_arrivee,'%d/%m/%Y %H:%i') AS date_arrivee_fr, produit.date_depart, DATE_FORMAT(produit.date_depart,'%d/%m/%Y %H:%i') AS date_depart_fr, produit.prix, salle.id_salle, salle.titre, salle.description, salle.photo, salle.ville, salle.adresse, salle.cp, salle.categorie, salle.capacite 
	FROM commande INNER JOIN (produit INNER JOIN salle ON produit.id_salle = salle.id_salle) ON commande.id_produit = produit.id_produit WHERE id_membre = :id_membre");
$requete->execute(array(
	'id_membre' => $id_membre
));
$resultat = $requete->fetchAll(PDO::FETCH_ASSOC);
// var_dump($resultat);

// RECHERCHER LES AVIS POSTES PAR LE MEMBRE
$requeteAvis = $bdd->prepare("SELECT avis.id_salle, avis.id_avis, avis.id_membre, avis.commentaire, avis.note, avis.date_enregistrement, DATE_FORMAT(avis.date_enregistrement,'le %d/%m/%Y à %H:%i') AS date_enreg_fr, salle.id_salle, salle.titre, salle.photo, salle.adresse, salle.cp, salle.ville
    FROM avis INNER JOIN salle ON avis.id_salle = salle.id_salle WHERE avis.id_membre = :id_membre");
$requeteAvis->execute(array(
    'id_membre' => $id_membre
));
$resultatAvis = $requeteAvis->fetchAll(PDO::FETCH_ASSOC);

// MODIFICATIONS
if($_POST)
{
    // MODIFICATION DU NOM
    if(isset($_POST['modifier_nom']))
    {
        // CONTROLES
        $nom = htmlentities($_POST['nom']);
        // VERIFICATION DU NOM
        // si le nom est vide
        if(empty($_POST['nom']))
        {
            $erreur .= 'Veuillez renseigner votre nom.<br>';
        }
        // la longueur du nom doit être entre 2 et 40
        if(strlen($nom) < 2 || strlen($nom) > 40)
        {
            $erreur .= 'Votre nom peut contenir entre 2 et 40 caractères.<br>';
        }
        // limitation de caractères pour le nom
        if(!preg_match("#^[a-zàâäéèêëîïçôöœüûA-ZÀÄÂÉÈÊËÎÏÇÔÖŒÛÜ' -]{2,40}$#", $_POST['nom']))
        {
            $erreur .= 'Votre nom peut contenir des lettres et " - " .<br>';
        }
        if($erreur == "")
        {
            $requeteUpdate = $bdd->prepare("UPDATE membre SET nom = :nom WHERE id_membre = :id_membre");
            $requeteUpdate->execute(array(
                'nom' => $nom,
                'id_membre' => $id_membre
            ));
            if($id_membre == $_SESSION['membre']['id_membre'])
            {
                if($requeteUpdate->rowCount() != 0)
                {
                    $_SESSION['confirmation']['message']['profil'] = 'Votre nom a été mis à jour avec succès';
                }
                else
                {
                    $_SESSION['confirmation']['erreur']['profil'] = 'Votre nom n\'a pas pu être mis à jour'; 
                }
                header('location:' . RACINE_SITE . 'profil.php');
                exit;
            }
            else
            {
                if($requeteUpdate->rowCount() != 0)
                {
                    $_SESSION['confirmation']['message']['profil'] = 'Le nom a été mis à jour avec succès';
                }
                else
                {
                    $_SESSION['confirmation']['erreur']['profil'] = 'Le nom n\'a pas pu être mis à jour'; 
                }
                header('location:' . RACINE_SITE . 'profil.php?id_membre=' . $id_membre);
                exit;
            }
        }
    }

    // MODIFICATION DU PRENOM
    if(isset($_POST['modifier_prenom']))
    {
        // CONTROLES
        $prenom = htmlentities($_POST['prenom']);
        // VERIFICATION DU PRENOM
        // si le prenom est vide
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
            $erreur .= 'Votre prénom peut contenir des lettres, " - " et \'.<br>';
        }
        if($erreur == "")
        {
            $requeteUpdate = $bdd->prepare("UPDATE membre SET prenom = :prenom WHERE id_membre = :id_membre");
            $requeteUpdate->execute(array(
                'prenom' => $prenom,
                'id_membre' => $id_membre
            ));
            if($id_membre == $_SESSION['membre']['id_membre'])
            {
                if($requeteUpdate->rowCount() != 0)
                {
                    $_SESSION['confirmation']['message']['profil'] = 'Votre prénom a été mis à jour avec succès';
                }
                else
                {
                    $_SESSION['confirmation']['erreur']['profil'] = 'Votre prénom n\'a pas pu être mis à jour'; 
                }
                header('location:' . RACINE_SITE . 'profil.php');
                exit;
            }
            else
            {
                if($requeteUpdate->rowCount() != 0)
                {
                    $_SESSION['confirmation']['message']['profil'] = 'Le prénom a été mis à jour avec succès';
                }
                else
                {
                    $_SESSION['confirmation']['erreur']['profil'] = 'Le prénom n\'a pas pu être mis à jour'; 
                }
                header('location:' . RACINE_SITE . 'profil.php?id_membre=' . $id_membre);
                exit;
            }
        }
    }

    // MODIFICATION DE LA CIVILITE
    if(isset($_POST['modifier_civilite']))
    {
        
        $requeteUpdate = $bdd->prepare("UPDATE membre SET civilite = :civilite WHERE id_membre = :id_membre");
        $requeteUpdate->execute(array(
        'civilite' => $_POST['civilite'],
        'id_membre' => $id_membre
        ));
        if($id_membre == $_SESSION['membre']['id_membre'])
        {
            if($requeteUpdate->rowCount() != 0)
            {
                $_SESSION['confirmation']['message']['profil'] = 'Votre civilité a été mise à jour avec succès';
            }
            else
            {
                $_SESSION['confirmation']['erreur']['profil'] = 'Votre civilité n\'a pas pu être mise à jour'; 
            }
            header('location:' . RACINE_SITE . 'profil.php');
            exit;
        }
        else
        {
            if($requeteUpdate->rowCount() != 0)
            {
                $_SESSION['confirmation']['message']['profil'] = 'La civilité a été mise à jour avec succès';
            }
            else
            {
                $_SESSION['confirmation']['erreur']['profil'] = 'La civilité n\'a pas pu être mise à jour'; 
            }
            header('location:' . RACINE_SITE . 'profil.php?id_membre=' . $id_membre);
        }
    }

    // MODIFICATION DU PSEUDO
    if(isset($_POST['modifier_pseudo']))
    {
        // CONTROLES
        $pseudo = htmlentities($_POST['pseudo']);
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
        // vérifier si pseudo unique
        if($erreur == "")
        {
            $requetePseudo = $bdd->prepare("SELECT * FROM membre WHERE pseudo = :pseudo AND id_membre != :id_membre");
            $requetePseudo->execute(array(
                'pseudo' => $pseudo,
                'id_membre' => $id_membre
            ));
            if($requetePseudo->rowCount() != 0)
            {
                $erreur .= 'Ce pseudo existe déjà : veuillez en choisir un autre';
            }
        }
        if($erreur == "")
        {
            $requeteUpdate = $bdd->prepare("UPDATE membre SET pseudo = :pseudo WHERE id_membre = :id_membre");
            $requeteUpdate->execute(array(
                'pseudo' => $pseudo,
                'id_membre' => $id_membre
            ));
            if($id_membre == $_SESSION['membre']['id_membre'])
            {
                if($requeteUpdate->rowCount() != 0)
                {
                    $_SESSION['confirmation']['message']['profil'] = 'Votre pseudo a été mis à jour avec succès';
                }
                else
                {
                    $_SESSION['confirmation']['erreur']['profil'] = 'Votre pseudo n\'a pas pu être mis à jour'; 
                }
                header('location:' . RACINE_SITE . 'profil.php');
                exit;
            }
            else
            {
                if($requeteUpdate->rowCount() != 0)
                {
                    $_SESSION['confirmation']['message']['profil'] = 'Le pseudo a été mis à jour avec succès';
                }
                else
                {
                    $_SESSION['confirmation']['erreur']['profil'] = 'Le pseudo n\'a pas pu être mis à jour'; 
                }
                header('location:' . RACINE_SITE . 'profil.php?id_membre=' . $id_membre);
                exit;
            }
        }
    }

    // MODIFICATION DU MAIL
    if(isset($_POST['modifier_email']))
    {
        // CONTROLES
        $email = htmlentities($_POST['email']);
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
        if($erreur == "")
        {
            $requeteUpdate = $bdd->prepare("UPDATE membre SET email = :email WHERE id_membre = :id_membre");
            $requeteUpdate->execute(array(
                'email' => $email,
                'id_membre' => $id_membre
            ));
            if($id_membre == $_SESSION['membre']['id_membre'])
            {
                if($requeteUpdate->rowCount() != 0)
                {
                    $_SESSION['confirmation']['message']['profil'] = 'Votre email a été mis à jour avec succès';
                }
                else
                {
                    $_SESSION['confirmation']['erreur']['profil'] = 'Votre email n\'a pas pu être mis à jour'; 
                }
                header('location:' . RACINE_SITE . 'profil.php');
                exit;
            }
            else
            {
                if($requeteUpdate->rowCount() != 0)
                {
                    $_SESSION['confirmation']['message']['profil'] = 'Le pseudo a été mis à jour avec succès';
                }
                else
                {
                    $_SESSION['confirmation']['erreur']['profil'] = 'Le pseudo n\'a pas pu être mis à jour'; 
                }
                header('location:' . RACINE_SITE . 'profil.php?id_membre=' . $id_membre);
                exit;
            }
        }
    }

    // MODIFICATION DU MOT DE PASSE
    if(isset($_POST['modifier_mdp']))
    {
        // CONTROLES
        // Vérifications ancien mdp
        if(empty($_POST['mdp_ancien']))
        {
            $erreur .= 'Veuillez renseigner votre mot de passe actuel.<br>';
        }
        else
        {
            $mdp_ancien = htmlentities($_POST['mdp_ancien']);
            // Si mot de passe est renseigné recherche s'il correspond à celui dans la base de données
            $requeteMdp = $bdd->prepare("SELECT * FROM membre WHERE id_membre = :id_membre");
            $requeteMdp->execute(array(
                'id_membre' => $id_membre
            ));
            $resultatMdp = $requeteMdp->fetch(PDO::FETCH_ASSOC);
            if(!password_verify($mdp_ancien, $resultatMdp['mdp']))
            {
                $erreur .= 'Erreur de mot de passe actuel<br>';
            }
        }
        
        // VERIFICATION NOUVEAU MOT DE PASSE
        // si le mot de passe est vide
        if(empty($_POST['mdp_nouveau']))
        {
            $erreur .= 'Veuillez renseigner votre nouveau mot de passe.<br>';
        }
        // si la longueur est moins de 5 caractères ou plus de 10 caractères
        if(strlen($_POST['mdp_nouveau']) < 5 || strlen($_POST['mdp_nouveau']) > 10)
        {
            $erreur .= 'Votre mot de passe doit contenir entre 5 et 10 caractères.<br>';
        }
        // limitation des caractères aux lettres et chiffres
        if(!preg_match("#^[a-zA-Z0-9]{5,10}$#", $_POST['mdp_nouveau']))
        {
            $erreur .= 'Votre mot de passe peut contenir des lettres et des chiffres.<br>';
        }

        // VERIFICATION NOUVEAU MOT DE PASSE 2
        // si le mot de passe est vide
        if(empty($_POST['mdp_nouveau_conf']))
        {
            $erreur .= 'Veuillez répéter votre mot de passe.<br>';
        }
        else // si le mot de passe ne correspond pas au premier
        {
            if($_POST['mdp_nouveau'] != $_POST['mdp_nouveau_conf'])
            {
                $erreur .= 'Votre confirmation de mot de passe n\'est pas identique.<br>';                                  
            }
        }
        if($erreur == "")
        {
            $mdp = htmlentities($_POST['mdp_nouveau']);
            $mdpHash = password_hash($mdp, PASSWORD_DEFAULT);
            $requeteUpdate = $bdd->prepare("UPDATE membre SET mdp = :mdp WHERE id_membre = :id_membre");
            $requeteUpdate->execute(array(
                'mdp' => $mdpHash,
                'id_membre' => $id_membre
            ));
            if($id_membre == $_SESSION['membre']['id_membre'])
            {
                if($requeteUpdate->rowCount() != 0)
                {
                    $_SESSION['confirmation']['message']['profil'] = 'Votre mot de passe a été mis à jour avec succès';
                }
                else
                {
                    $_SESSION['confirmation']['erreur']['profil'] = 'Votre mot de passe n\'a pas pu être mis à jour'; 
                }
                header('location:' . RACINE_SITE . 'profil.php');
                exit;
            }
            else
            {
                if($requeteUpdate->rowCount() != 0)
                {
                    $_SESSION['confirmation']['message']['profil'] = 'Le mot de passe a été mis à jour avec succès';
                }
                else
                {
                    $_SESSION['confirmation']['erreur']['profil'] = 'Le mot de passe n\'a pas pu être mis à jour'; 
                }
                header('location:' . RACINE_SITE . 'profil.php?id_membre=' . $id_membre);
                exit;
            }
        }
    }


    // MODIFIER SON AVIS - UNIQUEMENT MEMBRE CONNECTE AVEC SES IDENTIFIANTS
    
    if(isset($_POST['modifier_note']))
    {
        if($id_membre == $_SESSION['membre']['id_membre'])
        {
            if(isset($_GET['action']) && $_GET['action'] == 'modifier_note' && isset($_GET['id_avis']))
            {
                $requeteUpdate = $bdd->prepare("UPDATE avis SET note = :note WHERE id_membre = :id_membre AND id_avis = :id_avis");
                $requeteUpdate->execute(array(
                    'note' => $_POST['note'],
                    'id_membre' => $id_membre,
                    'id_avis' => $_GET['id_avis']
                ));
                if($requeteUpdate->rowCount() != 0)
                {
                    $_SESSION['confirmation']['message']['avis'] = 'Votre note a été mise à jour avec succès';
                }
                else
                {
                    $_SESSION['confirmation']['erreur']['avis'] = 'Votre note n\'a pas pu être mise à jour'; 
                }
                header('location:' . RACINE_SITE . 'profil.php#alert_avis');
                exit;
            }
        }
    }

    // MODIFIER SON AVIS - UNIQUEMENT MEMBRE CONNECTE AVEC SES IDENTIFIANTS
    if(isset($_POST['modifier_commentaire']))
    {
        if($id_membre == $_SESSION['membre']['id_membre'])
        {
            if(isset($_GET['action']) && $_GET['action'] == 'modifier_commentaire' && isset($_GET['id_avis']))
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
                if($erreurAvis == "")
                {
                    $requeteUpdate = $bdd->prepare("UPDATE avis SET commentaire = :commentaire WHERE id_membre = :id_membre AND id_avis = :id_avis");
                    $requeteUpdate->execute(array(
                        'commentaire' => $_POST['commentaire'],
                        'id_membre' => $id_membre,
                        'id_avis' => $_GET['id_avis']
                    ));

                    if($requeteUpdate->rowCount() != 0)
                    {
                        $_SESSION['confirmation']['message']['avis'] = 'Votre avis a été mis à jour avec succès';
                    }
                    else
                    {
                        $_SESSION['confirmation']['erreur']['avis'] = 'Votre avis n\'a pas pu être mis à jour'; 
                    }
                    header('location:' . RACINE_SITE . 'profil.php#alert_avis');
                    exit;
                }
            }
        }
    }
    
}

// SUPPRIMER UNE COMMANDE 
// VERIFIER SI COMMANDE EXISTE ET ID MEMBRE CORRESPOND
if(isset($_GET['action']) && $_GET['action'] == 'supprimer_commande')
{
    $requeteCommandeVerif = $bdd->prepare("SELECT commande.id_commande, commande.id_membre, commande.id_produit, produit.date_arrivee 
        FROM commande 
        INNER JOIN produit 
        ON commande.id_produit = produit.id_produit WHERE id_commande = :id_commande AND id_membre = :id_membre AND date_arrivee > now()");
    $requeteCommandeVerif->execute(array(
        'id_commande' => $_GET['id_commande'],
        'id_membre' => $id_membre
    ));

    if($requeteCommandeVerif->rowCount() == 0)
    {
        $erreurCommande .= 'Erreur de droits à la suppression de la commande';
    }
    else
    {
        // MISE A JOUR DE L'ETAT DE LA COMMANDE (recherche l'id produit)
        $requeteProduitCommande = $bdd->prepare("SELECT * FROM produit 
            INNER JOIN commande ON produit.id_produit = commande.id_produit 
            WHERE commande.id_commande = :id_commande");
        $requeteProduitCommande->execute(array(
            'id_commande' => $_GET['id_commande']
        ));
        $resultatProduitCommande = $requeteProduitCommande->fetch(PDO::FETCH_ASSOC);

        // suppression de la commande de la table commande
        $requeteCommandeSupp = $bdd->prepare("DELETE FROM commande USING
            commande INNER JOIN produit 
            ON commande.id_produit = produit.id_produit 
            WHERE id_commande = :id_commande AND id_membre = :id_membre AND date_arrivee > now()");
        $requeteCommandeSupp->execute(array(
            'id_commande' => $_GET['id_commande'],
            'id_membre' => $id_membre
        ));
        if($requeteCommandeSupp->rowCount() != 0)
        {
            // modifier l'état du produit
            $requeteProduitEtat = $bdd->prepare("UPDATE produit SET etat = 'libre' WHERE id_produit = :id_produit");
            $requeteProduitEtat->execute(array(
                'id_produit' => $resultatProduitCommande['id_produit']
            ));
        }

        if($id_membre == $_SESSION['membre']['id_membre'])
        {
            if($requeteCommandeSupp->rowCount() != 0)
            {
                $_SESSION['confirmation']['message']['commande'] = 'Votre commande a été supprimée avec succès';
            }
            else
            {
                $_SESSION['confirmation']['erreur']['commande'] = 'Votre commande n\'a pas pu être supprimée'; 
            }
            header('location:' . RACINE_SITE . 'profil.php#alert_commande');
            exit;
        }
        else
        {
            if($requeteCommandeSupp->rowCount() != 0)
            {
                $_SESSION['confirmation']['message']['commande'] = 'La commande a été supprimée avec succès';
            }
            else
            {
                $_SESSION['confirmation']['erreur']['commande'] = 'La commande n\'a pas pu être supprimée'; 
            }
            header('location:' . RACINE_SITE . 'profil.php?id_membre=' . $id_membre . '#alert_commande');
            exit;
        }
        $succesCommande = 'La commande a été supprimée';
    }
}

require_once("inc/haut.inc.php");
//echo $requeteCommandeVerif->rowCount();

if($erreur != "")
{
    echo '<div class="row"><div class="col-sm-12"><p class="alert alert-danger">' . $erreur . '</p></div></div>';
}

if(isset($_SESSION['confirmation']['message']['profil']))
{
    $contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-success">' . $_SESSION['confirmation']['message']['profil'] . '</p></div></div>';
}
if(isset($_SESSION['confirmation']['erreur']['profil']))
{
    $contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-danger">' . $_SESSION['confirmation']['erreur']['profil'] . '</p></div></div>';
}

// INFOS PROFIL
$contenu .= '<div class="row"><div class="col-sm-6"><h1>';
if($id_membre == $_SESSION['membre']['id_membre'])
{
    $contenu .= 'Votre profil';
}
else
{
    $contenu .= 'Profil';
}
$contenu .= '</h1>';
$contenu .= '<form method="post">';
$contenu .= '<div class="table-responsive"><table class="table text-left">';

 // NOM AFFICHAGE ET MODIFICATION
$contenu .= '<tr><th class="text-left">Nom : </th>';
if(isset($_GET['action']) && $_GET['action'] == 'modifier_nom')
{
    $contenu .= '<td><input type="text" name="nom" value="' . $resultatProfil['nom'] . '" class="form-control"></td>';
    $contenu .= '<td><input type="submit" class="btn btn-outline btn-primary" name="modifier_nom" value="Modifier"></td>';
}
else
{
    $contenu .= '<td>' . $resultatProfil['nom'] . '</td>';
    if($id_membre == $_SESSION['membre']['id_membre'])
    {
        $contenu .= '<td><a href="?action=modifier_nom"><i>Modifier</i></a></td>';
    }
    else
    {
        $contenu .= '<td><a href="?id_membre=' . htmlentities($id_membre) . '&action=modifier_nom"><i>Modifier</i></a></td>';
    }
    
}
$contenu .= '</tr>';

// PRENOM AFFICHAGE ET MODIFICATION
$contenu .= '<tr><th class="text-left">Prénom : </th>';
if(isset($_GET['action']) && $_GET['action'] == 'modifier_prenom')
{
    $contenu .= '<td><input type="text" name="prenom" value="' . $resultatProfil['prenom'] . '" class="form-control"></td>';
    $contenu .= '<td><input type="submit" name="modifier_prenom" class="btn btn-outline btn-primary" value="Modifier"></td>';
}
else
{
    $contenu .= '<td>' . $resultatProfil['prenom'] . '</td>';
    if($id_membre == $_SESSION['membre']['id_membre'])
    {
        $contenu .= '<td><a href="?action=modifier_prenom"><i>Modifier</i></a></td>';
    }
    else
    {
        $contenu .= '<td><a href="?id_membre=' . htmlentities($id_membre) . '&action=modifier_prenom"><i>Modifier</i></a></td>';
    }
}
$contenu .= '</tr>';

// CIVILITE AFFICHAGE ET MODIFICATION 
$contenu .= '<tr><th class="text-left">Civilité : </th>';

if(isset($_GET['action']) && $_GET['action'] == 'modifier_civilite')
{
    $contenu .= '<td><select name="civilite" class="form-control">';
    if($resultatProfil['civilite'] == 'f')
    {
        $contenu .= '<option name="civilite" value="f" selected>Femme</option>';
        $contenu .= '<option name="civilite" value="m">Homme</option>';
    }
    else
    {
        $contenu .= '<option name="civilite" value="m" selected>Homme</option>';
        $contenu .= '<option name="civilite" value="f">Femme</option>';
    }
    $contenu .= '</select>';

    $contenu .= '</td>';
    $contenu .= '<td><input type="submit" name="modifier_civilite" class="btn btn-outline btn-primary" value="Modifier"></td>';
}
else
{
    $contenu .= '<td>' . $civilite . '</td>';
    if($id_membre == $_SESSION['membre']['id_membre'])
    {
        $contenu .= '<td><a href="?action=modifier_civilite"><i>Modifier</i></a></td>';
    }
    else
    {
        $contenu .= '<td><a href="?id_membre=' . htmlentities($id_membre) . '&action=modifier_civilite"><i>Modifier</i></a></td>';
    }
}
$contenu .= '</tr>';

// PSEUDO AFFICHAGE ET MODIFICATION
$contenu .= '<tr><th class="text-left">Pseudo : </th>';
if(isset($_GET['action']) && $_GET['action'] == 'modifier_pseudo')
{
    $contenu .= '<td><input name="pseudo" type="text" value="' . $resultatProfil['pseudo'] . '" class="form-control"></td>';
    $contenu .= '<td><input type="submit" name="modifier_pseudo" class="btn btn-outline btn-primary" value="Modifier"></td>';
}
else
{
    $contenu .= '<td>' . $resultatProfil['pseudo'] . '</td>';
    if($id_membre == $_SESSION['membre']['id_membre'])
    {
        $contenu .= '<td><a href="?action=modifier_pseudo"><i>Modifier</i></a></td>';
    }
    else
    {
        $contenu .= '<td><a href="?id_membre=' . htmlentities($id_membre) . '&action=modifier_pseudo"><i>Modifier</i></a></td>';
    }
}
$contenu .= '</tr>';

// EMAIL AFFICHAGE ET MODIFICATION
$contenu .= '<tr><th class="text-left">Email : </th>';
if(isset($_GET['action']) && $_GET['action'] == 'modifier_email')
{
    $contenu .= '<td><input name="email" type="text" value="' . $resultatProfil['email'] . '" class="form-control"></td>';
    $contenu .= '<td><input type="submit" name="modifier_email" class="btn btn-outline btn-primary" value="Modifier"></td>';
}
else
{
    $contenu .= '<td>' . $resultatProfil['email'] . '</td>';
    if($id_membre == $_SESSION['membre']['id_membre'])
    {
        $contenu .= '<td><a href="?action=modifier_email"><i>Modifier</i></a></td>';
    }
    else
    {
        $contenu .= '<td><a href="?id_membre=' . htmlentities($id_membre) . '&action=modifier_email"><i>Modifier</i></a></td>';
    }
}
$contenu .= '</tr>';

// MOT DE PASSE AFFICHAGE ET MODIFICATION
if(isset($_GET['action']) && $_GET['action'] == 'modifier_mdp')
{
    $contenu .= '<tr>';
    $contenu .= '<th class="text-left">Ancien mot de passe : <br> <input type="password" class="form-control" name="mdp_ancien"></th>';
    $contenu .= '<th class="text-left">Nouveau mot de passe : <br> <input type="password" class="form-control" name="mdp_nouveau"> <br> <input type="password" class="form-control" name="mdp_nouveau_conf"></th>';
    $contenu .= '<td><input type="submit" name="modifier_mdp" class="btn btn-outline btn-primary" value="Modifier"></td>';
    $contenu .= '</tr>';
}
else
{
    $contenu .= '<tr><td colspan="3">';
    if($id_membre == $_SESSION['membre']['id_membre'])
    {
        $contenu .= '<a href="?action=modifier_mdp"><i>Modifier le mot de passe</i></a>';
    }
    else
    {
        $contenu .= '<a href="?id_membre=' . htmlentities($id_membre) . '&action=modifier_mdp"><i>Modifier le mot de passe</i></a>';
    }
    $contenu .= '</td>';
    $contenu .= '</tr>';
}

$contenu .= '</table></div>';
$contenu .= '</form>';
$contenu .= '</div>';

// BOUTON RETOUR A LA BOUTIQUE
$contenu .= '<div class="col-sm-offset-4 col-sm-2"><p><a href="' . RACINE_SITE . '" class="btn btn-outline btn-danger pull-right">Retour à l\'accueil</a></p></div>';

$contenu .= '</div>';

// HISTORIQUE COMMANDES

$contenu .= '<div class="row"><div class="col-sm-12">';
if($id_membre == $_SESSION['membre']['id_membre'])
{
    $contenu .= '<h1 id="commandes">Historique de vos commandes</h1>';
}
else
{
    $contenu .= '<h1 id="commandes">Historique des commandes</h1>';
}

$contenu .= '</div></div>';

// MESSAGES COMMANDE

if(isset($_SESSION['confirmation']['message']['commande']))
{
    $contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-success" id="alert_commande">' . $_SESSION['confirmation']['message']['commande'] . '</p></div></div>';
}
if(isset($_SESSION['confirmation']['erreur']['commande']))
{
    $contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-danger" id="alert_commande">' . $_SESSION['confirmation']['erreur']['commande'] . '</p></div></div>';
}

if($requete->rowCount() == 0)
{
    $contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-info">Vous trouverez ici les détails de vos commandes.</p></div></div>';
}

if($erreurCommande != "")
{
    $contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-danger">' . $erreurCommande . '</p></div></div>';
}

if(isset($succesCommande))
{
    $contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-success">' . $succesCommande . '</p></div></div>';
}


foreach($resultat as $key => $value)
{
	$contenu .= '
        <div class="row">
            <div class="col-sm-3">
                <a href="' . htmlentities($value['photo']) . '" class="fancybox" title="Salle ' . htmlentities($value['titre']) . '">
                    <img src="' . htmlentities($value['photo']) . '" class="img-responsive">
                </a>
            </div>
            <div class="col-sm-9">
                <h3 class="profil-h3">Salle ' . htmlentities($value['titre']) . '</h3>
                <h4><span class="glyphicon glyphicon-calendar"></span> ' . htmlentities($value['date_arrivee_fr']) . ' - ' . htmlentities($value['date_depart_fr']) . '</h4>
                <p>' . htmlentities($value['adresse']) . ', ' . htmlentities($value['cp']) . ', ' . htmlentities($value['ville']) . '</p>
                <a class="btn btn-outline btn-primary" href="' . RACINE_SITE . 'fiche_produit.php?id_produit=' . $value['id_produit'] . '">Voir la fiche produit <span class="glyphicon glyphicon-chevron-right"></span></a>';
                if($value['date_arrivee'] > date('Y-m-d H:i:s'))
                {
                    $contenu .= '<br><small><i><a href="?action=supprimer_commande&id_commande=' . $value['id_commande'] . '" OnClick="return(confirm(\'Êtes-vous sûr de vouloir supprimer la commande :\n\nSalle ' . $value['titre'] . ' du ' . $value['date_arrivee_fr'] . ' au ' . $value['date_depart_fr'] . '\nà ' . $value['adresse'] . ', ' . $value['cp'] . ', ' . $value['ville'] . ' ?\'))">Supprimer la commande</a></i></small>';
                }
    $contenu .= '            
            </div>
        </div>
        <div class="row"><div class="col-sm-12"><hr></div></div>';
}

// VOS AVIS POSTES
$contenu .= '<div class="row"><div class="col-sm-12">';
if($id_membre == $_SESSION['membre']['id_membre'])
{
    $contenu .= '<h1 id="avis">Vos avis postés</h1>';
}
else
{
    $contenu .= '<h1 id="avis">Avis postés</h1>';
}

$contenu .= '</div></div>';
$contenu .= '<div class="row"><div class="col-sm-12"><hr></div></div>';

if(isset($_SESSION['confirmation']['message']['avis']))
{
    $contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-success" id="alert_avis">' . $_SESSION['confirmation']['message']['avis'] . '</p></div></div>';
}
if(isset($_SESSION['confirmation']['erreur']['avis']))
{
    $contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-danger" id="alert_avis">' . $_SESSION['confirmation']['erreur']['avis'] . '</p></div></div>';
}

if($requeteAvis->rowCount() == 0)
{
    $contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-info">Vous trouverez ici vos avis postés.</p></div></div>';
}

//var_dump($resultatAvis);
if($erreurAvis != "")
{
    $contenu .= '<div class="row"><div class="col-sm-12"><p class="alert alert-danger">' . $erreurAvis . '</p></div></div>';
}

foreach($resultatAvis as $key => $value)
{
    //var_dump($value);
    $contenu .= '
        <div class="row">
            <div class="col-sm-3">
                <a href="' . htmlentities($value['photo']) . '" class="fancybox" title="Salle ' . htmlentities($value['titre']) . '">
                    <img src="' . htmlentities($value['photo']) . '" class="img-responsive">
                </a>
            </div>
            <div class="col-sm-9">
                <h3 class="profil-h3">Salle ' . htmlentities($value['titre']) . '</h3>
                <p>' . htmlentities($value['adresse']) . ', ' . htmlentities($value['cp']) . ', ' . htmlentities($value['ville']) . '</p>';
                if($id_membre == $_SESSION['membre']['id_membre'])
                {
                    $contenu .= '<h3>Votre note : ';
                }
                else
                {
                    $contenu .= '<h3>Note donnée : ';
                }
                // MODIFIER SON AVIS - PERSONNEL
                if($id_membre == $_SESSION['membre']['id_membre'])
                {
                    if(isset($_GET['action']) && $_GET['action'] == 'modifier_note' && isset($_GET['id_avis']) && $_GET['id_avis'] == $value['id_avis'])
                    {
                        $contenu .= '<form method="post" id="form_avis' . $value['id_avis'] . '" class="form-inline">';
                        $contenu .= '<select name="note" class="form-control">';
                        for($in = 1; $in <= 5; $in++)
                        {
                            if($in == $value['note'])
                            {
                                $contenu .= '<option name="note" value="' . $in . '" selected>' . $in . '</option>';
                            }
                            else
                            {
                                $contenu .= '<option name="note" value="' . $in . '">' . $in . '</option>';
                            }
                        }
                        $contenu .= '</select> ';
                        $contenu .= ' <input type="submit" class="btn btn-outline btn-primary" name="modifier_note" value="modifier">';
                        $contenu .= '</form>';
                    }
                    else
                    {
                        for($inote = 0; $inote < $value['note']; $inote++)
                        {
                            $contenu .= '<small><span class="glyphicon glyphicon-star"></span></small> ';
                        }
                        $contenu .= '<a href="?action=modifier_note&id_avis=' . $value['id_avis'] . '#form_avis' . $value['id_avis'] . '"><small><i>Modifier votre note</i></small></a>';
                    }
                }
                else
                {
                    for($inote = 0; $inote < $value['note']; $inote++)
                    {
                        $contenu .= '<small><span class="glyphicon glyphicon-star"></span></small> ';
                    }
                }
                
    $contenu .= '</h3>';
    $contenu .= '<p><i>Posté ' . $value['date_enreg_fr'] . ' </i></p>'; 
    $contenu .=   
            '</div>
        </div>';
    $contenu .= '<div class="row"><div class="col-sm-12">';
    $contenu .= '<h3 class="profil-h3">Commentaire :</h3>'; 
    if($id_membre == $_SESSION['membre']['id_membre'])
    {
        if(isset($_GET['action']) && $_GET['action'] == 'modifier_commentaire' && isset($_GET['id_avis']) && $_GET['id_avis'] == $value['id_avis'])
        {
            $contenu .= '<form method="post" id="form_com' . $value['id_avis'] . '">';
            $contenu .= '<textarea name="commentaire" class="form-control">' . htmlentities($value['commentaire']) . '</textarea><br>';
            $contenu .= '<input type="submit" name="modifier_commentaire" value="Modifier" class="btn btn-outline btn-primary">';
            $contenu .= '</form>';
        }
        else
        {
            $contenu .= '<p>' . htmlentities($value['commentaire']) . '</p>'; 
            $contenu .= '<a href="?action=modifier_commentaire&id_avis=' . $value['id_avis'] . '#form_com' . $value['id_avis'] . '"><i>Modifier votre commentaire</i></a>';
        }
    }
    else
    {
        $contenu .= '<p>' . htmlentities($value['commentaire']) . '</p>';
    }
    $contenu .= '</div></div>';

    $contenu .= '<div class="row"><div class="col-sm-12"><hr></div></div>';

}

echo $contenu;

// EFFACER LES MESSAGES
if(isset($_SESSION['confirmation']) && !isset($_GET['action']))
{
    unset($_SESSION['confirmation']);
}

require_once('inc/bas.inc.php');