<?php

class cURL {
	var $headers;
	var $respHead;
	var $user_agent;
    var $compression='gzip';
	var $cookies='';
	var $cookie_file;
	var $proxyIP;
	var $proxyPort;
	var $proxyUserPwd;
	var $proxyType='HTTP';
	var $_proxyTrack='NA';
	
	function __construct($cookie='selfCook.txt') {
		$this->headers[] = "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
		$this->headers[] = "Accept-Language:en-us,en;q=0.5";
		$this->headers[] = "Accept-Charset:ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$this->headers[] = "Connection:Keep-Alive";
		$this->headers[] = "Host:www.wayfair.com";
		$this->headers[] = 'If-None-Match:W/"52ae0-EPfT+30iqU3edqM2WeNiYLOmW4I"';
		$this->headers[] = "Referer:https://www.wayfair.com/";

		#$this->user_agent =  "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/534.57.2 (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2";
		$this->user_agent = $this->setBrowser();
		$this->cookies=$cookie;
		if ($this->cookies == TRUE) $this->cookie($cookie); 
	}
    
	function cookie($cookie_file) {
		if (file_exists($cookie_file)) {
			$this->cookie_file=$cookie_file;
		} else { 
			$fp=fopen($cookie_file,'w') or $this->error("The cookie file could not be opened. Make sure this directory has the correct permissions");
			$this->cookie_file=$cookie_file;
			fclose($fp);
		}
	}

