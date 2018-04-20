<?php
include('../connectAD.php');

$intitule = $_POST['intitule'];
$date = $_POST['date'];
$depense = $_POST['depense'];
$description = ($_POST['description'] != "") ? $_POST['description'] : null;
if ($_FILES['photo'])
{

	$extensionAuth = array(".jpg", ".png", ".jpeg");
	$extension = strchr($_FILES['photo']['name'], ".");

	if (!in_array($extension, $extensionAuth))
	{
		echo "Extension de fichier incorrect";	
	}

	$nomPhoto = $_FILES['photo']['name'];
	$destination = "../img/preuve/".$nomPhoto;

	$resultat = move_uploaded_file($_FILES['photo']['tmp_name'], $destination);
}   


$sql = "INSERT INTO `preuve` (`intitule`, `date`, `prix`, `photo`, `description`) 
            VALUES (\"$intitule\", '$date', '$depense', '$destination', '$description');";

$result = executeSQL($sql);

if ($result) 
{
    header("Refresh:0; url= /");
}
else {
    header("Refresh:0; url= /");
}