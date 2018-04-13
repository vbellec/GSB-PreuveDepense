<?php
include('../connectAD.php');

$intitule = $_POST['intitule'];
$date = $_POST['date'];
$depense = $_POST['depense'];
$description = ($_POST['description'] != "") ? $_POST['description'] : null;
echo "fkjsqdfkljf";
if ($_FILES['photo'])
{
	//Taille max du fichier
	$maxsize = 204800;
	$taille = $_FILES['photo']['size'];

	$extensionAuth = array(".jpg", ".png", ".jpeg");
	$extension = strchr($_FILES['photo']['name'], ".");

	if ($taille > $maxsize)
	{
		echo "Fichier trop volumineux";
	}

	if (!in_array($extension, $extensionAuth))
	{
		echo "Extension de fichier incorrect";	
	}

	$nomPhoto = $_FILES['photo']['name'];
	$destination = "/img/preuve/".$nomPhoto;

	$resultat = move_uploaded_file($_FILES['photo']['tmp_name'], $destination);
}   


$sql = "INSERT INTO `preuve` (`intitule`, `date`, `prix`, `photo`, `description`) 
            VALUES ('$intitule', '$date', '$depense', '$destination', '$description');";

$result = executeSQL($sql);

if ($result) 
{
    header("Refresh:0; url= /");
}
else {
    header("Refresh:0; url= /");
}












function uploadPhoto($photo)
{
    
}