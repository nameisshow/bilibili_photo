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

	//userAgent
	require_once './userAgent.php';
	// var_dump($userAgent);
	function randomUserAgent(&$userAgent){
		$count = count($userAgent);
		return $userAgent[rand(1,$count)];
	}

	//参数
	$param = [
		'Photo'=>['cos','sifu'],
		'Doc'=>['illustration','comic','draw'],
	];
	function formatParam($param){
		$res = [];
		foreach($param as $key=>$val){
			foreach($val as $k=>$v){
				$res[] = [$key,$v];
			}
		}
		return $res;
	}
	$param = formatParam($param);

	// require_once './proxy.php';

	require_once './curl.php';

	

	$mycurl = new myCurl();
	$mycurl->setHttps(false);



	function getUrl($photo,$category,$page,$type = 'hot'){
		//phot : Photo,Doc
		//category : cos,sifu,illustration,comic,draw,all
		//type : hot,new
		//page : 0,1,2,3,4...
		$url = "https://api.vc.bilibili.com/link_draw/v2/{$photo}/list?category={$category}&type={$type}&page_num={$page}&page_size=20";

		return $url;
	}

	for($i = 0; $i < 25; $i++){
		foreach($param as $key=>$val){
			$url = getUrl($val[0],$val[1],$i,'hot');
			$mycurl->setUrl($url);
			$result = $mycurl->exec();
			if(!$result){
				var_dump($mycurl->error());die;
			}else{
				insert($result['data']['items']);
			}
		}
	}

	function insert($data){

	}

