<?php
	ini_set('max_execution_time', 300);
	require_once '../include/core.inc.php';
	$host = "192.168.1.193";
	$database = "gestionale_{$_SESSION['quintana']}";
	$user = "user";
	$password = "furente";
	$link = connect_mysql_server($host, "information_schema");

	if(isset($_POST['operazione']) && $_POST['operazione']=='backup' && isset($_POST['data']) && !empty($_POST['data'])){
		$query_check_db = "SELECT * FROM SCHEMATA WHERE SCHEMA_NAME='{$database}'";
		if($res = mysqli_query($link, $query_check_db)){
			$row = mysqli_fetch_assoc($res);
			if (sizeof($row) == 0) {
				mysqli_query($link, "CREATE DATABASE IF NOT EXISTS ".$database) or die("#error#".mysqli_error($link));
				$server_db_link=connect_mysql_server($host, $database);
				$db_conn=connetti_mysql();
				foreach ($tbls as $tbl) {
					
					$crate_table="SHOW CREATE TABLE ".$tbl;
					if ($resp=mysqli_query($db_conn, $crate_table)) {
						while ($row1 = mysqli_fetch_assoc($resp)) {
							if(sizeof($row1)>0){
								$_sql=$row1["Create Table"];
								mysqli_query($server_db_link, $_sql) or die("#error#".mysqli_error($server_db_link));
							}
						}
						
					}
				}
				disconnetti_mysql($server_db_link);
				disconnetti_mysql($db_conn);
			} else {			
				foreach($tbls as $t) {
		            if(backup_data_tables($host, $database, $t)){
		            	echo "operazione avvenuta con successo!";
		            }
		        }

				

    

    
			
		}
		foreach($tbls as $t) {
            backup_data_tables($host, $database, $t);
        }
	}
	
	disconnetti_mysql($link);
}












function backup_data_tables($server_host, $server_db, $tbl_name){
    $db_locale=connetti_mysql();
    $aFields;
    $desc = mysqli_query($db_locale, "DESCRIBE " . $tbl_name) or die("#error#".mysqli_error($db_locale));
    while ($row = mysqli_fetch_assoc($desc)) {
        $aFields[]= array("field"=>$row["Field"], "type"=> $row["Type"]);
    }    

    $db_remoto=connect_mysql_server($server_host, $server_db);
    $tblquery = '';

    //loop through data
    $_data= " (";
    $_numItems = sizeof($aFields);
	$_i = 0;
    foreach ($aFields as $value) {
	    if(++$_i == $_numItems) {
	    	$_data .= $value["field"].")";
	  	}else{
	  		$_data .= $value["field"].',';
	  	}

    }

    $tblquery .= 'INSERT IGNORE INTO  '.$tbl_name.$_data. ' VALUES ';
    $result = mysqli_query($db_locale, "SELECT * FROM " . $tbl_name) or die("#error#".mysqli_error($db_locale));
    $numResults = mysqli_num_rows($result);
	$counter = 0;
	
    while ($row_ = mysqli_fetch_assoc($result)) {
		$numItems = sizeof($aFields);
		$i = 1;
        $tblquery .= '(';
        foreach ($aFields as $field) {
        	$val;
	        if (isset($row_[$field["field"]])) {
	        	$val=$row_[$field["field"]];
        	}else{
	        	$val= "null";
	        }
        	if ((strpos($field["type"], "int") !== false) || (strpos($field["type"], "decimal") !== false)) {
        		$val=$row_[$field["field"]];
        		continue;
        	} else {
        		$val='"'.$val.'"';
        	}
        	
            if($i == $numItems) {
		    	$tblquery .= $val;
		  	}else{
		  		$tblquery .= $val.',';
		  	}			  		       
        	$i++;
        }

        if (++$counter == $numResults) {
	        $tblquery .= '); '."\r\n";
	    } else {
	        $tblquery .= '),';
	    }
        
        
    }
	//mysqli_query($db_remoto, $tblquery) or die(mysqli_error($db_remoto));
	//echo $tblquery;
	mysqli_query($db_remoto, $tblquery) or die("#error#".mysqli_error($db_remoto));
    disconnetti_mysql($db_remoto);
    disconnetti_mysql($db_locale);
    return true;
}


?>

