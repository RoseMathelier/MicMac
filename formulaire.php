<!-- Ce script php permet de récupérer les données du chantier rentrés
par l'utilisateur dans le formulaire créé avec le script CreerChantier.php -->
<?php

//Récupération données images
include("UploadPictures.php");

//RECUPERATION ADRESSE DOSSIER, TAILLE (pour avoir la résolution), EXTENSION DE L'IMAGE
$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["myimage"]["name"]);
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
$imageSize = getimagesize($_FILES["myimage"]["name"]); //ajouter un champ dans le formulaire : résolution basse, moyenne ou haute

//On récupère le plus grand côté
if($imageSize[0] >= $imageSize[1]){
  $imageResolution = $imageSize[0];
}
else{
  $imageResolution = $imageSize[1];
}

//On initialise une variable pour vérifier l'upload au fur et à mesure
$uploadOk = UploadPicture();

//Si les images ont bien été uploadées, on rentre les paramètres du chantier dans la BDD et on génère les instructions micmac
if($uploadOk){

  //Connexion à la BDD
  $bdd = mysqli_connect("127.0.0.1", "root" ,"" ,"micmac");

  if (!$bdd) {
    echo "Erreur de connexion à la BDD.";
    exit;
  }

  //Récupération des données du formulaire
  $nom_chantier = mysqli_real_escape_string($bdd,$_POST["nom_chantier"]);
  $type_cam = mysqli_real_escape_string($bdd,$_POST["type_camera"]);

  //Récupération données de session
  $date = getdate();
  $datestring = $date["mday"]."/".$date["mon"]."/".$date["year"];
  $id_user = $_SESSION["id_user"];

  //Insertions des infos du chantier dans la BDD
  $requete_chantier = "INSERT INTO chantiers VALUES (NULL,".$nom_chantier.",".$datestring.",".$id_user.",".$type_cam.",".$imageResolution.",".$imageFileType.", 0, 0, 'en_attente', ".$adresse_dossier.")";
  $result_chantier = mysqli_query($bdd, $requete_chantier);

  if (!$result_chantier) {
    echo "Erreur : les informations du chantier n'ont pas pu être insérées dans la base de données.";
    exit;
  }
  $id_chantier = mysqli_query($bdd, "SELECT LAST_INSERT_ID()");

  //Insertions des instructions MicMac dans la BDD
  include("instructions.php");
  $nb_instr = setMicMacTable($id_chantier); //on récupère au passage le nombre d'instructions

  //Ajout du nombre d'étapes dans la BDD (définie grâce à setMicMac)
  $requete_update ="UPDATE chantier WHERE id = ".$id_chantier." SET nb_etapes = ".$nb_instr;

}

?>
