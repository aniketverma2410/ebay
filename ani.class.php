<?php
//error_reporting(0);
require_once PATH . "comprehensive_search_crawlers/sunil/colemanfurniture/aniket/cURL.php";
require_once PATH . "comprehensive_search_crawlers/sunil/colemanfurniture/aniket/fetch_data.class.php";

class ebay extends cURL
{
	var $server_ip = '169.62.76.75';
    function __construct($con,$client_id,$crawler_id)
    {
    	 	$this->con=$con;
	        $this->user_id = $client_id;
	        $this->crawler_id = $crawler_id;
     }

    public function startCrawler($searchKey){

		$htmlpage = '';
		$this->searchKey=$searchKey;
		$this->sku=$sku;
		$this->upc=$upc;
		$searchKey=preg_replace('/ /',"+",$this->searchKey);
		$this->product_url=$this->url_creator($searchKey);
		$retry_count = 0;
		$this->checkstatus=0;

	Retry:
		$retry_count++;
		$json_array = array();
		$json_data_array = array();
		$this->product_main_array = array();
		$this->url = trim($this->product_url);
		print "\n+++++++$this->url+++++++++\n";
		for ($i=1; $i <33;$i++) {
			$url="https://colemanfurniture.com/searchresults.html?q=classic+home&_pgn=$i&rt=nc";
			// $url="https://www.ebay.com/sch/i.html?_from=R40&_nkw=transocean+rugs&_sacat=0&LH_TitleDesc=0&_pgn=$i&rt=nc";


		$this->htmlpage = $this->getPageResponse($url);
		echo '1';die;
		file_put_contents('aaaaaaaaaaaa.html',$this->htmlpage);
		echo 'ok';die;
		//die;
			if ($this->htmlpage!="") {	
				echo 'ok1';die;		
				$this->fetch_data();
			}
			$this->htmlpage="";
		}
		$htmlpage = '';
	}

	function fetch_data()
	{
		echo 'ok2';die;
	   $resultFile=new fetch($this->searchKey);
	   if(preg_match('/class\=\"imgContent\"\>(.*)\<script\>/',$this->htmlpage,$index_data)){
	   	if (preg_match_all('/<li(.*?)<\/li>/',$index_data[1],$product_list)) {	 
	   		foreach ($product_list[1] as $key => $value) {
	   			$product_mpn="";
	   			$product_upc="";
	   			$product_brand="";
	   			if(preg_match('/item__link href=(.*?)>/',$value,$match_url)){	   				
		   			//foreach ($match_url[1] as $key => $value) {
	   				if ($match_url[1]!="") {
	   					
		   				$htmlpage_new = $this->getPageResponse($match_url[1]);
		   				file_put_contents('new.html', $htmlpage_new);
		   				if ($htmlpage_new!="") {
		   					$htmlDoc=new DomDocument();	
							@$htmlDoc->loadHTML($htmlpage_new);
							$xpath = new DOMXpath($htmlDoc);
							$nodelist = $xpath->query('//*[@id="viTabs_0_is"]');
							foreach($nodelist as $n) {
								$node = $xpath->query('//span[@itemprop="brand"]/span/div/span',$n);
								foreach($node as $a){
									$product_brand = trim($a->nodeValue);
									
								}

								$node = $xpath->query('//span[@itemprop="mpn"]/div/span',$n);
								foreach($node as $a){
									$product_mpn = trim($a->nodeValue);
									//$product_name=preg_replace('/[^a-zA-Z0-9_ ]/s','',$product_name);
								}
								$node = $xpath->query('//div/span[@itemprop="gtin13"]/div/span',$n);
								foreach($node as $a){
									$product_upc = trim($a->nodeValue);
									
								}

							}

							if(preg_match('/id\=\"vi\-lkhdr\-itmTitl\"\s+class\=\"u\-dspn\"\>(.*?)<\/span>/',$htmlpage_new,$name_array)){		
							 	$name=$name_array[1];
							}else{
								$name="";
							}
							file_put_contents("ani1.csv", "" . $match_url[1] . ",".$name.",".$product_brand.",".$product_mpn.",".$product_upc."" . PHP_EOL, @FILE_APPEND);
							//self::data_insert($match_url[1],$name,$product_brand,$product_mpn,$product_upc);
		   				}
		   			}
	   			}
	   	 		
	   		}
	   		
	   	}
	   }
		
	}



function data_insert($url,$name,$product_brand,$product_mpn,$product_upc) {
	
		$dataArr = array();
		$dataArr['url'] =$url;
		$dataArr['name'] =$name;
		$dataArr['brand'] =$product_brand;
		$dataArr['mpn'] =$product_mpn;
		$dataArr['upc'] =$product_upc;
		print_r($dataArr);
		//echo $this->table_name;die;
		$this->con->Save($this->table_name , $dataArr);
		unset($dataArr);
		$this->return_data="";
}	

