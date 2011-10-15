<?php

// init orm class
R::setup();

// print debug information
R::debug(true);

// connect to dbs from dbSettings - every key is a new db object
try {
	foreach ($dbSettings as $dbTitle => $dbSet) {
		
		list($dbHost, $dbUser, $dbPass, $dbName) = array_values($dbSet);
		// all parameters like in the PDO exept for the last one - if true db schema will not change and R works faster
		R::addDatabase($dbTitle,"mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);

		// select the database
		R::selectDatabase($dbTitle);
		//$dbTitle->query('SET NAMES utf8');
		   
	}
} catch(Exception $e) {
	print_r($dbSet);
	sendMail("Error connecting to db ".$e->getMessage());
	exit;  
}

// set default formatter - defines table name, primary key name, etc.
class MyBeanFormatter implements RedBean_IBeanFormatter {
	public function formatBeanTable($type) {
		//return 'cms_'.$type;
		return $type;
	}
	public function formatBeanID($type) {
		return $type.'Id';
	}
	public function getAlias($field) {
		//if ($field=='student') return 'person';
		return $field;
	}
}
    
R::$writer->setBeanFormatter( new MyBeanFormatter );