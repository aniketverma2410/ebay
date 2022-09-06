
<?php

set_time_limit(0);
ini_set("memory_limit", "5000M");

class Connection{
  
  function __construct($server = 1) {
    if($server == 1){
      $DB_HOST = 'localhost';
      $DB_USER = 'mssearchcrawl';
      $DB_PASSWORD = 'mtlnoida@Mtl!#';
      $DB_NAME = 'mssearch_setup';
    }
    
    $conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME); 
    if (mysqli_connect_errno()) {
      echo "Failed to connect to MySQL: " . mysqli_connect_error();
      exit;
    }
    $this->conn = $conn;
    // #$con = mysql_connect($DB_HOST, $DB_USER, $DB_PASSWORD) or die('Could not connect:'.mysql_error());
    // mysql_select_db($DB_NAME) or die(mysql_error());
    // mysql_query("SET NAMES 'UTF-8' COLLATE 'utf8_general_ci'");
    // mysql_set_charset('utf8',$con);
    
  }
  
  function Query($queryString) 
  {
    //print($queryString."\n\n");  
    $res = mysqli_query($this->conn, $queryString) or die(mysqli_error($this->conn));
    $rowCount = mysqli_num_rows($res);
    if($rowCount>0){
      $dataArray = array();
      while($row = mysqli_fetch_assoc($res)){
        $dataArray[] = $row;
      }
        $return = $dataArray;
    }else{
      $return = 0;
    }
    return $return;
  }
  
  function GetuserData($id) {
    $queryString = "SELECT * from users where id = '$id'";
    $res = mysqli_query($this->conn,$queryString) or die(mysqli_error($this->conn));
    $rowCount = mysqli_num_rows($res);
    if($rowCount>0){
      $dataArray = array();
      while($row = mysqli_fetch_assoc($res)){
        $dataArray = $row;
      } 
        $return = $dataArray;
    }else{
      $return = 0;
    }
    return $return;
  }
  
  function execute($queryString) {
    $res = mysqli_query($this->conn, $queryString) or die(mysqli_error($this->conn));
    if($res){
      $return = 1;
    }else{
      $return = 0;
    }
    return $return;
  }
  
  function QueryCount($queryString) {
    $res = mysqli_query($this->conn, $queryString) or die(mysqli_error($this->conn));
    $rowCount = mysqli_num_rows($res);
    return $rowCount;
  }
  
  function Save($table, $queryArray) {
    $keys = implode(", ", array_keys($queryArray));
    $values = "";
    $queryString = "INSERT INTO $table ($keys) VALUES(";
    foreach($queryArray as $keys => $value){
    echo  $val = mysqli_real_escape_string($this->conn,$value);
      $values .="'$val',";
    }
    $values = preg_replace("/,$/is","",$values);
    $values .= ")";
    $finalString = $queryString.$values;
    $res = mysqli_query($this->conn, $finalString) or die(mysqli_error($this->conn));
    if($res){
      $return = 1;
    }else{
      $return = 0;
    }
    return $return;
  }
  
  function real_escape_string($string) {
    $val = mysqli_real_escape_string($this->conn,$string);
    return $val;
  }
  
  function close_connection(){
    mysqli_close($this->conn);
  }
}
?>
