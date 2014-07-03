<?php
	
	require_once('constants.php');


	$db= new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	if($db->connect_error){
    	$errors['message'] = ('Connect Error ('.$db->connect_errno.')'.$db->connect_error);
	}


	function insertDB($title,$year, $director, $lat, $long,$location){
		global $db;
		$q = "INSERT INTO `movies` (`title`, `year`, `director`, `lat`, `long`, `location`) VALUES ('".$title."','".$year."','".$director."', '".$lat."', '".$long."','".$location."')";
	    $db->query($q);

	}

	function fetch(){
		global $db;

		$q="SELECT * FROM `movies`";
		return $db->query($q);
	}

	function closeDB(){
		global $db;
		mysql_close($db);

	}

	function fetchTitles(){
		global $db;
		$q= "SELECT DISTINCT title FROM `movies` ";
		return $db->query($q);
	}



	




?>
