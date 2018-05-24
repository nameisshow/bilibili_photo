<?php

	$host = '127.0.0.1';
	$dbname = 'bi';
	$user = 'root';
	$passwd = 'root';
	$port = 3306;

	$dsn = "mysql:host=$host;dbname=$dbname;port=$port";
	$username = $user;
	$passwd = $passwd;

	try {
		$db = new \PDO($dsn, $username, $passwd);
	} catch (\Exception $e) {
		print_r($e->getMessage()) . "\r\n";
		exit();
	}

	require_once './userAgent.php';
	// var_dump($userAgent);
	function randomUserAgent(&$userAgent){
		$count = count($userAgent);
		return $userAgent[rand(1,$count)];
	}

	

	// require_once './proxy.php';

	require_once './curl.php';

