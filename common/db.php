<?php

// connect to dbs from dbSettings - every key is a new db object
try {
	foreach ($dbSettings as $dbTitle => $dbSet) {
		
		list($dbHost, $dbUser, $dbPass, $dbName) = array_values($dbSet);
		dbWrapper::connect($dbTitle, "mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
		dbWrapper::getDb($dbTitle)->setErrorCallbackFunction("dbErrorHandler", "text");
		// if we are using PDO directly - now we use dbWrapper
		/*$$dbTitle = new PDO(
				"mysql:host={$dbHost};dbname={$dbName}",
				$dbUser,
				$dbPass,
				array(
					PDO::ATTR_PERSISTENT => true,
					1002 => 'SET NAMES utf8',
					//PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
				)
		);*/
	}
} catch(Exception $e) {
	print_r($dbSet);
	echo("Error connecting to db ".$e->getMessage());
	exit;  
}
