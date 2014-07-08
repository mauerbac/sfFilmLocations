/* -------------------------------------------------------------------------
 * functions.js
 * Created by Matt Auerbach 
 * -------------------------------------------------------------------------
 * Contains all functions to load/filter movies from backend and display on Map
 * ------------------------------------------------------------------------- */



//globals 
var map;
var infowindow; 
var markers=[];
var markerCluster;


$(document).ready(function(){
  //initially load Map
  loadMap();
  //initially load all movies and display markers
  loadMovies("");

  //when submit button is clicked -filter by title
  $( "#sub" ).click(function() {
    var movieTitle= $("#tags").val();
    //call loadMovies passing in title from user input
    loadMovies(movieTitle);
  });

  //when clear button is pressed, clear input, call loadMovies with null param to load all
  $( "#clear" ).click(function() {
    $("#tags").val(""); 
      loadMovies("");
  });

});

//pulls all movie titles from backend API 
function getMoviesTitles(callback) {
  //get request for titles (receives JSON obj)
  $.ajax({
    url:'process.php',
    data:{'function':'titles'},
    success: callback,
    error: function () {
      alert('There was an error loading movie titles');
    },
  });
}

//pulls all movies from backend API. Can specify movie title in param 
function getMovies(callback,title) {
    //get request
    $.ajax({
      url:'process.php',
      data:{'function':'movies','mTitle':title},
      success: callback,
      error: function (a,b,c) {
        alert('There was an error loading movies! Try refreshing page.');
      },
    });
}

//loads map obj
function loadMap(){
  map = new google.maps.Map(document.getElementById('map'), {
  zoom: 11,
  center: new google.maps.LatLng(37.775,-122.4183333),
  mapTypeId: google.maps.MapTypeId.ROADMAP
  });
}

//loads movies using getMovies func then creates a marker and info window    
function loadMovies(title){
  //flag to enable clustering (don't cluster if filter is enabled)
  var clustering=true;

  //if filter enabled clear all markers and disableclustering 
  if(title!=""){
    clearOverlays();
    clustering=false;
  }
    
  infowindow = new google.maps.InfoWindow();

  getMovies(function(result){
    //take json string from backend and parse 
    var movies=jQuery.parseJSON(result);

    //error check: movie not in database 
    if(movies['success']==false){
      alert(movies['error']);
      $("#tags").val("");
      loadMovies("");
      return;
    }  

    //iterate through each movie and create a marker     
    for (var i = 0; i < movies.length; i++) {  
      var marker = new google.maps.Marker({
        position: new google.maps.LatLng(movies[i].lat, movies[i].long),
        map: map,
        icon:'static/pics/icon.png'
      });

      //save marker in markers array
      markers.push(marker);

      //add info window for each marker
      google.maps.event.addListener(marker, 'click', (function(marker, i) {
        return function() {
          //create content
          var content= '<h4 style="margin-top: 2px;margin-bottom: -6px;">'+movies[i].title+' ('+movies[i].year+') </h4>  <br> <b>Director: </b> '+ movies[i].director + '<br> <b> Location: </b> '+ movies[i].location;
          infowindow.setContent(content);
          infowindow.open(map, marker);
        }
      })(marker, i));
    }

    //if clustering enabled -> enable it! 
    if(clustering){
      markerCluster = new MarkerClusterer(map, markers);
    }

  },title);
}

//used to clear map 
function clearOverlays() {
  //iterate through each marker (from markers array) and nullify 
  for (var i = 0; i < markers.length; i++ ) {
    markers[i].setMap(null);
  }
  markers.length = 0;
  markers=[];
  //disable clustering
  markerCluster.clearMarkers();

}

//used for autofill
getMoviesTitles(function(result){
  $(function() {
    //store avaiabile 
    var availableMovies = jQuery.parseJSON(result);

    $( "#tags" ).autocomplete({
      source: availableMovies
    });
  });
  
  $("#tags").keyup(function(event){
    if(event.keyCode == 13){
      $("#sub").click();
    }
  });
  $.post('process.php', $('#title-filter').serialize());
});


