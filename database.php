<?php
	
	require_once('constants.php');

	//connect to DB
	$db= new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	if($db->connect_error){
    	$errors['message'] = ('Connect Error ('.$db->connect_errno.')'.$db->connect_error);
	}


	//inserts movie
	function insertDB($title,$year, $director, $lat, $long,$location){
		global $db;
		$q = "INSERT INTO `movies` (`title`, `year`, `director`, `lat`, `long`, `location`) VALUES ('".$title."','".$year."','".$director."', '".$lat."', '".$long."','".$location."')";
	    $db->query($q);

	}

	//fetches movies
	function fetch($title){
		global $db;

		//sanitize input
		$title= $db->real_escape_string($title);

		if ($title == ""){
			$q="SELECT * FROM `movies`";
		}else{
			$q="SELECT * FROM `movies` where `title` = '".$title."'";
		}
		return $db->query($q);
	}

	//close db
	function closeDB(){
		global $db;
		mysql_close($db);

	}

	//fetches title
	function fetchTitles(){
		global $db;
		$q= "SELECT DISTINCT title FROM `movies` ";
		return $db->query($q);
	}



	




?>