	 function reading_csv_data(){
	 	$this->all_input_url_array = array();		 
		if(file_exists($this->delta_file_name)){
			$row = 1;
			if (($handle = fopen("$this->delta_file_name", "r")) !== FALSE) {
			while (($data = fgetcsv($handle,  ",")) !== FALSE) {
				if ($row != 1){
					$temp_array['search_keyword']=trim($data[0]);
					$temp_array['SKU']=$data[4];
					$temp_array['UPC']=trim($data[5]);
					$this->all_input_url_array[]=$temp_array;
				}				
				
				$row++;
			}
			fclose($handle);
		}
	}	
	}


	function getPageResponse($url){
	$this->configProxy(1);
	$retryflag = 0;
	retry:
		if ($retryflag % 2 == 0){
			$this->configProxy(0);
		}
		else{
			$this->configProxy(1);
		}
		$fileName="ani.html";
		$this->user_agent = $this->setBrowser();

			print "responce cmd ===================\n\n";
			if ($retryflag == 0)
			{
				 echo $curl_cmd = 'curl -L -x "'.$this->proxyIP.'":"'.$this->proxyPort.'"  -U "'.$this->proxyUserPwd.'" "'.$url.'" -H "authority: www.ebay.com" -H "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9" -H "accept-language: en-IN,en-GB;q=0.9,en-US;q=0.8,en;q=0.7" -H "cache-control: max-age=0" -H "sec-ch-ua: \".Not/A)Brand\";v=\"99\", \"Google Chrome\";v=\"103\", \"Chromium\";v=\"103\"" -H "sec-ch-ua-mobile: ?0" -H "sec-ch-ua-platform: \"Windows\"" -H "sec-fetch-dest: document" -H "sec-fetch-mode: navigate" -H "sec-fetch-site: same-origin" -H "sec-fetch-user: ?1" -H "upgrade-insecure-requests: 1" -H "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36" --compressed';
		         $resultFile= shell_exec($curl_cmd);
		        
				// echo $curl = "curl -A 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36' -x '$this->proxyIP:$this->proxyPort' -U '$this->proxyUserPwd' -k -L '$url' -m '10'  -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8' -H 'Accept-Language: en-US,en;q=0.5' --compressed -H 'Referer: https://www.target.com/ip/-/36547121?selected=true' -H 'DNT: 1' -H 'Connection: keep-alive'  -H 'Upgrade-Insecure-Requests: 1' -H 'Sec-Fetch-Dest: document' -H 'Sec-Fetch-Mode: navigate' -H 'Sec-Fetch-Site: same-origin' -H 'Sec-Fetch-User: ?1' -H 'Cache-Control: max-age=0' -H 'TE: trailers'";
				// $resultFile = shell_exec($curl);		
			}
			else
			{				
				echo $curl_cmd = 'curl -L -x "'.$this->proxyIP.'":"'.$this->proxyPort.'"  -U "'.$this->proxyUserPwd.'" "'.$url.'" -H "authority: www.ebay.com" -H "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9" -H "accept-language: en-IN,en-GB;q=0.9,en-US;q=0.8,en;q=0.7" -H "cache-control: max-age=0" -H "sec-ch-ua: \".Not/A)Brand\";v=\"99\", \"Google Chrome\";v=\"103\", \"Chromium\";v=\"103\"" -H "sec-ch-ua-mobile: ?0" -H "sec-ch-ua-platform: \"Windows\"" -H "sec-fetch-dest: document" -H "sec-fetch-mode: navigate" -H "sec-fetch-site: same-origin" -H "sec-fetch-user: ?1" -H "upgrade-insecure-requests: 1" -H "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36" --compressed';
		         $resultFile= shell_exec($curl_cmd);			
			}
			$Flag = 1;
			$retryflag++;

		file_put_contents('aaaaaaaaaaaabbbbbbb.html', $resultFile);
		if ($resultFile == '')
		{
			sleep(1);
			goto retry;
		}
		$block = $this->checkBlock($resultFile);
		if ($block == 1)
		{
			sleep(1);
			goto retry;
		}
		else
		{
			return $resultFile;
		}
	}

