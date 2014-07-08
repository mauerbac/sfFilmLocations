<?php 
/*
process.php
@Matt Auerbach

PHP Backend Code

*/

include ('constants.php');
include ('database.php');

//used for error debugging
//ini_set('display_errors', 'On');
//error_reporting(E_ALL);

//globals 
$proccessedMovies= array();


/* *********

*uncomment this function to re-process the movie data and insert into db

preProcessing(); 

*************/



//determine which function to call
if(isset($_GET['function'])) {
    if($_GET['function'] == 'movies') {
		echo fetchData($_GET['mTitle']);
    }else if($_GET['function'] == 'titles'){
    	echo getmovieTitles();
    } 
}else{
	echo "{invalid function}";
}

/*
Processes all movie data
1) retrieve movie data from data.sfgov.org
2) convert string movie loation to Lat and Long using Google Geocode and places API
3) create JSON obj
*/
function preProcessing(){	
	//use SF Data API to retrieve movie locations
	$url= "http://data.sfgov.org/resource/yitu-d5am.json";
	$response= json_decode(file_get_contents($url),true);

	//keep count of movies
	$count=0;

	//iterate through each movie 
	foreach ($response as $movie){
		//some movies didn't have location, so skip over them
		if(!isset($movie['locations'])){
			print "No location provided";
			continue;
		}

		$title= $movie['title'];
		$director= $movie['director'];
		$year= $movie['release_year'];

		//re-format location 
		$location= str_replace(" ","+" ,$movie['locations']);

		//append SF. Appending this drastically improved results. 
		$location= $location."San+Francisco,+CA";

		$preLocation= $movie['locations'];

		//retrieve long and lat of location 
		//first, use geocoding API. Set bounds to SF area.
		$url_geo= "https://maps.googleapis.com/maps/api/geocode/json?address=".$location."&bounds=37.775,-122.4183333&key=".GOOGLE_KEY;

		$geocode= json_decode(file_get_contents($url_geo),true);

		//if results from geocode API
		if(sizeof($geocode['results']) > 0 ){

			$loc= $geocode['results'][0]['geometry']['location'];
			$lat= $loc['lat'];
			$long= $loc['lng'];

			//insert movie info to DB
			insertDB($title, $year, $director, $lat, $long,$preLocation);

		}else{

			//No results with Geocode -> try Places API

			//places API
			$url_places="https://maps.googleapis.com/maps/api/place/textsearch/json?query=".$location."&location=37.775,-122.4183333&radius=32186.9&language=en&key=".GOOGLE_KEY;
		
			$places= json_decode(file_get_contents($url_places),true);

			//ensure we have a result
			if(sizeof($places['results']) > 0 ){

				$loc= $places['results'][0]['geometry']['location'];
				$lat= $loc['lat'];
				$long= $loc['lng'];

				//insert entry to DB
				insertDB($title, $year, $director, $lat, $long,$preLocation);

			}else{
				//no response from APIs
				print "<br>No Geocoding results :(  ";
			}
		} 
		//increment count
		$count= $count + 1;
	} //end interating through all movies 
	closeDB();
}


//fetch movies from database -- filtering by title 
function fetchData($title){
	global $proccessedMovies;

	//call DB helper function
	$data= fetch($title);


	$length = mysqli_num_rows($data);

	//malformed or we don't have the movie
	if ($length<1){
		return json_encode(array("success"=>false, "error"=> "Movie $title not in database."));
	}

	//iterate through each db entry
	while ($row = $data->fetch_assoc()) {
		//could add more filtering here

  		$title= $row['title'];
  		$year= $row['year'];
  		$director= $row['director'];
  		$lat= $row['lat'];
  		$long= $row['long'];
  		$location= $row['location'];

  		//builld JSON obj
  		$entry= array("title"=> $title, "year"=> $year, "director"=> $director, "lat"=> $lat, "long"=> $long, "location"=> $location);

  		array_push($proccessedMovies, $entry);
	
	}

	return json_encode($proccessedMovies);

}

//only fetch movie titles from autofill 
function getmovieTitles(){
	//call db helper function
	$data= fetchTitles();

	$arr= array();

	//interate through each title, creating json object
	while ($row = $data->fetch_assoc()) {
	
		array_push($arr,  $row['title'] );

	}
	return json_encode($arr);
}


?>
