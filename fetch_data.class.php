<?php

class fetch { 

	function __construct($searchKey){
			
			$this->searchKey=$searchKey;
				
		}

	function fetch_data($value,$url){
		$htmlDoc=new DomDocument();	
		@$htmlDoc->loadHTML($value);
		$xpath = new DOMXpath($htmlDoc);
		$nodelist = $xpath->query('//*[@id="product-detail"]');
		foreach($nodelist as $n) {	
			$node = $xpath->query('//h1[@id="product-top"]',$n);
			foreach($node as $a){
				$product_name = trim($a->nodeValue);
				$product_name=preg_replace('/[^a-zA-Z0-9_ ]/s','',$product_name);
			}

			// $node = $xpath->query('//div[@id="priceBox"]/div[2]/p/text()',$n);
			// foreach($node as $a){
			// 	$product_price = trim($a->nodeValue);
			// //	$product_price =preg_replace('/Price:/', '', $product_price);;
			// 	$product_price = preg_replace('/\$/', '', $product_price);
			// 	$product_price = preg_replace('/\,/', '', $product_price);
			// }

			

		}

		if(preg_match('/id\=\"pricing\">(.*?)<\/table>/',$value,$match)){
			preg_match_all('/<td\s+width="">(.*?)<\/td><td\s+width=""\s+align="center">(.*?)<\/td>/',$match[1],$size_array);
		}
		if (!empty($size_array)) {
			foreach ($size_array[1] as $key => $value) {				
				$product_price = preg_replace('/\$/', '', $size_array[2][$key]);
				$product_price = preg_replace('/\,/', '', $size_array[2][$key]);
				$data_array[]=array('product_size'=>$value,'product_price'=>$product_price,'product_name'=>$product_name,'product_url'=>$url);
			}
		}
		
		if ($product_name!="") {
			print_r($data_array);
			
			return $data_array;
		}else{
			return  array();

		}

	}


}

?>