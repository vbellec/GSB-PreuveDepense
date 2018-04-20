<?php
/**
 *  Biblioth�que de fonctions AccesDonnees.php
*
*
*
* @author Erwan
* @copyright Estran
* @version 4.8 Mardi 8 Novembre 2016
*
*
* Suite implementation Oracle
*  - TODO debug de la fonction typeChamp
*  - TODO faire la restaure
*
*/


///////////// CONFIGURATION DE L'ACCES AUX DONNEES ////////////////////

// nom du moteur d'acc�s � la base : mysql - mysqli - pdo
$modeacces = "pdo";

// type de la base de donnees : mysql - oracle
$typebase = "mysql";

// enregistrement des logs de connexion : true - false
$logcnx = FALSE;

// enregistrement des requetes SQL : none - all - modif
$logsql = "none";

//////////////////////////////////////////////////////////////////////

$mysql_data_type_hash = array(
		1=>'tinyint',
		2=>'smallint',
		3=>'int',
		4=>'float',
		5=>'double',
		7=>'timestamp',
		8=>'bigint',
		9=>'mediumint',
		10=>'date',
		11=>'time',
		12=>'datetime',
		13=>'year',
		16=>'bit',
		//252 is currently mapped to all text and blob types (MySQL 5.0.51a)
		252=>'blob',
		253=>'string',
		254=>'string',
		246=>'decimal'
);





/**
 *
 * Ouvre une connexion � un serveur MySQL ou ORACLE et s�lectionne une base de donn�es.
 * @param host string
 *  <p>Adresse du serveur MySQL.</p>
 * @param port integer
 *  <p>Num�ro du port du serveur MySQL.</p>
 * @param dbname string
 *  <p>Nom de la base de donnees.</p>
 * @param user string
 *  <p>Nom de l'utilisateur.</p>
 * @param password string
 *  <p>Mot de passe de l'utilisateur.</p>
 *
 * @return Retourne l'identifiant de connexion MySQL en cas de succ�s
 *         ou FALSE si une erreur survient.
 */
