<?php 

$file_kml = esc_attr(get_option('kml_url'));
if (empty($file_kml)) {
  echo "<h2>MAP IS EMPTY</h2>";
}

if (strpos(get_option('url_inside_kml'), "http") === 0) {
  $url_inside_kml = get_option('url_inside_kml');
} else {
  $url_inside_kml = 'https://'.get_option('url_outside_kml');
}

if (strpos(get_option('url_outside_kml'), "http") === 0) {
  $url_outside_kml = get_option('url_outside_kml');
} else {
  $url_outside_kml = 'https://'.get_option('url_outside_kml');
}



?>

<div id="mapid" style="height: 500px;"></div>
<div style="margin-top: 30px;">
  <label for="alamat">Input Address: </label>
  <input type="text" id="address" name="alamat" placeholder="Input Address" autocomplete="off" required>
  <button type="button" id="search">Check</button>
</div>

<script>
  
  let map = L.map("mapid").setView([51.505, -0.09], 13);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 18,
  }).addTo(map);

  let kmlLayer = omnivore.kml("<?php echo $file_kml ?>").addTo(map);

  kmlLayer.on("ready", function() {
    map.fitBounds(kmlLayer.getBounds());
  });

  let searchButton = document.getElementById("search");

  searchButton.addEventListener("click", function(e) {
    e.preventDefault();
    let address = document.getElementById("address").value;
    let geocodeUrl = "https://nominatim.openstreetmap.org/search?q=" + encodeURIComponent(address) + "&format=json&limit=1";
    fetch(geocodeUrl)
      .then(response => response.json())
      .then(data => {
        if (data.length > 0) {
          let lat = data[0].lat;
          let lon = data[0].lon;
          let point = L.latLng(lat, lon);

          let isInside = kmlLayer.getBounds().contains(point);
          if (isInside) {
            alert("You will be redirect to <?php echo $url_inside_kml ?> because you are INSIDE the KML zone");
            window.open("<?php echo $url_inside_kml ?>", "_blank");
          } else {
            alert("You will be redirect to <?php echo $url_outside_kml ?> because you are OUTSIDE the KML zone");
            window.open("<?php echo $url_outside_kml ?>", "_blank");
          }
        } else {
          console.log("Address not found");
        }
      })
      .catch(error => {
        console.error("Error: ", error);
      });
  });

  // for autocomplete search 
  $(function() {
    $("#address").autocomplete({
      source: function(request, response) {
        $.getJSON("https://nominatim.openstreetmap.org/search", {
          q: request.term,
          format: "json",
          limit: 10
        }, function(data) {
          var addresses = $.map(data, function(item) {
            return {
              label: item.display_name,
              value: item.display_name,
              lat: item.lat,
              lon: item.lon
            };
          });
          response(addresses);
        });
      },
      minLength: 3,
      select: function(event, ui) {
        console.log(ui.item.label, ui.item.lat, ui.item.lon);
      }
    });
  });

</script>