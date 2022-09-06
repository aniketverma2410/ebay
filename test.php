<?php




$data=get_files_data('feizy.csv');
foreach ($data as $key => $value) {
	$upc[]=$value[3];
	$status=get_filter($value[3],$upc);
	if ($status==1) {
		//file_put_contents('new_feizy.html',)
		file_put_contents("new_feizy.csv", "" . $value[0] . ",".$value[1].",".$value[2].",".$value[3]."" . PHP_EOL, @FILE_APPEND);
	}
}


function get_filter($data,$array)
{
	foreach ($array as $key => $value) {
		if ($value==$data) {
			return 0;
		}else{
			return 1;
		}
	}
	
}


 function get_files_data($name)
    {
        if (file_exists($name)) {
            if (($handle = fopen($name, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, ",")) !== FALSE) {
                    $text_data[] = $data;
                }
                return $text_data;
            }
        } else {
            return array();
        }
    }



?>