<?php
/* The purpose of this file to establish the connection between your php page
 * and the database. On successfull completion you will have a variable $db 
 * that is your database connection ready to use.
 */



include("/usr/local/uvm-inc/dguild.inc");

$databaseName="DGUILD_cs148_assignment4";        

$dsn = 'mysql:host=webdb.uvm.edu;dbname=';

function dbConnect($dbName){
    global $db, $dsn, $db_A_username, $db_A_password;

    if (!$db) $db = new PDO($dsn . $dbName, $db_A_username, $db_A_password); 
        if (!$db) {
          return 0;
        } else {
          return $db;
        }
} 

// create the PDO object
try {     
    $db=dbConnect($databaseName);
    if($debug) echo '<p>A You are connected to the database!</p>';
} catch (PDOException $e) {
    $error_message = $e->getMessage();
    if($debug) echo "<p>An error occurred while connecting to the database: $error_message </p>";
}
?>