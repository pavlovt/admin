<?
// connect to dbs from dbSettings - every key is a new db object
try {
	foreach ($dbSettings as $dbTitle => $dbSet) {
		
		list($dbHost, $dbUser, $dbPass, $dbName) = array_values($dbSet);
		dbWrapper::connect($dbTitle, "mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
		dbWrapper::getDb($dbTitle)->setErrorCallbackFunction("dbErrorHandler", "text");
	}

	dbWrapper::getDb('db')->run('SET SESSION group_concat_max_len = 1000000');
} catch(Exception $e) {
	print_r($dbSet);
	echo("Error connecting to db ".$e->getMessage());
	exit;  
}