    function getHeader($url) {
        $process = curl_init ($url);
		curl_setopt($process, CURLOPT_URL, $url);
        curl_setopt($process, CURLOPT_HEADER, 1);
		curl_setopt($process, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 10);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_BINARYTRANSFER,1);
        $rawdata=curl_exec($process);
		list($headers, $response) = explode("\r\n\r\n", $rawdata, 2);
		return $headers;
    }

	function getHeader1($url) {
		$process = curl_init($url);
		curl_setopt($process, CURLOPT_URL, $url);
		curl_setopt($process, CURLOPT_HEADER, 1);
		curl_setopt($process, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($process, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($process, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($process, CURLOPT_TIMEOUT, 100);
		curl_setopt($process, CURLOPT_MAXREDIRS, 10);
		curl_exec($process);
		$lasturl = curl_getinfo($process, CURLINFO_EFFECTIVE_URL);
		return $lasturl;
	} 

	function get($url,$refer=null) {
		$cookie='';
		if(file_exists($this->cookie_file)) {
			$cookie=trim($this->getCookieStr());
			if($cookie != '') {$cookie.='; ';}
		}
		$this->user_agent = $this->setBrowser();
		$options = array(
			CURLOPT_RETURNTRANSFER => true,         		// return web page
			CURLOPT_HTTPHEADER     => $this->headers,       // customise header request
			CURLOPT_HEADER         => false,			    // don't return headers
			CURLINFO_HEADER_OUT    => true,					// return request headers
			CURLOPT_REFERER        => $refer,         		// follow redirects
			CURLOPT_USERAGENT      => $this->user_agent,    // who am i
			CURLOPT_COOKIE		   => $cookie,				// who am i
			CURLOPT_COOKIEFILE     => $this->cookie_file,   // who am i
			CURLOPT_COOKIEJAR      => $this->cookie_file,   // who am i
			CURLOPT_AUTOREFERER    => true,         		// set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,          		// timeout on connect
			CURLOPT_TIMEOUT        => 240,          		// timeout on response
			CURLOPT_MAXREDIRS      => 30,           		// stop after 10 redirects
			CURLOPT_SSL_VERIFYHOST => false,            	// don't verify ssl
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_VERBOSE        => 1,
			CURLOPT_FOLLOWLOCATION => 5
			#CURLOPT_ENCODING       => $this->compression
		);			

		if (isset($this->proxyIP))
		{
			$options[CURLOPT_PROXY] = $this->proxyIP;
			if(strtolower($this->proxyType)=='socks4') { $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS4; };
			if(strtolower($this->proxyType)=='socks5') { $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5; };
			$this->_proxyTrack="$this->proxyIP:$this->proxyPort";
		}
		if (isset($this->proxyIP) and isset($this->proxyPort)) { $options[CURLOPT_PROXYPORT] = $this->proxyPort; };
		if ($this->proxyUserPwd) { $options[CURLOPT_PROXYUSERPWD] = $this->proxyUserPwd; };
		$ch = curl_init($url);
		#print_r($options);
		curl_setopt_array($ch,$options);
		$content = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($http_code!=200) {
			$data = "[" . date("Y-m-d-H-i-s") . "] : Error: [$http_code ## $url ## $this->_proxyTrack]\n";
			$dir = "/home/mysamm/price_checker/logs/dev-logs/";
			if (!is_dir($dir)) {
				$dir = "/home/mysamm/ibmspace/price_checker/logs/dev-logs/";
			}
			$dir = $dir."dev-log.txt";
			file_put_contents($dir, $data, FILE_APPEND | LOCK_EX);	
		}
		if ($http_code == 301 || $http_code == 302 || $http_code == 303){
			$last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
			curl_close($ch);
			if(preg_match("/Location:\s*(.*?)(\n|\s+)/is", $content, $match)){
				$path = trim($match[1]);
				if(!preg_match('/^http/is', $path)){
					$path = $last_url['scheme'].'://'.$last_url['host'].$path;
				}
				return $this->get($path);
			}else{
				return $content;
			}
		} else{
			curl_close($ch);
			return $content;
		}
	}

	function post($url,$data='',$refer=null) {
		$cookie='';
		if(file_exists($this->cookie_file)) {
			$cookie=trim($this->getCookieStr());
			if($cookie != '') $cookie.='; ';
		}

		$options = array(
			CURLOPT_RETURNTRANSFER => true,               // return web page
			CURLOPT_HTTPHEADER     => $this->headers,     // customise header request
			CURLOPT_HEADER         => true,               // don't return headers
			CURLINFO_HEADER_OUT    => true,               // return request headers
			CURLOPT_REFERER        => $refer,             // follow redirects
			CURLOPT_USERAGENT      => $this->setBrowser(),// who am i
			CURLOPT_COOKIE		   => $cookie,            // who am i
			CURLOPT_COOKIEFILE     => $this->cookie_file, // who am i
			CURLOPT_COOKIEJAR      => $this->cookie_file, // who am i
			CURLOPT_AUTOREFERER    => true,               // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,                // timeout on connect
			CURLOPT_TIMEOUT        => 360,                // timeout on response
			CURLOPT_MAXREDIRS      => 30,                 // stop after 10 redirects
			CURLOPT_POST           => 1,                  // i am sending post data
			CURLOPT_POSTFIELDS     => $data,              // this are my post vars
			CURLOPT_SSL_VERIFYHOST => false,              // don't verify ssl
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_VERBOSE        => 1,
			CURLOPT_FOLLOWLOCATION => 5,
			CURLOPT_ENCODING       => $this->compression
		);

		if (isset($this->proxyIP)) {
			$options[CURLOPT_PROXY] = $this->proxyIP;
			if(strtolower($this->proxyType)=='socks4') { $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS4; };
			if(strtolower($this->proxyType)=='socks5') { $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5; };
			$this->_proxyTrack="$this->proxyIP:$this->proxyPort";
		}
		if (isset($this->proxyIP) and isset($this->proxyPort)) { $options[CURLOPT_PROXYPORT] = $this->proxyPort; };
		if ($this->proxyUserPwd) { $options[CURLOPT_PROXYUSERPWD] = $this->proxyUserPwd; };
		$ch = curl_init($url);
		curl_setopt_array($ch,$options);
		$content = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($http_code!=200) {
			$data = "[" . date("Y-m-d-H-i-s") . "] : Error: [$http_code ## $url ## $this->_proxyTrack]\n";
			$dir = "/home/mysamm/price_checker/logs/dev-logs/";
			if (!is_dir($dir)) {
				$dir = "/home/mysamm/ibmspace/price_checker/logs/dev-logs/";
			}
			$dir = $dir."dev-log.txt";
			file_put_contents($dir, $data, FILE_APPEND | LOCK_EX);	
		}
		if ($http_code == 301 || $http_code == 302 || $http_code == 303){
			$last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
			curl_close($ch);
			if(preg_match("/Location:\s*(.*?)(\n|\s+)/is", $content, $match)){
				$path = trim($match[1]);
				if(!preg_match('/^http/is', $path)){
					$path = $last_url['scheme'].'://'.$last_url['host'].$path;
				}
				return $this->get($path);
			}else{
				return $content;
			}
		} else {
			curl_close($ch);
			return $content;
		}
	}

	function getURL($url){
		$options = array(
			CURLOPT_RETURNTRANSFER => true,         		// return web page
			CURLOPT_HTTPHEADER     => $this->headers,       // customise header request
			CURLOPT_HEADER         => true,				// don't return headers
			CURLINFO_HEADER_OUT    => true,					// return request headers
			CURLOPT_REFERER        => '',         		// follow redirects
			CURLOPT_USERAGENT      => $this->user_agent,    // who am i
			CURLOPT_COOKIE		   => '',				// who am i
			CURLOPT_COOKIEFILE     => $this->cookie_file,   // who am i
			CURLOPT_COOKIEJAR      => $this->cookie_file,   // who am i
			CURLOPT_AUTOREFERER    => true,         		// set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,          		// timeout on connect
			CURLOPT_TIMEOUT        => 240,          		// timeout on response
			CURLOPT_MAXREDIRS      => 30,           		// stop after 10 redirects
			CURLOPT_SSL_VERIFYHOST => false,            	// don't verify ssl
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_VERBOSE        => 1
		);

		if (isset($this->proxyIP)) {
			$options[CURLOPT_PROXY] = $this->proxyIP;
			if(strtolower($this->proxyType)=='socks4') { $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS4; };
			if(strtolower($this->proxyType)=='socks5') { $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5; };
			$this->_proxyTrack="$this->proxyIP:$this->proxyPort";
		}
		if (isset($this->proxyIP) and isset($this->proxyPort)) { $options[CURLOPT_PROXYPORT] = $this->proxyPort; };
		if ($this->proxyUserPwd) { $options[CURLOPT_PROXYUSERPWD] = $this->proxyUserPwd; };

		$ch = curl_init($url);
		curl_setopt_array($ch,$options);
		$content = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($http_code!=200) {
			$data = "[" . date("Y-m-d-H-i-s") . "] : Error: [$http_code ## $url ## $this->_proxyTrack]\n";
			$dir = "/home/mysamm/price_checker/logs/dev-logs/";
			if (!is_dir($dir)) {
				$dir = "/home/mysamm/ibmspace/price_checker/logs/dev-logs/";
			}
			$dir = $dir."dev-log.txt";
			file_put_contents($dir, $data, FILE_APPEND | LOCK_EX);	
		}
		if ($http_code == 301 || $http_code == 302){
			$last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
			curl_close($ch);
			if(preg_match("/Location:\s*(.*?)(\n|\s+)/is", $content, $match)){
				$path = trim($match[1]);
				if(!preg_match('/http/is', $path)){
					$path = $last_url['scheme'].'://'.$last_url['host'].$path;
				}
				return $path;
			}else{
				return $url;
			}
		}
		else{
			curl_close($ch);
			return $url;
		}
	}

	function getCookieStr() {
		$cookies=$this->extractCookies(file_get_contents($this->cookie_file));
		$len=count($cookies);
		$str=array();
		if($len>0) {
			for ($i=0;$i<$len;$i++) {
				$str[]=$cookies[$i]['name'].'='.$cookies[$i]['value'];
			}
			$str = implode('; ', $str);
			return $str;
		}
	}

	function extractCookies($string) {
		$cookies = array();
		$lines = explode("\n", $string);
		foreach ($lines as $line) {
			if (isset($line[0]) && substr_count($line, "\t") == 6) {
				$tokens = explode("\t", $line);
				$tokens = array_map('trim', $tokens);
				$cookie = array();
				$cookie['domain'] = $tokens[0];
				$cookie['flag'] = $tokens[1];
				$cookie['path'] = $tokens[2];
				$cookie['secure'] = $tokens[3];
				$cookie['expiration'] = date('Y-m-d h:i:s', $tokens[4]);
				$cookie['name'] = $tokens[5];
				$cookie['value'] = $tokens[6];
				$cookies[] = $cookie;
			}
		}
		return $cookies;
	}

	function setBrowser() {
		$browsers = Array(
			
			'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:74.0) Gecko/20100101 Firefox/74.0',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:77.0) Gecko/20100101 Firefox/77.0',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:80.0) Gecko/20100101 Firefox/80.0',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:80.0) Gecko/20100101 Firefox/80.0',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:80.0) Gecko/20100101 Firefox/80.0',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:94.0) Gecko/20100101 Firefox/94.0',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:99.0) Gecko/20100101 Firefox/99.0',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:97.0) Gecko/20100101 Firefox/97.0',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:100.0) Gecko/20100101 Firefox/100.0'
		);
		$random_number = rand(0,4);
		$browser = $browsers[$random_number];
		return $browser;
	}
  
	function error($error) {
		echo "<center><div style='width:500px;border: 3px solid #FFEEFF; padding: 3px; background-color: #FFDDFF;font-family: verdana; font-size: 10px'><b>cURL Error</b><br>$error</div></center>";
		die;
	}


	function http_get_response($url, $post_data=null, $refer=null){
			REDIR:
			$cookie = null;
			if(file_exists($this->cookie_file)) {
				$cookie = trim(self::get_cookie_str());
				if($cookie != ''){
					$cookie .= '; ';
				}
			}
			$options = array(
				CURLOPT_RETURNTRANSFER => true,         		// return web page
				CURLOPT_HTTPHEADER     => $this->headers,       // customise header request
				CURLOPT_HEADER         => false,			    // don't return headers
				CURLINFO_HEADER_OUT    => true,					// return request headers
				CURLOPT_REFERER        => $refer,         		// follow redirects
				CURLOPT_USERAGENT      => $this->setBrowser(),  // who am i
				CURLOPT_COOKIE		   => $cookie,				// who am i
				CURLOPT_COOKIEFILE     => $this->cookie_file,   // who am i
				CURLOPT_COOKIEJAR      => $this->cookie_file,   // who am i
				CURLOPT_AUTOREFERER    => true,         		// set referer on redirect
				CURLOPT_CONNECTTIMEOUT => 120,          		// timeout on connect
				CURLOPT_TIMEOUT        => 240,          		// timeout on response
				CURLOPT_MAXREDIRS      => 30,           		// stop after 10 redirects
				CURLOPT_SSL_VERIFYHOST => false,            	// don't verify ssl
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_VERBOSE        => 1,
				CURLOPT_FOLLOWLOCATION => 5,
				CURLOPT_ENCODING       => 'gzip'
			);
			if (strlen($post_data) > 5) {
				$options[CURLOPT_POST] = 1;
				$options[CURLOPT_POSTFIELDS] = $post_data;
			}			

			if (isset($this->proxy_value)) {
				$options[CURLOPT_PROXY] = $this->proxy_value;
				if (strtolower($this->proxy_type) == 'socks4') { 
					$options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS4; 
				}
				if (strtolower($this->proxy_type) == 'socks5') { 
					$options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5; 
				}
				if (isset($this->proxy_port)) { 
					$options[CURLOPT_PROXYPORT] = $this->proxy_port; 
				}
				if (isset($this->proxy_user_pwd)) { 
					$options[CURLOPT_PROXYUSERPWD] = $this->proxy_user_pwd; 
				}
			}
			$curl_init = curl_init($url);
			curl_setopt_array($curl_init, $options);
			$output = curl_exec($curl_init);
			$http_code = curl_getinfo($curl_init, CURLINFO_HTTP_CODE);				

			if (preg_match('/^30\d/', $http_code)) {
				$redir_array = parse_url(curl_getinfo($curl_init, CURLINFO_EFFECTIVE_URL));
				curl_close($curl_init);
				if (preg_match("/Location:\s*(.*?)(\n|\s+)/is", $output, $match)) {
					$path = trim($match[1]);
					if (!preg_match('/^http/is', $path)) {
						$path = $redir_array['scheme'].'://'.$redir_array['host'].$path;
					}
					$url = $path;
					goto REDIR;
				} else {
					return $output;
				}
			} else {
				curl_close($curl_init);
				return $output;
			}
		}

		
		function get_cookie_str(){
			$cookies = self::extract_cookies(file_get_contents($this->cookie_file));
			$count = count($cookies);
			$str = array();
			if ($count>0) {
				for ($i=0; $i<$count; $i++) {
					$str[] = $cookies[$i]['name'].'='.$cookies[$i]['value'];
				}
				$str = implode('; ', $str);
				return $str;
			}
		}

		function extract_cookies($string){
			$cookies = array();
			$lines = explode("\n", $string);
			foreach ($lines as $line) {
				if (isset($line[0]) && substr_count($line, "\t") == 6) {
					$tokens = explode("\t", $line);
					$tokens = array_map('trim', $tokens);
					$cookie = array();
					$cookie['domain'] = $tokens[0];
					$cookie['flag'] = $tokens[1];
					$cookie['path'] = $tokens[2];
					$cookie['secure'] = $tokens[3];
					$cookie['expiration'] = date('Y-m-d h:i:s', $tokens[4]);
					$cookie['name'] = $tokens[5];
					$cookie['value'] = $tokens[6];
					$cookies[] = $cookie;
				}
			}
			return $cookies;
		}
}
?>