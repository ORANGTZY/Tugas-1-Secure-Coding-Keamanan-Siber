[33mcommit b0efc832c90b42a672176284f09aa4133a292953[m[33m ([m[1;36mHEAD[m[33m -> [m[1;32mmitigasi-alternatif-sqli[m[33m)[m
Author: unknown <felixvalentino58@gmail.com>
Date:   Sat Oct 17 12:06:57 2026 +0700

    Implementasi mitigasi alternatif SQLi dengan Regex dan MySQLi Prepared Statement

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex 8821795..7f9ef74 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -6,18 +6,29 @@[m [mif( isset( $_REQUEST[ 'Submit' ] ) ) {[m
 [m
 	switch ($_DVWA['SQLI_DB']) {[m
 		case MYSQL:[m
[31m-			// Check database[m
[31m-			$query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";[m
[31m-			$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );[m
[32m+[m			[32m// Validasi input ketat dengan Regex Whitelisting (hanya digit angka positif)[m[41m[m
[32m+[m			[32mif( preg_match( '/^[0-9]+$/', (string)$id ) ) {[m[41m[m
[32m+[m				[32m$id = (int)$id;[m[41m[m
[32m+[m[41m[m
[32m+[m				[32m// Check database menggunakan MySQLi Prepared Statement[m[41m[m
[32m+[m				[32m$stmt = mysqli_prepare( $GLOBALS["___mysqli_ston"], "SELECT first_name, last_name FROM users WHERE user_id = ?;" );[m[41m[m
[32m+[m				[32mmysqli_stmt_bind_param( $stmt, "i", $id );[m[41m[m
[32m+[m				[32mmysqli_stmt_execute( $stmt );[m[41m[m
[32m+[m				[32m$result = mysqli_stmt_get_result( $stmt );[m[41m[m
[32m+[m[41m[m
[32m+[m				[32m// Get results[m[41m[m
[32m+[m				[32mwhile( $row = mysqli_fetch_assoc( $result ) ) {[m[41m[m
[32m+[m					[32m// Get values[m[41m[m
[32m+[m					[32m$first = $row["first_name"];[m[41m[m
[32m+[m					[32m$last  = $row["last_name"];[m[41m[m
 [m
[31m-			// Get results[m
[31m-			while( $row = mysqli_fetch_assoc( $result ) ) {[m
[31m-				// Get values[m
[31m-				$first = $row["first_name"];[m
[31m-				$last  = $row["last_name"];[m
[32m+[m					[32m// Feedback for end user[m[41m[m
[32m+[m					[32m$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";[m[41m[m
[32m+[m				[32m}[m[41m[m
 [m
[31m-				// Feedback for end user[m
[31m-				$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";[m
[32m+[m				[32mmysqli_stmt_close( $stmt );[m[41m[m
[32m+[m			[32m} else {[m[41m[m
[32m+[m				[32m$html .= "<pre>Input ditolak: User ID harus berupa angka bulat positif.</pre>";[m[41m[m
 			}[m
 [m
 			mysqli_close($GLOBALS["___mysqli_ston"]);[m
[36m@@ -53,4 +64,4 @@[m [mif( isset( $_REQUEST[ 'Submit' ] ) ) {[m
 	} [m
 }[m
 [m
[31m-?>[m
[32m+[m[32m?>[m
\ No newline at end of file[m

[33mcommit 98904eaca9ca53f4eb05f6dc73773f4f7e5abdea[m
Author: Robin Wood <robin@digi.ninja>
Date:   Thu Aug 12 08:41:17 2021 +0100

    better config

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex ea65838..8821795 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -4,13 +4,7 @@[m [mif( isset( $_REQUEST[ 'Submit' ] ) ) {[m
 	// Get input[m
 	$id = $_REQUEST[ 'id' ];[m
 [m
[31m-	define ("MYSQL", "mysql");[m
[31m-	define ("SQLITE", "sqlite");[m
[31m-[m
[31m-	define ("SQLI_DB", SQLITE);[m
[31m-	//define ("SQLI_DB", MYSQL);[m
[31m-[m
[31m-	switch (SQLI_DB) {[m
[32m+[m	[32mswitch ($_DVWA['SQLI_DB']) {[m[41m[m
 		case MYSQL:[m
 			// Check database[m
 			$query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";[m
[36m@@ -29,17 +23,31 @@[m [mif( isset( $_REQUEST[ 'Submit' ] ) ) {[m
 			mysqli_close($GLOBALS["___mysqli_ston"]);[m
 			break;[m
 		case SQLITE:[m
[31m-			$sqlite_db = "/var/sites/dvwa/non-secure/htdocs/vulnerabilities/sqli/source/sqli.db";[m
[31m-			$sqlite_db_connection = new SQLite3($sqlite_db);[m
[32m+[m			[32mglobal $sqlite_db_connection;[m[41m[m
[32m+[m[41m[m
[32m+[m			[32m#$sqlite_db_connection = new SQLite3($_DVWA['SQLITE_DB']);[m[41m[m
[32m+[m			[32m#$sqlite_db_connection->enableExceptions(true);[m[41m[m
[32m+[m[41m[m
 			$query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";[m
[31m-			$results = $sqlite_db_connection->query($query);[m
[31m-			while ($row = $results->fetchArray()) {[m
[31m-				// Get values[m
[31m-				$first = $row["first_name"];[m
[31m-				$last  = $row["last_name"];[m
[32m+[m			[32m#print $query;[m[41m[m
[32m+[m			[32mtry {[m[41m[m
[32m+[m				[32m$results = $sqlite_db_connection->query($query);[m[41m[m
[32m+[m			[32m} catch (Exception $e) {[m[41m[m
[32m+[m				[32mecho 'Caught exception: ' . $e->getMessage();[m[41m[m
[32m+[m				[32mexit();[m[41m[m
[32m+[m			[32m}[m[41m[m
 [m
[31m-				// Feedback for end user[m
[31m-				$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";[m
[32m+[m			[32mif ($results) {[m[41m[m
[32m+[m				[32mwhile ($row = $results->fetchArray()) {[m[41m[m
[32m+[m					[32m// Get values[m[41m[m
[32m+[m					[32m$first = $row["first_name"];[m[41m[m
[32m+[m					[32m$last  = $row["last_name"];[m[41m[m
[32m+[m[41m[m
[32m+[m					[32m// Feedback for end user[m[41m[m
[32m+[m					[32m$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";[m[41m[m
[32m+[m				[32m}[m[41m[m
[32m+[m			[32m} else {[m[41m[m
[32m+[m				[32mecho "Error in fetch ".$sqlite_db->lastErrorMsg();[m[41m[m
 			}[m
 			break;[m
 	} [m

[33mcommit cabfbc478fe89c0d1db33a2e86b947afab80c197[m
Author: Robin Wood <robin@digi.ninja>
Date:   Wed Aug 11 22:32:03 2021 +0100

    seeing if sqli in sqlite3 will work

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex 6b7d364..ea65838 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -4,21 +4,45 @@[m [mif( isset( $_REQUEST[ 'Submit' ] ) ) {[m
 	// Get input[m
 	$id = $_REQUEST[ 'id' ];[m
 [m
[31m-	// Check database[m
[31m-	$query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";[m
[31m-	$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );[m
[32m+[m	[32mdefine ("MYSQL", "mysql");[m[41m[m
[32m+[m	[32mdefine ("SQLITE", "sqlite");[m[41m[m
 [m
[31m-	// Get results[m
[31m-	while( $row = mysqli_fetch_assoc( $result ) ) {[m
[31m-		// Get values[m
[31m-		$first = $row["first_name"];[m
[31m-		$last  = $row["last_name"];[m
[32m+[m	[32mdefine ("SQLI_DB", SQLITE);[m[41m[m
[32m+[m	[32m//define ("SQLI_DB", MYSQL);[m[41m[m
 [m
[31m-		// Feedback for end user[m
[31m-		$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";[m
[31m-	}[m
[32m+[m	[32mswitch (SQLI_DB) {[m[41m[m
[32m+[m		[32mcase MYSQL:[m[41m[m
[32m+[m			[32m// Check database[m[41m[m
[32m+[m			[32m$query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";[m[41m[m
[32m+[m			[32m$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );[m[41m[m
 [m
[31m-	mysqli_close($GLOBALS["___mysqli_ston"]);[m
[32m+[m			[32m// Get results[m[41m[m
[32m+[m			[32mwhile( $row = mysqli_fetch_assoc( $result ) ) {[m[41m[m
[32m+[m				[32m// Get values[m[41m[m
[32m+[m				[32m$first = $row["first_name"];[m[41m[m
[32m+[m				[32m$last  = $row["last_name"];[m[41m[m
[32m+[m[41m[m
[32m+[m				[32m// Feedback for end user[m[41m[m
[32m+[m				[32m$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";[m[41m[m
[32m+[m			[32m}[m[41m[m
[32m+[m[41m[m
[32m+[m			[32mmysqli_close($GLOBALS["___mysqli_ston"]);[m[41m[m
[32m+[m			[32mbreak;[m[41m[m
[32m+[m		[32mcase SQLITE:[m[41m[m
[32m+[m			[32m$sqlite_db = "/var/sites/dvwa/non-secure/htdocs/vulnerabilities/sqli/source/sqli.db";[m[41m[m
[32m+[m			[32m$sqlite_db_connection = new SQLite3($sqlite_db);[m[41m[m
[32m+[m			[32m$query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";[m[41m[m
[32m+[m			[32m$results = $sqlite_db_connection->query($query);[m[41m[m
[32m+[m			[32mwhile ($row = $results->fetchArray()) {[m[41m[m
[32m+[m				[32m// Get values[m[41m[m
[32m+[m				[32m$first = $row["first_name"];[m[41m[m
[32m+[m				[32m$last  = $row["last_name"];[m[41m[m
[32m+[m[41m[m
[32m+[m				[32m// Feedback for end user[m[41m[m
[32m+[m				[32m$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";[m[41m[m
[32m+[m			[32m}[m[41m[m
[32m+[m			[32mbreak;[m[41m[m
[32m+[m	[32m}[m[41m [m
 }[m
 [m
 ?>[m

[33mcommit 965c92b63e7caef953a503b1887be75420e4c768[m
Author: Robin Wood <robin@digi.ninja>
Date:   Mon Apr 17 22:37:22 2017 +0100

    Fixed my mess up of the sqli vuln

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex 9503ece..6b7d364 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -6,7 +6,7 @@[m [mif( isset( $_REQUEST[ 'Submit' ] ) ) {[m
 [m
 	// Check database[m
 	$query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";[m
[31m-	$result = mysqli_query($query ) or die( '<pre>' . mysqli_connect_error() . '</pre>' );[m
[32m+[m	[32m$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );[m[41m[m
 [m
 	// Get results[m
 	while( $row = mysqli_fetch_assoc( $result ) ) {[m
[36m@@ -18,7 +18,7 @@[m [mif( isset( $_REQUEST[ 'Submit' ] ) ) {[m
 		$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";[m
 	}[m
 [m
[31m-	mysqli_close();[m
[32m+[m	[32mmysqli_close($GLOBALS["___mysqli_ston"]);[m[41m[m
 }[m
 [m
 ?>[m

[33mcommit 80f682e4afdaea07d9aa4465d74e4061bc20dbfd[m
Author: Robin Wood <robin@digi.ninja>
Date:   Wed Apr 12 04:30:04 2017 +0100

    tidied up the sqli source

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex 7ac78e7..9503ece 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -6,7 +6,7 @@[m [mif( isset( $_REQUEST[ 'Submit' ] ) ) {[m
 [m
 	// Check database[m
 	$query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";[m
[31m-	$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );[m
[32m+[m	[32m$result = mysqli_query($query ) or die( '<pre>' . mysqli_connect_error() . '</pre>' );[m[41m[m
 [m
 	// Get results[m
 	while( $row = mysqli_fetch_assoc( $result ) ) {[m
[36m@@ -18,7 +18,7 @@[m [mif( isset( $_REQUEST[ 'Submit' ] ) ) {[m
 		$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";[m
 	}[m
 [m
[31m-	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);[m
[32m+[m	[32mmysqli_close();[m[41m[m
 }[m
 [m
 ?>[m

[33mcommit 45eb7da35a8f12f43f9fe5903d30cc4c3fdf60c6[m
Author: Nick Armstrong <nick@nickarmstrong.id.au>
Date:   Wed Aug 24 18:45:08 2016 +1000

    Update SQL Injection pages to use MySQLi

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex f6a522d..7ac78e7 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -9,18 +9,13 @@[m [mif( isset( $_REQUEST[ 'Submit' ] ) ) {[m
 	$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );[m
 [m
 	// Get results[m
[31m-	$num = mysqli_num_rows( $result );[m
[31m-	$i   = 0;[m
[31m-	while( $i < $num ) {[m
[32m+[m	[32mwhile( $row = mysqli_fetch_assoc( $result ) ) {[m[41m[m
 		// Get values[m
[31m-		$first = mysql_result( $result, $i, "first_name" );[m
[31m-		$last  = mysql_result( $result, $i, "last_name" );[m
[32m+[m		[32m$first = $row["first_name"];[m[41m[m
[32m+[m		[32m$last  = $row["last_name"];[m[41m[m
 [m
 		// Feedback for end user[m
 		$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";[m
[31m-[m
[31m-		// Increase loop count[m
[31m-		$i++;[m
 	}[m
 [m
 	((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);[m

[33mcommit d185522a990df19485233b1351b8c09fe5b30daf[m
Author: Diego Blanco <diego.blanco@treitos.com>
Date:   Tue Feb 23 16:53:01 2016 +0100

    Migration to mysqli

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex 4094cec..f6a522d 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -6,10 +6,10 @@[m [mif( isset( $_REQUEST[ 'Submit' ] ) ) {[m
 [m
 	// Check database[m
 	$query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";[m
[31m-	$result = mysql_query( $query ) or die( '<pre>' . mysql_error() . '</pre>' );[m
[32m+[m	[32m$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );[m[41m[m
 [m
 	// Get results[m
[31m-	$num = mysql_numrows( $result );[m
[32m+[m	[32m$num = mysqli_num_rows( $result );[m[41m[m
 	$i   = 0;[m
 	while( $i < $num ) {[m
 		// Get values[m
[36m@@ -23,7 +23,7 @@[m [mif( isset( $_REQUEST[ 'Submit' ] ) ) {[m
 		$i++;[m
 	}[m
 [m
[31m-	mysql_close();[m
[32m+[m	[32m((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);[m[41m[m
 }[m
 [m
 ?>[m

[33mcommit 55d393a5f469b49ae1a6cb6ec1cc6e3b82faa251[m
Author: g0tmi1k <have.you.g0tmi1k@gmail.com>
Date:   Tue Sep 29 13:48:50 2015 +0100

    Clean up - spacing issues

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex 77ff575..4094cec 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -1,6 +1,6 @@[m
 <?php[m
 [m
[31m-if( isset( $_REQUEST[ 'Submit' ]  ) ) {[m
[32m+[m[32mif( isset( $_REQUEST[ 'Submit' ] ) ) {[m[41m[m
 	// Get input[m
 	$id = $_REQUEST[ 'id' ];[m
 [m

[33mcommit d41ae43d18aa0eb128ca324f8ebd4e2a9ff210ae[m
Author: g0tmi1k <have.you.g0tmi1k@gmail.com>
Date:   Sun Sep 27 19:15:44 2015 +0100

    Add comments to visible source code

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex 2fb6eec..77ff575 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -1,24 +1,29 @@[m
 <?php[m
 [m
[31m-if( isset( $_REQUEST[ 'Submit' ] ) ) {[m
[31m-	// Retrieve data[m
[32m+[m[32mif( isset( $_REQUEST[ 'Submit' ]  ) ) {[m[41m[m
[32m+[m	[32m// Get input[m[41m[m
 	$id = $_REQUEST[ 'id' ];[m
 [m
[31m-	$query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id'";[m
[32m+[m	[32m// Check database[m[41m[m
[32m+[m	[32m$query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";[m[41m[m
 	$result = mysql_query( $query ) or die( '<pre>' . mysql_error() . '</pre>' );[m
 [m
[32m+[m	[32m// Get results[m[41m[m
 	$num = mysql_numrows( $result );[m
 	$i   = 0;[m
 	while( $i < $num ) {[m
[32m+[m		[32m// Get values[m[41m[m
 		$first = mysql_result( $result, $i, "first_name" );[m
 		$last  = mysql_result( $result, $i, "last_name" );[m
 [m
[31m-		$html .= "<pre>";[m
[31m-		$html .= "ID: {$id}<br />First name: {$first}<br />Surname: {$last}";[m
[31m-		$html .= "</pre>";[m
[32m+[m		[32m// Feedback for end user[m[41m[m
[32m+[m		[32m$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";[m[41m[m
 [m
[32m+[m		[32m// Increase loop count[m[41m[m
 		$i++;[m
 	}[m
[32m+[m[41m[m
[32m+[m	[32mmysql_close();[m[41m[m
 }[m
 [m
 ?>[m

[33mcommit 6ad2b89f25d110df36cbcc6203b4c0e5dfe13a58[m
Author: g0tmi1k <have.you.g0tmi1k@gmail.com>
Date:   Fri Sep 18 10:34:58 2015 +0100

    Changed CSRF Medium HTTP REFERER & title format. Fix Impossible CSRF bug
    
    ..and cleanup code.

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex 6bffaa8..2fb6eec 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -1,14 +1,14 @@[m
 <?php[m
 [m
[31m-if( isset( $_GET[ 'Submit' ] ) ) {[m
[32m+[m[32mif( isset( $_REQUEST[ 'Submit' ] ) ) {[m[41m[m
 	// Retrieve data[m
[31m-	$id = $_GET[ 'id' ];[m
[32m+[m	[32m$id = $_REQUEST[ 'id' ];[m[41m[m
 [m
 	$query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id'";[m
 	$result = mysql_query( $query ) or die( '<pre>' . mysql_error() . '</pre>' );[m
 [m
 	$num = mysql_numrows( $result );[m
[31m-	$i = 0;[m
[32m+[m	[32m$i   = 0;[m[41m[m
 	while( $i < $num ) {[m
 		$first = mysql_result( $result, $i, "first_name" );[m
 		$last  = mysql_result( $result, $i, "last_name" );[m

[33mcommit 7ec7409a5d6c81a230e56f2a42582b43ecda6a8c[m
Author: g0tmi1k <have.you.g0tmi1k@gmail.com>
Date:   Wed Sep 16 16:32:51 2015 +0100

    cleanup - mass formatting fixing

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex c6cb623..6bffaa8 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -1,6 +1,6 @@[m
 <?php[m
 [m
[31m-if( isset($_GET[ 'Submit' ] ) ) {[m
[32m+[m[32mif( isset( $_GET[ 'Submit' ] ) ) {[m[41m[m
 	// Retrieve data[m
 	$id = $_GET[ 'id' ];[m
 [m
[36m@@ -9,13 +9,13 @@[m [mif( isset($_GET[ 'Submit' ] ) ) {[m
 [m
 	$num = mysql_numrows( $result );[m
 	$i = 0;[m
[31m-	while($i < $num) {[m
[31m-		$first = mysql_result($result, $i, "first_name");[m
[31m-		$last  = mysql_result($result, $i, "last_name");[m
[32m+[m	[32mwhile( $i < $num ) {[m[41m[m
[32m+[m		[32m$first = mysql_result( $result, $i, "first_name" );[m[41m[m
[32m+[m		[32m$last  = mysql_result( $result, $i, "last_name" );[m[41m[m
 [m
[31m-		$html .= '<pre>';[m
[31m-		$html .= 'ID: ' . $id . '<br />First name: ' . $first . '<br />Surname: ' . $last;[m
[31m-		$html .= '</pre>';[m
[32m+[m		[32m$html .= "<pre>";[m[41m[m
[32m+[m		[32m$html .= "ID: {$id}<br />First name: {$first}<br />Surname: {$last}";[m[41m[m
[32m+[m		[32m$html .= "</pre>";[m[41m[m
 [m
 		$i++;[m
 	}[m

[33mcommit 7ae63158824d784730f0fcda8c3ec4be54e70f46[m
Author: g0tmi1k <have.you.g0tmi1k@gmail.com>
Date:   Wed Sep 16 13:25:23 2015 +0100

    cleanup - formatting & removed unused xss code

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex 2cd9b14..c6cb623 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -1,6 +1,6 @@[m
 <?php[m
 [m
[31m-if(isset($_GET[ 'Submit' ])) {[m
[32m+[m[32mif( isset($_GET[ 'Submit' ] ) ) {[m[41m[m
 	// Retrieve data[m
 	$id = $_GET[ 'id' ];[m
 [m

[33mcommit 4a8c30281d3f12dfa7baa4cd17861bcbb705a1db[m
Author: g0tmi1k <have.you.g0tmi1k@gmail.com>
Date:   Sun Sep 13 20:17:44 2015 +0100

    cleanup - More general stuff

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex bbd3bb3..2cd9b14 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -4,8 +4,8 @@[m [mif(isset($_GET[ 'Submit' ])) {[m
 	// Retrieve data[m
 	$id = $_GET[ 'id' ];[m
 [m
[31m-	$getid  = "SELECT first_name, last_name FROM users WHERE user_id = '$id'";[m
[31m-	$result = mysql_query( $getid ) or die( '<pre>' . mysql_error() . '</pre>' );[m
[32m+[m	[32m$query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id'";[m[41m[m
[32m+[m	[32m$result = mysql_query( $query ) or die( '<pre>' . mysql_error() . '</pre>' );[m[41m[m
 [m
 	$num = mysql_numrows( $result );[m
 	$i = 0;[m

[33mcommit 8f9f12cfa5d701e47d9e15abc09549cabf18ea0b[m
Author: g0tmi1k <have.you.g0tmi1k@gmail.com>
Date:   Sun Sep 13 17:05:14 2015 +0100

    cleanup - generic code & minor layout changes

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex d4d1a6e..bbd3bb3 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -1,7 +1,6 @@[m
 <?php[m
 [m
 if(isset($_GET[ 'Submit' ])) {[m
[31m-[m
 	// Retrieve data[m
 	$id = $_GET[ 'id' ];[m
 [m
[36m@@ -10,7 +9,7 @@[m [mif(isset($_GET[ 'Submit' ])) {[m
 [m
 	$num = mysql_numrows( $result );[m
 	$i = 0;[m
[31m-	while ($i < $num) {[m
[32m+[m	[32mwhile($i < $num) {[m[41m[m
 		$first = mysql_result($result, $i, "first_name");[m
 		$last  = mysql_result($result, $i, "last_name");[m
 [m

[33mcommit a3e8094466cbf8086310bddd7da5d387206e06b5[m
Author: g0tmi1k <have.you.g0tmi1k@gmail.com>
Date:   Fri Sep 11 13:31:09 2015 +0100

    cleanup - General spacing/formatting issues

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex d4bfdbc..d4d1a6e 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -1,6 +1,6 @@[m
 <?php[m
 [m
[31m-if(isset($_GET['Submit'])) {[m
[32m+[m[32mif(isset($_GET[ 'Submit' ])) {[m[41m[m
 [m
 	// Retrieve data[m
 	$id = $_GET[ 'id' ];[m

[33mcommit fa0948153855ab732fd136815d5d144f8217d058[m
Author: g0tmi1k <have.you.g0tmi1k@gmail.com>
Date:   Thu Sep 10 23:15:06 2015 +0100

    cleanup - inconsistencies in levels

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex 9dce8e4..d4bfdbc 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -3,19 +3,16 @@[m
 if(isset($_GET['Submit'])) {[m
 [m
 	// Retrieve data[m
[31m-	$id = $_GET['id'];[m
[32m+[m	[32m$id = $_GET[ 'id' ];[m[41m[m
 [m
[31m-	$getid = "SELECT first_name, last_name FROM users WHERE user_id = '$id'";[m
[31m-	$result = mysql_query($getid) or die('<pre>' . mysql_error() . '</pre>' );[m
[31m-[m
[31m-	$num = mysql_numrows($result);[m
[32m+[m	[32m$getid  = "SELECT first_name, last_name FROM users WHERE user_id = '$id'";[m[41m[m
[32m+[m	[32m$result = mysql_query( $getid ) or die( '<pre>' . mysql_error() . '</pre>' );[m[41m[m
 [m
[32m+[m	[32m$num = mysql_numrows( $result );[m[41m[m
 	$i = 0;[m
[31m-[m
 	while ($i < $num) {[m
[31m-[m
[31m-		$first = mysql_result($result,$i,"first_name");[m
[31m-		$last = mysql_result($result,$i,"last_name");[m
[32m+[m		[32m$first = mysql_result($result, $i, "first_name");[m[41m[m
[32m+[m		[32m$last  = mysql_result($result, $i, "last_name");[m[41m[m
 [m
 		$html .= '<pre>';[m
 		$html .= 'ID: ' . $id . '<br />First name: ' . $first . '<br />Surname: ' . $last;[m

[33mcommit da1dc52cface2af8a20df10690fc9e623aac7063[m
Author: g0tmi1k <have.you.g0tmi1k@gmail.com>
Date:   Thu Sep 10 14:00:47 2015 +0100

    cleanup - Various spacing/formatting issues
    
    ...Theres a lot here

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex 95ae548..9dce8e4 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -1,9 +1,8 @@[m
 <?php[m
 [m
[31m-if(isset($_GET['Submit'])){[m
[32m+[m[32mif(isset($_GET['Submit'])) {[m[41m[m
 [m
 	// Retrieve data[m
[31m-[m
 	$id = $_GET['id'];[m
 [m
 	$getid = "SELECT first_name, last_name FROM users WHERE user_id = '$id'";[m
[36m@@ -19,10 +18,11 @@[m [mif(isset($_GET['Submit'])){[m
 		$last = mysql_result($result,$i,"last_name");[m
 [m
 		$html .= '<pre>';[m
[31m-		$html .= 'ID: ' . $id . '<br>First name: ' . $first . '<br>Surname: ' . $last;[m
[32m+[m		[32m$html .= 'ID: ' . $id . '<br />First name: ' . $first . '<br />Surname: ' . $last;[m[41m[m
 		$html .= '</pre>';[m
 [m
 		$i++;[m
 	}[m
 }[m
[32m+[m[41m[m
 ?>[m

[33mcommit 912247ac5ddcd348fe33b75f3dc9f3a85f7f42ab[m
Author: g0tmi1k <have.you.g0tmi1k@gmail.com>
Date:   Thu Sep 10 12:43:50 2015 +0100

    cleanup - EOL inconsistency & trailing spaces

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex 14146b0..95ae548 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -1,9 +1,9 @@[m
[31m-<?php	[m
[32m+[m[32m<?php[m[41m[m
 [m
 if(isset($_GET['Submit'])){[m
[31m-	[m
[32m+[m[41m[m
 	// Retrieve data[m
[31m-	[m
[32m+[m[41m[m
 	$id = $_GET['id'];[m
 [m
 	$getid = "SELECT first_name, last_name FROM users WHERE user_id = '$id'";[m
[36m@@ -17,7 +17,7 @@[m [mif(isset($_GET['Submit'])){[m
 [m
 		$first = mysql_result($result,$i,"first_name");[m
 		$last = mysql_result($result,$i,"last_name");[m
[31m-		[m
[32m+[m[41m[m
 		$html .= '<pre>';[m
 		$html .= 'ID: ' . $id . '<br>First name: ' . $first . '<br>Surname: ' . $last;[m
 		$html .= '</pre>';[m

[33mcommit 6312642831abf9eec245b2542e0614bddeb50af6[m
Author: g0tmi1k <have.you.g0tmi1k@gmail.com>
Date:   Thu Sep 10 12:30:18 2015 +0100

    cleanup - unix2dos (main PHP files)
    
    + Missing: ./external/*
    + *nix is able to cope 'better' with Windows EOL,
      rather than other way around.

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mindex 2a036c0..14146b0 100644[m
[1m--- a/vulnerabilities/sqli/source/low.php[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -25,4 +25,4 @@[m [mif(isset($_GET['Submit'])){[m
 		$i++;[m
 	}[m
 }[m
[31m-?>[m
[32m+[m[32m?>[m[41m[m

[33mcommit d80faa1bd32ab0e9fc32373fe958cc19e550a2df[m
Author: erwanlr <erwan.lr@gmail.com>
Date:   Wed May 1 15:10:15 2013 +0200

    Initial Commit

[1mdiff --git a/vulnerabilities/sqli/source/low.php b/vulnerabilities/sqli/source/low.php[m
[1mnew file mode 100644[m
[1mindex 0000000..2a036c0[m
[1m--- /dev/null[m
[1m+++ b/vulnerabilities/sqli/source/low.php[m
[36m@@ -0,0 +1,28 @@[m
[32m+[m[32m<?php[m[41m	[m
[32m+[m[41m[m
[32m+[m[32mif(isset($_GET['Submit'])){[m[41m[m
[32m+[m[41m	[m
[32m+[m	[32m// Retrieve data[m[41m[m
[32m+[m[41m	[m
[32m+[m	[32m$id = $_GET['id'];[m[41m[m
[32m+[m[41m[m
[32m+[m	[32m$getid = "SELECT first_name, last_name FROM users WHERE user_id = '$id'";[m[41m[m
[32m+[m	[32m$result = mysql_query($getid) or die('<pre>' . mysql_error() . '</pre>' );[m[41m[m
[32m+[m[41m[m
[32m+[m	[32m$num = mysql_numrows($result);[m[41m[m
[32m+[m[41m[m
[32m+[m	[32m$i = 0;[m[41m[m
[32m+[m[41m[m
[32m+[m	[32mwhile ($i < $num) {[m[41m[m
[32m+[m[41m[m
[32m+[m		[32m$first = mysql_result($result,$i,"first_name");[m[41m[m
[32m+[m		[32m$last = mysql_result($result,$i,"last_name");[m[41m[m
[32m+[m[41m		[m
[32m+[m		[32m$html .= '<pre>';[m[41m[m
[32m+[m		[32m$html .= 'ID: ' . $id . '<br>First name: ' . $first . '<br>Surname: ' . $last;[m[41m[m
[32m+[m		[32m$html .= '</pre>';[m[41m[m
[32m+[m[41m[m
[32m+[m		[32m$i++;[m[41m[m
[32m+[m	[32m}[m[41m[m
[32m+[m[32m}[m[41m[m
[32m+[m[32m?>[m