function connexion($host,$port,$dbname,$user,$password) {

	global $modeacces, $logcnx, $connexion, $typebase;



	/*  TEST CNX ORACLE
	 *
	 */
	if ($modeacces=="pdo") {

		if ($typebase=="mysql") {

			// ceation du Data Source Name, ou DSN, qui contient les infos
			// requises pour se connecter � un base de donnees MySQL en
			// utilisant un driver PDO.
			$dsn='mysql:host='.$host.';port='.$port.';dbname='.$dbname;
				
		}

		if ($typebase=="oracle") {
				
			// ceation du Data Source Name, ou DSN, qui contient les infos
			// requises pour se connecter � la base en PDO driver oracle.
			// exemple : oci:dbname=//10.100.22.20:1521/ora18sdis29
			$dsn='oci:dbname=//'.$host.':'.$port.'/'.$dbname;

		}
			
		try
		{
			$connexion = new PDO($dsn, $user, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
		}
			
		catch(Exception $e)
		{
			/*echo 'Erreur : '.$e->getMessage().'<br />';
			 echo 'N� : '.$e->getCode();
			 die();*/
			$chaine = "Connexion PB - ".date("j M Y - G:i:s - ").$user." - ". $e->getCode() . " - ". $e->getMessage()."\r\n";
			$connexion = FALSE;
		}
			
		if ($connexion) {
			$chaine = "Connexion OK - ".date("j M Y - G:i:s - ").$user."\r\n";
		}


	}


	if ($modeacces=="mysql") {
			
		@$link = mysql_connect("$host:$port", "$user", "$password");

		if (!$link) {
			$chaine = "Connexion PB - ".date("j M Y - G:i:s - ").$user." - ". mysql_error()."\r\n";
			$connexion = FALSE;
		} else {
			@$connexion = mysql_select_db("$dbname");
			if (!$connexion) {
				$chaine = "Selection base PB - ".date("j M Y - G:i:s - ").$user." - ". mysql_error()."\r\n";
				$connexion = FALSE;
			} else {
				$chaine = "Connexion OK - ".date("j M Y - G:i:s - ").$user."\r\n";
			}
		}

	}


	if ($modeacces=="mysqli") {

		@$connexion = new mysqli("$host", "$user", "$password", "$dbname", $port);

		if ($connexion->connect_error) {
			$chaine = "Connexion PB - ".date("j M Y - G:i:s - ").$user." - ". $connexion->connect_error."\r\n";
			$connexion = FALSE;
		} else {
			$chaine = "Connexion OK - ".date("j M Y - G:i:s - ").$user."\r\n";
		}

	}


	if ($logcnx) {
		$handle=fopen("log.txt","a");
		fwrite($handle,$chaine);
		fclose($handle);
	} else {
		//echo $chaine."<br />";
	}
	return $connexion;

}



/**
 *
 * Ferme la connexion MySQL.
 *
 */
function deconnexion() {

	global $modeacces, $connexion;

	if ($modeacces=="pdo") {
		$connexion=NULL;
	}

	if ($modeacces=="mysql") {
		mysql_close();
	}

	if ($modeacces=="mysqli") {
		$connexion->close();
	}

}



/**
 *
 *Envoie une requ�te � un serveur MySQL.
 * @param sql string
 *  <p>Requete SQL.</p>
 *
 *
 * @return  Pour les requ�tes du type SELECT, SHOW, DESCRIBE, EXPLAIN et
 *          les autres requ�tes retournant un jeu de r�sultats, mysql_query()
 *          retournera une ressource en cas de succ�s, ou FALSE en cas d'erreur.
 *
 *          Pour les autres types de requ�tes, INSERT, UPDATE, DELETE, DROP, etc.,
 *          mysql_query() retourne TRUE en cas de succ�s ou FALSE en cas d'erreur.
 */
function executeSQL($sql) {

	global $modeacces, $connexion, $logsql;

	$uneChaine = date("j M Y - G:i:s --> ").$sql."\r\n";

	if ($logsql=="all") {

		ecritRequeteSQL($uneChaine);

	} else {

		if ($logsql=="modif") {

			$mot=strtolower(substr($sql,0, 6));
			if ($mot=="insert" || $mot=="update") {
				ecritRequeteSQL($uneChaine);
			}

		}

	}

	if ($modeacces=="pdo") {
		$result = $connexion->query($sql)
		or die ( afficheErreur($sql,$connexion->errorInfo()[2]));
	}

	if ($modeacces=="mysql") {
		$result = mysql_query($sql)
		or die (afficheErreur($sql, mysql_error()));

	}

	if ($modeacces=="mysqli") {
		$result = $connexion->query($sql)
		//or die (afficheErreur($sql, mysqli_error_list($connexion)[0]['error']));
		or die (afficheErreur($sql, $connexion->error_list[0]['error']));


	}

	return $result;
}

function afficheErreur($sql, $erreur) {

	$uneChaine = "ERREUR SQL : ".date("j M Y - G:i:s.u --> ").$sql." : ($erreur) \r\n";

	ecritRequeteSQL($uneChaine);

	return "Erreur SQL de <b>".$_SERVER["SCRIPT_NAME"].
	"</b>.<br />Dans le fichier : ".__FILE__.
	" a la ligne : ".__LINE__.
	"<br />".$erreur.
	"<br /><br /><b>REQUETE SQL : </b>$sql<br />";

}

function ecritRequeteSQL($uneChaine) {
	$handle=fopen("requete.sql","a");
	fwrite($handle,$uneChaine);
	fclose($handle);
}



/**
 *
 *Retourne un tableau r�sultat d'une requete MySQL.
 * @param sql string
 *  <p>Requete SQL.</p>
 *
 *
 * @return un tableau r�sultat de la requete MySQL.
 *
 * exemple : 	$sql = "select * from user";

 $results = tableSQL($sql);

 foreach ($results as $ligne) {
 //on extrait chaque valeur de la ligne courante
 $login = $ligne['login'];
 $password = $ligne[3];

 echo $login." ".$password."<br />";
 }
 */
function tableSQL($sql) {

	global $modeacces, $connexion;

	$result = executeSQL($sql);
	$rows=array();

	if ($modeacces=="pdo") {
		//while ($row = $result->fetch(PDO::FETCH_BOTH)) {
		//	array_push($rows,$row);
		//}
		$rows = $result->fetchAll(PDO::FETCH_OBJ );
		//return $rows;
	}


	if ($modeacces=="mysql") {
		while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
			array_push($rows,$row);
		}
		//return $rows;
	}

	if ($modeacces=="mysqli") {
		//while ($row = $result->fetch_array(MYSQLI_BOTH)) {
		//	array_push($rows,$row);
		//}
		$rows = $result->fetch_all(MYSQLI_BOTH);
		//return $rows;
	}

	return $rows;
}



