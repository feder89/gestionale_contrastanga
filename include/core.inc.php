<?php
	//fai partire la sessione
	session_start();

	function ottieni_data_serata_attuale(){
		$link = connetti_mysql();

		$query = "SELECT * FROM Serata WHERE inizializzata = 1";

		if(($res = mysqli_query($link, $query))){
			if(mysqli_num_rows($res)==1){
				$row = mysqli_fetch_assoc($res);
				return $row['data'];
			}
			else {
				return -1;
			}
		}
		else{
			return 0;
		}

		disconnetti_mysql($link);
	}

	/* e.g. login.php  */
	function curPageFile(){
		$file_name = basename($_SERVER["SCRIPT_FILENAME"]);
		return $file_name;
	}

	/* url completo di variabili get */
	function curPageURL() {
		$pageURL = 'http';
		if (@$_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}

	/* imposta la pagina precedente */
	function setPagePrevUrlSession(){
		if(curPageFile() != 'login.php' && curPageFile() != 'logout.php' ) $_SESSION['pagina_precedente']=curPageURL();
	}

	function nome_file(){
		$filename = basename($_SERVER['PHP_SELF']);
		if(isset($filename) && !empty($filename)) return $filename;
		else return 'index.php';
	}
	$page_name=nome_file();
	
	function connetti_mysql(){
		$link = mysqli_connect("127.0.0.1","root","","gestionale_{$_SESSION['quintana']}");

		// Check connection
		if (mysqli_connect_errno()){
		  	die("Failed to connect to MySQL: " . mysqli_connect_error());
		}

		if(!mysqli_set_charset ( $link , 'utf8' )){
			die("Unable to set utf8 charset: " . mysqli_error());
		}

		return $link;

	}

	function connetti_mysql_info_schema(){
		$link = mysqli_connect("127.0.0.1","root","","information_schema");

		// Check connection
		if (mysqli_connect_errno()){
		  	die("Failed to connect to MySQL: " . mysqli_connect_error());
		}

		if(!mysqli_set_charset ( $link , 'utf8' )){
			die("Unable to set utf8 charset: " . mysqli_error());
		}

		return $link;

	}

	function connect_mysql_server($ip_address, $db_name){
		$link = mysqli_connect($ip_address, "root", "", $db_name);

		if (mysqli_connect_errno()){
		  	die("Failed to connect to MySQL: " . mysqli_connect_error());
		}

		if(!mysqli_set_charset ( $link , 'utf8' )){
			die("Unable to set utf8 charset: " . mysqli_error());
		}

		return $link;
	}
	
	function esegui_query($link, $query) {
		if (($result = mysqli_query($link, $query))) return $result;
		else return 0;
	}

	function disconnetti_mysql($link,$res = NULL){
		if(isset($res) && !empty($res)) mysqli_free_result ( $res );
		mysqli_close ( $link );
	}

	function is_logged(){
	    if(!isset($_SESSION['username']) || !isset($_SESSION['gruppo'])){
	        return false;
	    } else {
	    	return true;
	    }
	}

	function login($username, $password, $quintana, $remember, $cookie=false){
        //$quintana = mysqli_real_escape_string($link, $quintana);
        $_SESSION['quintana']=$quintana;
		$link = connetti_mysql();
		$username = mysqli_real_escape_string($link, $username);
		$password = mysqli_real_escape_string($link, $password);
		//se la password arriva da cookie, è in md5
		$query = "SELECT * FROM Utente WHERE username='${username}' AND password='".( $cookie ? $password : md5($password))."'";
		$res = esegui_query($link, $query);
		$logged=false;
		if($res && mysqli_num_rows($res)==1 && ($row = mysqli_fetch_assoc($res))){
			$_SESSION['username']=$row['username'];
			$_SESSION['gruppo']=$row['gruppo'];
            
			if($remember && !$cookie){
				//ricordati dell'utente, imposta coockie
				setcookie('username', $_SESSION['username'], time() + (86400 * 365)); /* 1 year coockie */
				setcookie('password', md5($password), time() + (86400 * 365)); /* salviamo la password in md5 */
				setcookie('quintana', $_SESSION['quintana'], time() + (86400 * 60));
			}
			$logged=true;
		}
		disconnetti_mysql($link);
		return $logged;
	}


	/**
 * @function    restoreDatabaseTables
 * @author      CodexWorld
 * @link        http://www.codexworld.com
 * @usage       Restore database tables from a SQL file
 */
function restoreDatabaseTables($dbHost, $dbUsername, $dbPassword, $dbName, $filePath, $dbconn){
    // Connect & select the database
    $db = connect_mysql_server($dbHost, $dbconn); 

    // Temporary variable, used to store current query
    $templine = '';
    
    // Read in entire file
    $lines = file($filePath);
    $sql_ = str_replace("gestionale_", $dbName, $lines);
    
    $error = '';
    
    // Loop through each line
    foreach ($sql_ as $line){
        // Skip it if it's a comment
        if(substr($line, 0, 2) == '--' || $line == ''){
            continue;
        }
        
        // Add this line to the current segment
        $templine .= $line;
        
        // If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1, 1) == ';'){
            // Perform the query
            if(!$db->query($templine)){
                $error .= 'Error performing query "<b>' . $templine . '</b>": ' . $db->error . '<br /><br />';
            }
            
            // Reset temp variable to empty
            $templine = '';
        }
    }
    disconnetti_mysql($db);
    return !empty($error)?$error:true;
}

function begnWith($str, $begnString) {
      $len = strlen($begnString);
      return (substr($str, 0, $len) === $begnString);
}	

$tbls=[
	"Serata",
	"Portata",
	"Menu",
	"Menuserata",
	"Responsabili",
	"Zone",
	"Tavoli",
	"Responsabiliserata",
	"QuantitàPiattiSerata",
	"Composizionemenu",
	"Comande",
	"Ordini",
	"Prenotazioni",
	"Utente",
	"Ricevutefiscali",
	"Materieprime",
	"Composizioneportate"
];

function execute_multiline_query($sql_, $db){
	$error = '';
	$templine='';
	foreach ($sql_ as $line){
        // Skip it if it's a comment
        if(substr($line, 0, 2) == '--' || $line == ''){
            continue;
        }
        
        // Add this line to the current segment
        $templine .= $line;
        
        // If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1, 1) == ';'){
            // Perform the query
            if(!$db->query($templine)){
                $error .= 'Error performing query "<b>' . $templine . '</b>": ' . $db->error . '<br /><br />';
            }
            
            // Reset temp variable to empty
            $templine = '';
        }
    }
    return !empty($error)?$error:true;
}

	
?>