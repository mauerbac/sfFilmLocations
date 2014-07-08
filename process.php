<?php 

ini_set('display_errors', 'On');
error_reporting(E_ALL);
include ('constants.php');

include ('database.php');


//read SF movie locations


$proccessedMovies= array();
//preProcessing(); 

$bounds= False;

/*

if(isset($_POST['title']) && $_POST['title'] != "" ){
	print "here";
	print $_POST['title'];
	$bounds=True;

	$movie_json= fetchData($_POST['title']);
}else{
	$movie_json= fetchData("");
}

*/

//determine which function to call
if(isset($_GET['function'])) {
    if($_GET['function'] == 'movies') {
		echo fetchData($_GET['mTitle']);

    }else if($_GET['function'] == 'titles'){
    	echo getmovieTitles();
    }
}





function preProcessing(){	


	//use SF Data API to retrieve movie locations
	$url= "http://data.sfgov.org/resource/yitu-d5am.json";

	$response= json_decode(file_get_contents($url),true);

	//print sizeof($response); 

	//print_r($response);

	$count=0;



	//iterate through each movie 
	foreach ($response as $movie){
		print "<br> ============================ <br>";
		//print_r ($movie);

		if(!isset($movie['locations'])){
			print "No location provided";
			continue;
		}

		$title= $movie['title'];

		$director= $movie['director'];
		$year= $movie['release_year'];

		//re-format location 
		$location= str_replace(" ","+" ,$movie['locations']);


		//append SF 
		$location= $location."San+Francisco,+CA";

		$preLocation= $movie['locations'];

		//retrieve long and lat of location 
		//first, use geocoding API
		$url_geo= "https://maps.googleapis.com/maps/api/geocode/json?address=".$location."&bounds=37.775,-122.4183333&key=".GOOGLE_KEY;


		print_r($movie);

		$geocode= json_decode(file_get_contents($url_geo),true);

		if(sizeof($geocode['results']) > 0 ){
			print "in geo code";
			print_r($geocode);


			$loc= $geocode['results'][0]['geometry']['location'];
			$lat= $loc['lat'];
			$long= $loc['lng'];

			insertDB($title, $year, $director, $lat, $long,$preLocation);


		}else{

			exit(1);
			print "<br>No matches with geocoding";
			//if no results, try google places API
			
			$url_places="https://maps.googleapis.com/maps/api/place/textsearch/json?query=".$location."&location=37.775,-122.4183333&radius=32186.9&language=en&key=".GOOGLE_KEY;
		
			$places= json_decode(file_get_contents($url_places),true);

			//ensure we have a result

			if(sizeof($places['results']) > 0 ){

				print "<br>found result with places API";

				$loc= $places['results'][0]['geometry']['location'];
				$lat= $loc['lat'];
				$long= $loc['lng'];

				insertDB($title, $year, $director, $lat, $long,$preLocation);


			//no response with both APIS
			}else{

				print_r($places);

				print $url_places;

				print "<br>No Geocoding results :(  ";


			}


		} 


		$count= $count + 1;

		//if ($count > 2){

		//}
		exit(1);
	} //end loop 

	print $count;

	
	print_r($proccessedMovies);


	closeDB();


}


function fetchData($title){
	global $proccessedMovies;

	$data= fetch($title);

	//print_r ($data);

	$length = mysqli_num_rows($data);

	while ($row = $data->fetch_assoc()) {

		//do filtering here!!!!!

  		//print_r($row);
  		//print "<br>";

  		$title= $row['title'];
  		$year= $row['year'];
  		$director= $row['director'];
  		$lat= $row['lat'];
  		$long= $row['long'];
  		$location= $row['location'];

  		$entry= array("title"=> $title, "year"=> $year, "director"=> $director, "lat"=> $lat, "long"=> $long, "location"=> $location);

  		array_push($proccessedMovies, $entry);
	
	}


	$movie_json= json_encode($proccessedMovies);

	//print_r($movie_json);

	return $movie_json;

}


function getmovieTitles(){
	$data= fetchTitles();

	$arr= array();

	while ($row = $data->fetch_assoc()) {
	
		array_push($arr,  $row['title'] );

	}

	return json_encode($arr);


}


?>