/**
 *
 *Retourne le nombre de lignes d'une requete MySQL.
 * @param sql string
 *  <p>Requete SQL.</p>
 *
 *
 * @return Le nombre de lignes dans un jeu de r�sultats en cas de succ�s
 *         ou FALSE si une erreur survient.
 */
function compteSQL($sql) {

	global $modeacces, $connexion, $typebase;

	if ($modeacces=="pdo") {

		if ($typebase=="mysql") {
			$repueteP=$connexion->prepare($sql);
			$repueteP->execute();
			$num_rows = $repueteP->rowCount();
		}

		if ($typebase=="oracle") {
			$recordset=$connexion->query($sql);
			$fields = $recordset->fetchAll(PDO::FETCH_ASSOC);
			$num_rows = sizeof($fields);
		}

	}

	if ($modeacces=="mysql") {
		$result = executeSQL($sql);
		$num_rows = mysql_num_rows($result);
	}

	if ($modeacces=="mysqli") {
		$result = executeSQL($sql);
		$num_rows = $connexion->affected_rows;
	}

	return $num_rows;
}



/**
 *
 *Retourne un seul champ r�sultat d'une requete MySQL.
 * @param sql string
 *  <p>Requete SQL.</p>
 *
 *
 * @return une chaine r�sultat de la requete MySQL.
 */
function champSQL($sql) {

	global $modeacces, $connexion;

	$result = executeSQL($sql);

	if ($modeacces=="pdo") {
		$rows = $result->fetch(PDO::FETCH_BOTH);
	}

	if ($modeacces=="mysql") {
		$rows = mysql_fetch_array($result, MYSQL_NUM);
	}

	if ($modeacces=="mysqli") {
		$rows = $result->fetch_array(MYSQLI_NUM);
	}

	return $rows[0];
}


/**
 *
 *Retourne un seule ligne r�sultat d'une requete MySQL.
 * @param sql string
 *  <p>Requete SQL.</p>
 *
 *
 * @return un tableau indexe et associatif representant  la ligne
 */
function ligneSQL($sql) {

	global $modeacces, $connexion;

	$result = executeSQL($sql);

	if ($modeacces=="pdo") {
		$rows = $result->fetch(PDO::FETCH_OBJ);
	}

	if ($modeacces=="mysql") {
		$rows = mysql_fetch_array($result, MYSQL_NUM);
	}

	if ($modeacces=="mysqli") {
		$rows = $result->fetch_array(MYSQLI_NUM);
	}

	return $rows;
}



/**
 *
 *Retourne le nombre de champs d'une requete MySQL
 * @param sql string
 *  <p>Requete SQL.</p>
 *
 *
 * @return Retourne le nombre de champs d'un jeu de r�sultat en cas de succ�s
 *         ou FALSE si une erreur survient.
 */
function nombreChamp($sql) {

	global $modeacces, $connexion;

	if ($modeacces=="pdo") {
		//utilisation d'une requete preparee
		$requeteP=$connexion->prepare($sql);
		$requeteP->execute();
		$num_rows = $requeteP->columnCount();
		return $num_rows;
	}

	if ($modeacces=="mysql") {
		$result = executeSQL($sql);
		return mysql_num_fields($result);
	}

	if ($modeacces=="mysqli") {
		$result = executeSQL($sql);
		return  $result->field_count;
	}

}



/**
 *
 *Retourne le type d'une colonne MySQL sp�cifique
 * @param sql string
 *  <p>Requete SQL.</p>
 * @param field_offset integer
 *  <p>La position num�rique du champ. field_offset commence � 0. Si field_offset
 *     n'existe pas, une alerte E_WARNING sera �galement g�n�r�e.</p>
 *
 *
 * @return Retourne le type du champ retourn� peut �tre : "int", "real", "string", "blob"
 *         ou d'autres, comme d�taill� � dans la documentation MySQL.
 * TODO revoir la valeur du type qui est renvoye
 *
 */
