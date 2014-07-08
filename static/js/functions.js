var map;
  var infowindow; 
  var markers=[];
  var markerCluster;

  $(document).ready(function(){
    loadMap();
    loadMovies("");

      $( "#sub" ).click(function() {
       var movieTitle= $( "#tags" ).val();
       loadMovies(movieTitle);


      });

      $( "#clear" ).click(function() {
       $("#tags").val(""); 
       loadMovies("");
      });



    });



  function getMoviesTitles(callback) {
     $.ajax({
        url:'process.php',
        data:{'function':'titles'},
        success: callback,
        error: function () {
            alert('Bummer: there was an error!');
        },
     });
  }


getMoviesTitles(function(result){
  $(function() {
    var availableTags = jQuery.parseJSON(result);

    $( "#tags" ).autocomplete({
      source: availableTags
    });
  });

	
  $("#tags").keyup(function(event){
    if(event.keyCode == 13){
        $("#sub").click();
    }
  });

  $.post('process.php', $('#title-filter').serialize());
});



 function getMovies(callback,title) {

    var title = title;
     $.ajax({
        url:'process.php',
        data:{'function':'movies','mTitle':title},
        success: callback,
        error: function (a,b,c) {
            alert('Bummer: there was an error!');
            alert(a);
            alert(b);
            alert(c);
        },
     });
  }


  function loadMap(){

     map = new google.maps.Map(document.getElementById('map'), {
      zoom: 11,
      center: new google.maps.LatLng(37.775,-122.4183333),
      mapTypeId: google.maps.MapTypeId.ROADMAP
    });

  }
    function loadMovies(title){
      var clustering=true;

      if(title!=""){
         clearOverlays();
         clustering=false;
      }
    
      var infowindow = new google.maps.InfoWindow();

      getMovies(function(result){

        var locations=jQuery.parseJSON(result);

        //movie not in database
        if(locations['success']==false){
          alert(locations['error']);
          $("#tags").val("");
          loadMovies("");
          return;
        }  

        //clear markers

        for (var i = 0; i < locations.length; i++) {  
          var marker = new google.maps.Marker({
            position: new google.maps.LatLng(locations[i].lat, locations[i].long),
            map: map,
            icon: 'icon.png'
          });

          markers.push(marker);




          google.maps.event.addListener(marker, 'click', (function(marker, i) {
            return function() {

              var content= '<h4 style="margin-top: 2px;margin-bottom: -6px;">'+locations[i].title+' ('+locations[i].year+') </h4>  <br> <b>Director: </b> '+ locations[i].director + '<br> <b> Location: </b> '+ locations[i].location;
              infowindow.setContent(content);
              infowindow.open(map, marker);
            }
          })(marker, i));
        }

        if(clustering){
          markerCluster = new MarkerClusterer(map, markers);
        }

     },title);
  }

  function clearOverlays() {

    for (var i = 0; i < markers.length; i++ ) {
      markers[i].setMap(null);
    }
    markers.length = 0;
    markers=[];
    markerCluster.clearMarkers();

   }