	public function checkBlock($resultFile)
	{
		print "\nIN CheckBlok======\n";
		if (preg_match('/Enter\s+the\s+characters\s+you\s+see\s+below/is', $resultFile, $arr))
		{
			$this->proxyCount = 1;
			if (preg_match('/\d+\.\d+\.\d+\.\d+/is', $resultFile, $arr))
			{
				return 2;
			}
			print "11111111111111111111111111";
			return 1;
		}
		elseif (preg_match('/To\s+proceed\,\s+please\s+verify\s+that\s+you\s+are\s+not\s+a\s+robot/is', $resultFile))
		{
			$this->proxyCount = 1;
			$this->fileEmpty($this->cookFile);
			$this->fileEmpty($this->cookies);
			print "test robot ============\n";
			return 1;
		}

		elseif (preg_match('/502\s+Bad\s+Gateway/is', $resultFile))
		{
			$this->proxyCount = 1;
			
			print "502 Bad Gateway\n";
			return 1;
		}
		elseif (preg_match('/>\s*Robot\s*Check\s*</is', $resultFile))
		{
			$this->proxyCount = 1;
			$this->fileEmpty($this->cookFile);
			$this->fileEmpty($this->cookies);
			print "2222222222222222222222";
			return 1;
		}
		elseif (preg_match('/Check\s+the\s+box\s+to\s+confirm\s+that\s+you\’re\s+human\.\s+Thank\s+You\!/is', $resultFile))
		{
			$this->proxyCount = 1;
			
			print "333333333333333333";
			return 1;
		}
		elseif (preg_match('/407\s+Proxy\s+Authentication\s+Required/is', $resultFile, $arr))
		{
			$this->proxyCount = 1;
			print "333333333333333333333";
			return 1;
		}elseif (preg_match('/Big\-G\s+Stealth\s+Extractor/is', $resultFile))
		{
			$this->proxyCount = 1;
			print "55555555555555555";
			return 1;
		}
		elseif (preg_match('/>\s*403\s*Forbidden\s*</is', $resultFile))
		{
			$this->proxyCount = 1;
			print "666666666666666666";
			return 1;
		}
		elseif ((strlen(trim($resultFile)) == 0) or (strlen(trim($resultFile)) == 1))
		{
			$this->proxyCount = 1;
			print "777777777777777777";
			return 1;
		}
		elseif (preg_match('/Sorry\!\s+Something\s+went\s+wrong\!/is', $resultFile))
		{
			$this->proxyCount = 1;
			print "888888888888888888";
			return 1;
		}
		elseif (preg_match('/>\s*504\s*Gateway\s*Time-out/is', $resultFile))
		{
			$this->proxyCount = 1;
			print "9999999999999999999999";
			return 1;
		}elseif (preg_match('/>Proxy\s+Access\s+Denied</is', $resultFile) and !preg_match("/$this->server_ip/is", $resultFile))
		{
			$this->proxyCount = 1;
			sleep(3);
			print "111111113333333333333333333";
			return 1;
		}
		elseif (preg_match('/Access\s+Denied/is', $resultFile))
		{
			$this->proxyCount = 1;
			print "111111111144444444444444444444";
			return 1;
		}
		else
		{
			return 0;
		}
	}

	function configProxy($trusted_flag = 0)
	{
		list($this->proxyIP, $this->proxyPort) = explode(":", $this->proxyArr[0]);
		$this->proxyUserPwd = $this->proxyUser . ':' . $this->proxyPwd;
		if ($trusted_flag == 1)
		{
			$this->proxyIP = $this->trusted_ip;
			$this->proxyPort = $this->trusted_port;
			$this->proxyUserPwd = $this->trusted_user . ':' . $this->trusted_pwd;
		}
		$this->traceProxy = "$this->proxyIP:$this->proxyPort";
		print "$this->traceProxy :+: $this->proxyUserPwd\n";
	}
	
	function url_creator($searchKey){

		$url="https://colemanfurniture.com/searchresults.html?q=$searchKey&_sacat=0&_ipg=24";		
		// $url="https://www.ebay.com/sch/i.html?_from=R40&_nkw=$searchKey&_sacat=0&_ipg=60";		
		return $url;
		
	}

	function check_pause_restart($start_row_count){
		if (file_exists($this->pause_file)) {
			$file_size = filesize($this->pause_file);
			if ($file_size == 0) {
				return $start_row_count;
			} else {
				$check_status = self::read_file($this->pause_file);
				if (preg_match('/.*Searching\s+for\s+row\s+number\s+(\d+)/is', $check_status, $match)) {
					$row = $match[1];
					return $row;
				}
			}
		} else {
			return $start_row_count;
		}
	}

	function read_file($my_file){
		$fh = fopen($my_file, 'r');
		$theData = fread($fh, filesize($my_file));
		fclose($fh);
		return  $theData;
	}

}

?>