function typeChamp($sql, $field_offset) {

	global $modeacces, $connexion, $mysql_data_type_hash, $typebase;

	$result = executeSQL($sql);

	if ($modeacces=="pdo") {

		//recupere le  nom de la table
		$posfrom = strpos(strtoupper($sql), "FROM");
		$newsql = substr($sql, $posfrom+5, strlen($sql)-5-$posfrom);
		$nomtables = explode(',',$newsql);
		$nomtable = trim($nomtables[0]);
			
		if ($typebase=="pdo") {
			$recordset = $connexion->query("SHOW COLUMNS FROM $nomtable");
			$fields = $recordset->fetchAll(PDO::FETCH_ASSOC);
			$letype = ($fields[$field_offset]["Type"]);
		}
			
		if ($typebase=="oracle") {
			$nomDuChamp = nomChamp($sql,$field_offset);
			$sqlD =" SELECT data_type
			FROM user_tab_cols
			WHERE COLUMN_NAME='$nomDuChamp' AND TABLE_NAME='$nomtable'";
			$letype =  champSQL($sqlD);
		}
			
		// harmonisation entre les differents types des bases de donnees
		if (stristr($letype,'varchar')!=FALSE) {
			$letype="string";
		}
		if (stristr($letype,'int')!=FALSE) {
			$letype="int";
		}
		if (stristr($letype,'number')!=FALSE) {
			$letype="int";
		}
		if (stristr($letype,'date')!=FALSE) {
			$letype="date";
		}

		return $letype;
	}

	if ($modeacces=="mysql") {
		return mysql_field_type($result, $field_offset);
	}

	if ($modeacces=="mysqli") {
		return  $mysql_data_type_hash[$result->fetch_field_direct($field_offset)->type];
	}

}



/**
 *
 *Retourne le nom d'une colonne MySQL sp�cifique
 * @param sql string
 *  <p>Requete SQL.</p>
 * @param field_offset integer
 *  <p>La position num�rique du champ. field_offset commence � 0. Si field_offset
 *     n'existe pas, une alerte E_WARNING sera �galement g�n�r�e.</p>
 *
 *
 * @return Retourne le nom du champ d'une colonne sp�cifique
 *
 */
function nomChamp($sql, $field_offset) {

	global $modeacces, $connexion, $mysql_data_type_hash;

	/* getColumnMeta est EXPERIMENTALE. Cela signifie que le comportement de cette fonction, son nom et,
	 * concr�tement, TOUT ce qui est document� ici peut changer dans un futur proche, SANS PREAVIS !
	 * Soyez-en conscient, et utilisez cette fonction � vos risques et p�rils.
	 *
	 *    $select = $connexion->query($sql);
	 *	  $meta = $select->getColumnMeta($field_offset);
	 *	  return ($meta["name"]);
	 */

	if ($modeacces=="pdo") {
		$requeteP=$connexion->prepare($sql);
		$requeteP->execute();
		$fields = $requeteP->fetch(PDO::FETCH_BOTH);
		return (array_keys($fields)[$field_offset*2]);
	}

	if ($modeacces=="mysql") {
		$result = executeSQL($sql);
		return mysql_field_name($result, $field_offset);
	}

	if ($modeacces=="mysqli") {
		$result = executeSQL($sql);
		return  $mysql_data_type_hash[$result->fetch_field_direct($field_offset)->type];
	}

}



/**
 *
 *Retourne la version du serveur MySQL
 *
 *
 * @return Retourne une cha�ne de caract�res repr�sentant la version du serveur MySQL
 *         auquel l'extension  est connect�e (repr�sent� par le param�tre $connexion).
 */
function versionMYSQL() {

	global $modeacces, $connexion;

	if ($modeacces=="pdo") {
		return $connexion->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
	}

	if ($modeacces=="mysql") {
		return mysql_get_server_info();
	}

	if ($modeacces=="mysqli") {
		return   $connexion->server_info;
	}

}

function versionOracle() {

	///////////////////////
	///////////////////////
	//////////////////////
	// TODO EN PDO
	$conn = oci_connect('scott', 'tiger', '10.0.220.100:1521/ORAPROF');
	$version = oci_server_version($conn);
	oci_close($conn);

	return $version;
}

function versionBase() {

	global $typebase, $connexion;

	if ($typebase=="mysql") {

		return versionMYSQL();
			
	}

	if ($typebase=="oracle") {

		return versionOracle();
	}
		
}

/**
 *
 *Retourne le nom de la base courante
 *
 *
 * @return Retourne une cha�ne de caract�res repr�sentant le nom de la base de donn�es
 *         auquel l'extension  est connect�e (repr�sent� par le param�tre $connexion).
 */
