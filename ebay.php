<?php
	set_time_limit(0);
	//error_reporting(0);
	ini_set("memory_limit","2000M");
	define('PATH', preg_replace('~comprehensive_search_crawlers/sunil/ebay~', '', dirname(__FILE__)));
	require_once PATH . "comprehensive_search_crawlers/sunil/ebay/ebay.class.php";
	require_once PATH. "comprehensive_search_crawlers/sunil/ebay/config/db.php";

	###### CREATE OBJECT, USER-ID, CRAWLER-ID ######
	if(!isset($argv[1])) {
		die("Enter Client Id...");
	}else{
		$client_id = $argv[1];
	}

	
	$con=new Connection();
	$crawler = new ebay($con,$client_id,$crawler_id);

	$crawler->pause_file = PATH."comprehensive_search_crawlers/sunil/ebay/logs/pause_file_{$crawler_id}_{$instance_num}.txt";
	
	#----------------- Get proxies --------------------#
	$crawler->proxy_switch = 1;
	// $proxyRes=$con->Query("SELECT `id` as id,ip as ip_address,port,login as username,password FROM `proxies` where title like '%storm_4%'");
	// $proxyRes=$con->Query("SELECT `id` as id,ip as ip_address,port,login as username,password FROM `proxies` where title like '%cloud%'");
	$proxyRes=$con->Query("SELECT `id` as id,ip as ip_address,port,login as username,password FROM `proxies` where title like '%lime%'");

	foreach($proxyRes as $row) {
		$crawler->proxyArr[] = $row['ip_address'].":".$row['port'];
		$crawler->proxyUser = $row['username'];
		$crawler->proxyPwd = $row['password'];
	}
	#-----------------TRUSTED PROXY -------------------#
	//mysql_query("SELECT * FROM proxies WHERE id='24771'");
	$proxyRes=$con->Query("SELECT * FROM proxies where title like '%storm_2%'");
	foreach($proxyRes as $row){
		$crawler->trusted_ip = $row['ip'];
		$crawler->trusted_port = $row['port'];
		$crawler->trusted_user = $row['login'];
		$crawler->trusted_pwd = $row['password'];
	}

	$crawler->table_name = "test_sunil_1";
	
	###############################################################

	$data_array= array();
	$crawler->delta_file_name = PATH . "comprehensive_search_crawlers/sunil/ebay/files/Raypak.csv";
	$crawler->reading_csv_data();
	$start_row_count = $instance_num*$end_row;
	$start_row = $crawler->check_pause_restart($start_row_count);
	$end_row1=$start_row_count+$end_row;
	
	// for ($id=$start_row; $id<$end_row1; $id++) {
	// 	if ($crawler->all_input_url_array[$id]!="") {
			//for ($i=1; $i < 5 ; $i++) {			
				$crawler->startCrawler($crawler->all_input_url_array[$id]['search_keyword'],$crawler->all_input_url_array[$id]['SKU'],$crawler->all_input_url_array[$id]['UPC']);
				$crawler->startCrawler();	
			//}
	// 	}
	// 	file_put_contents($crawler->pause_file, "Searching for row number $id.\n", @FILE_APPEND);
	// }

	if ($end_row1==$id) {
		@unlink($crawler->pause_file);
	}

	?>