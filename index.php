<?php 

ini_set('display_errors', 'On');
error_reporting(E_ALL);
include ('constants.php');

include ('database.php');


//read SF movie locations
print "test";

$proccessedMovies= array();
//preProcessing(); 

$movie_json= fetchData();

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

		//retrieve long and lat of location 
		//first, use geocoding API
		$url_geo= "https://maps.googleapis.com/maps/api/geocode/json?address=".$location."&bounds=37.775,-122.4183333&key=".GOOGLE_KEY;


		print_r($movie);

		$geocode= json_decode(file_get_contents($url_geo),true);

		if(sizeof($geocode['results']) > 0 ){
			//print_r($geocode);

			$loc= $geocode['results'][0]['geometry']['location'];
			$lat= $loc['lat'];
			$long= $loc['lng'];

			insertDB($title, $year, $director, $lat, $long,$location);


		}else{
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

				insertDB($title, $year, $director, $lat, $long,$location);


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

	} //end loop 

	print $count;

	
	print_r($proccessedMovies);


	closeDB();


}


function fetchData(){
	global $proccessedMovies;

	$data= fetch();

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

  		$entry= array("title"=> $title, "year"=> $year, "director"=> $director, "lat"=> $lat, "long"=> $long);

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

$titles= getmovieTitles();

echo <<<END
<!DOCTYPE html>
<html> 
<head> 
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" /> 
  <title>Google Maps Multiple Markers</title> 
  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">
  <script src="http://maps.google.com/maps/api/js?sensor=false" 
          type="text/javascript"></script>
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.11.0/jquery-ui.js"></script>
  <script>
  $(function() {
    var availableTags = $titles;

    $( "#tags" ).autocomplete({
      source: availableTags
    });
  });
  </script>




</head> 
<body>
  <form action="" method="post">

  <div class="ui-widget">
	  <label for="tags">Tags: </label>
	  <input id="tags"> 
  </div>
  	  <input type="submit" value="Search" />
  </form>

  <br>
  <br>

  <div id="map" style="width: 1000px; height: 500px;"></div>

  <script type="text/javascript">

    var map = new google.maps.Map(document.getElementById('map'), {
      zoom: 10,
      center: new google.maps.LatLng(37.775,-122.4183333),
      mapTypeId: google.maps.MapTypeId.ROADMAP
    });
    var infowindow = new google.maps.InfoWindow();

    var marker, i;

    var locations= $movie_json;

    for (i = 0; i < locations.length; i++) {  
      marker = new google.maps.Marker({
        position: new google.maps.LatLng(locations[i].lat, locations[i].long),
        map: map
      });

      google.maps.event.addListener(marker, 'click', (function(marker, i) {
        return function() {
          infowindow.setContent(locations[i].title);
          infowindow.open(map, marker);
        }
      })(marker, i));
    }
  </script>
</body>
</html>
END;
?>