function nomBase() {

	global $modeacces, $connexion;

	$sql = "SELECT DATABASE()";
	return champSQL($sql);
}



/**
 *
 *Affiche sous forme d'un tableau le r�sultat d'une requette SQL
 * @param sql string
 *  <p>Requete SQL.</p>
 *
 *
 */
function afficheRequeteSQL($sql) {

	$results = tableSQL($sql);

	$nbchamps = nombreChamp($sql);

	echo "<table border=1 >";
	echo "   <caption>$sql</caption>
	<tr>";
	for ($i=0;$i<$nbchamps;$i++) {
		echo "<th >".nomChamp($sql,$i)."</th>";
	}
	echo "   </tr>";

	foreach ($results as $ligne) {
		echo "<tr>";
		//on extrait chaque valeur de la ligne courante
		for ($i=0;$i<$nbchamps;$i++) {
			echo "<td>".$ligne[$i]."</td>";
		}
		echo "</tr>";
	}
	 
	echo "</table>";
}



/**
 *
 *Sauvegarde (DUMP) dans un fichier texte la base de donnees courante.
 * @param mode string
 *  <p>S pour la structure de la base de donnees
 *     D pour les donnees de la base
 *     A pour la structure et les donnees de la base</p>
 * @param repertoire string
 *  <p>Nom du repertoire dans lequel le fichier dump sera sauvagarde
 *     si NULL on sauvegarde dans le repertoire courant</p>
 * @param nomfichier string
 *  <p>Nom du fichier pour le dump (ajout de l'extension .slq)
 *     si NULL on utilise 'nomDeLaBase'.sql </p>
 *
 */
function export($mode,$repertoire,$nomfichier) {

	global $modeacces, $connexion;

	$dbname=nomBase();
	$jour = array("Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi");
	$mois = array("","Janvier","F�vrier","Mars","Avril","Mai","Juin","Juillet","Ao�t","Septembre","Octobre","Novembre","D�cembre");

	$entete  = "-- ----------------------\n";
	$entete .= "-- dump de la base ".$dbname." du ".$jour[date("w")]." ".date("d")." ".$mois[date("n")].date(" Y \� H:i:s")."\n";
	$entete .= "-- ----------------------\n\n\n";

	$creations ="";
	$insertions="\n\n";

	$sql = "show tables";
	$results = tableSQL($sql);


	foreach ($results as $ligne) {
		//on extrait chaque valeur de la ligne courante
		$table = $ligne[0];


		// structure de la table
		$creations .= "-- -----------------------------\n";
		$creations .= "-- Structure de la table ".$table."\n";
		$creations .= "-- -----------------------------\n";

		$sql1="show create table ".$table;
		$results1 = tableSQL($sql1);

		foreach ($results1 as $ligne1) {
			//on extrait chaque valeur de la ligne courante
			$requete = $ligne1[1];

			//on affiche la requete
			$creations .= $requete.";\n\n";
		}


		// donnees de la table
		$sql2 = "SELECT * FROM ".$table;
		$results2 = tableSQL($sql2);

		$insertions .= "-- -----------------------------\n";
		$insertions .= "-- Contenu de la table ".$table."\n";
		$insertions .= "-- -----------------------------\n";

		foreach ($results2 as $ligne2) {

			$nombredechamps = nombrechamp($sql2);

			$insertions .= "INSERT INTO ".$table." VALUES(";

			for($i=0; $i <$nombredechamps; $i++) {
					
				$letypeduchamp = typechamp($sql2,$i);
				$lavaleurduchamp =  $ligne2[$i];
					
				if($i != 0)
					$insertions .=  ", ";

					if($letypeduchamp == "string" || $letypeduchamp == "blob")
						$insertions .=  "'";
							
						$insertions .= addslashes($lavaleurduchamp);
							
						if($letypeduchamp == "string" ||$letypeduchamp == "blob")
							$insertions .=  "'";
			}

			$insertions .=  ");\n";

		}

		$insertions .= "\n";

	}


	if ($repertoire == NULL)
		$repertoire = ".";

		if ($nomfichier == NULL) {
			$info = date("dMy(H-i-s)");
			$nomfichier = $dbname."_".$info.".sql";
		}

		$nf =  $repertoire."/".$nomfichier;

		//echo "Nom fic : $nf <br />";

		$fichierDump = fopen($nf, "w");
		fwrite($fichierDump, $entete);
		//echo "Entete $dbname sauvegardee...<br />";
		if ($mode == 'S' or $mode == 'A') {
			fwrite($fichierDump, $creations);
			//echo "Structure de la base $dbname sauvegardee...<br />";
		}
		if ($mode == 'D' or $mode == 'A') {
			fwrite($fichierDump, $insertions);
			//echo "Donnees de la base $dbname sauvegardee...<br />";
		}
		fclose($fichierDump);

}



