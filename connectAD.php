<?php
	include "AccesDonnees.php";

	$ip=explode(".",$_SERVER['SERVER_ADDR']);

	switch ($ip[0]) {

		case 127 :
		case '::1' :
		//local
			$host = "localhost";
			$user = "root";
			$password = "";
			$dbname = "gsb";
			$port='3306';
			break;
			
			
		default :
			exit ("Serveur non reconnu...");
			break;
	}

	
	
	$connexion=connexion($host,$port,$dbname,$user,$password);
	
	/*if ($connexion) {
		echo "Connexion reussie<br />";
		echo "Host : $host:$port<br />";
		echo "Base $dbname selectionnee... <br />";
		echo "Utilisateur : $user<br />";
		echo "Mode acces : $modeacces<br />";
		echo "Type base : $typebase<hr />";
	}
	*/
?>

