<?php

	class myCurl {

		protected $ch;

		protected $url;

		protected $userAgent;

		protected $proxyIp;

		protected $proxyPort;

		protected $error;

		protected $isPost;

		public function __construct(){
			$this->ch = curl_init();
			$this->setReturn();
			$this->setShowHeader();
		}

		public function setUrl($url){
			$this->url = $url;
			curl_setopt($this->ch,CURLOPT_URL,$this->url);
		}

		public function setPost($bool,$param = []){
			$this->isPost = $bool;
			curl_setopt($this->ch,CURLOPT_POST,$bool);
			curl_setopt($this->ch,CURLOPT_POSTFIELDS,$param);
		}

		public function setPostParam($param = []){
			if($this->isPost){
				curl_setopt($this->ch,CURLOPT_POSTFIELDS,$param);
			}
		}

		public function setUserAgent($userAgent){
			$this->userAgent = $userAgent;
			// curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)");
			curl_setopt($this->ch,CURLOPT_USERAGENT,$this->userAgent);
		}

		public function setProxy($ip,$port){
			$this->proxyIp = $ip;
			$this->proxyPort = $port;
			curl_setopt($this->ch,CURLOPT_PROXY,$this->proxyIp);
			curl_setopt($this->ch,CURLOPT_PROXYPORT,$this->proxyPort);
		}

		public function setHttps($bool = false){
			//访问https
			curl_setopt($this->ch,CURLOPT_SSL_VERIFYPEER,$bool);
			curl_setopt($this->ch,CURLOPT_SSL_VERIFYHOST,$bool);
		}

		public function setopt($key,$val){
			curl_setopt($this->ch,$key,$val);
		}

		public function setReturn($bool = 1){
			curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,$bool);//返回数据
		}

		public function setShowHeader($boll = 0){
			curl_setopt($this->ch,CURLOPT_HEADER,0);//不显示头信息
		}

		public function exec(){
			$result = curl_exec($this->ch);
			if($result === false){
				$this->error = curl_error();
			}
			return $result;
		}

		public function close(){
			curl_close($this->ch);
		}

		public function getError(){
			return $this->error;
		}

		public function getCurl(){
			return $this->ch;
		}
	}