<?php
//print_r($dbSettings);//exit;	

// connect to dbs from dbSettings - every key is a new db object
try {
	foreach ($dbSettings as $dbsName => $dbSet) {
	  //echo "connecting to ".$dbsName."\n";
	  list($dbHost, $dbUser, $dbPass, $dbName) = array_values($dbSet);
	  $$dbsName = new PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass, array(PDO::ATTR_PERSISTENT => true));

	  $$dbsName->query('SET NAMES utf8');
	   
	}  
}  
catch(PDOException $e) {
	print_r($dbSet);
	sendMail("Error connecting to db ".$e->getMessage());
	exit;  
}