/**
 *
 *Exporte la structure et les donnees de la base de donnees courante � la racine
 *avec pour nom le nom de la base de donn�es.
 *
 */
function dump() {
	export("A",NULL,nomBase().".sql");
}



/**
 *
 *Exporte la structure de la base de donnees courante � la racine
 *avec pour nom le nom de la base de donn�es.
 *
 */
function exportBase() {
	export("S",NULL,nomBase().".sql");
}



/**
 *Sauvegarde dans le r�pertoire 'repertoire' les donn�es de la base.
 *le nom du fichier est g�r� en fonction de la date et de l'heure
 * @param repertoire string
 *  <p>Nom du repertoire dans lequel le fichier dump sera sauvagarde
 *     si NULL on sauvegarde dans le repertoire courant</p>
 *
 */
function sauvegarde($repertoire) {
	dump("D",$repertoire,NULL);
}



/**
 *
 *Restaure depuis un fichier texte la base de donnees courante.
 *
 ** @param repertoire string
 *  <p>Nom du repertoire dans lequel le fichier dump sera sauvagarde
 *     si NULL on sauvegarde dans le repertoire courant</p>
 */
function import(){

	global $modeacces, $connexion;

	///////////////////////
	///////////////////////
	//////////////////////
	// TODO
	echo "DEBUG TODO....<br />";

	//desactive les cle etrangere pour pouvoir effacer les tables de la base
	$sql = "SET FOREIGN_KEY_CHECKS = 0";
	echo "SQL : $sql<br/>";
	$result = executeSQL($sql);

	//quelques informations importantes
	$ipsrv=$_SERVER['SERVER_ADDR'];
	$versionPHP=getenv("SERVER_SOFTWARE");
	$namesrv=$_SERVER['SERVER_NAME'];
	$dbname=nomBase();

	//si la base existe on supprime les tables
	if ($connexion) {
		$sql = "SHOW tables";
		$results = tableSQL($sql);
		foreach ($results as $ligne) {
			//on extrait chaque valeur de la ligne courante
			$nomTable = $ligne[0];

			$sql = "DROP TABLE `$nomTable`";
			echo "SQL : $sql<br/>";
			$result = executeSQL($sql);
		}
	}  else {
		//sinon on cree la base
		$sql="CREATE DATABASE `$dbname` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
		echo "SQL : $sql<br/>";
		$result = executeSQL($sql);
	}

	//on utilise la base de donnees
	$sql="USE `$dbname`";
	echo "SQL : $sql<br/>";
	$result = executeSQL($sql);


	$versionMySQL=versionMYSQL();

	echo "Version : ".$versionMySQL;



	//restaure la base en fonction du fichier creer par le dump (V2)
	//Lit le fichier et renvoie le r�sultat dans un tableau
	$lines = file($dbname.".sql");
	$versionBase=$lines[1];

	$sql="";

	//execute toutes les requetes du fichier de dump
	for($i=0;$i<count($lines);$i++){
		$line=$lines[$i];
		if ($line[0]!='-' && $line[0]!='/' && strlen($line)>2) {
			$sql=$sql.$line;
			$test=strrev($line);
			$pospv=strpos($test,";");
			if ($pospv>0 && $pospv<5){
				echo "SQL : $sql<br/>";
				$result = executeSQL($sql);
				$sql="";
			}
		}
	}


	//reactive les cle etrangere la base
	$sql = "SET FOREIGN_KEY_CHECKS = 1";
	echo "SQL : $sql<br/>";
	$result = executeSQL($sql);

	$versionBase= substr($versionBase,23+strlen($dbname),strlen($versionBase)-23-strlen($dbname));
	$versionBase = str_replace("�","<br />",$versionBase);

	echo "<font color=green> Version base  $dbname <br /> $versionBase <br />$namesrv(IP : $ipsrv)<br />$versionPHP<br />MySQL : $versionMySQL</font>";


}





?>
