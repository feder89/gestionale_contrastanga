
<!DOCTYPE html>
<html>
	<body>
		<?php
			require_once '../include/core.inc.php';
			$link = connect_mysql_server("192.168.1.193", "information_schema");
			$query_check_db = "SELECT * FROM SCHEMATA WHERE SCHEMA_NAME=gestionale_{$_SESSION['quintana']}";

		    echo($query_check_db);
		    disconnetti_mysql($link);

?>
<!DOCTYPE html>
		
	</body>

</html>

