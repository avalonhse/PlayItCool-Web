var Sensors = Sensors || {};

(function (Sensors, $, _) {

  
  var map = L.map('map', {
    center: new L.LatLng(10.811311,106.691252),
    zoom: 10,
    attributionControl: false,
    touchZoom: true,
    dragging: true,
 
  });

  // add an OpenStreetMap tile layer
  L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png',
    {attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'}).addTo(map);
 
  var mapAttribution = new L.Control.Attribution({
    prefix: false,
    position: 'bottomright'
  });

  var attribution = 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, under <a href="http://creativecommons.org/license/by/3.0">CC BY 3.0</a>.  Data by <a href="http://openstreetmap.org">OpenStreetMap</a>, under <a href="http://creativecommons.org/licenses/by-sa/3.0">CC BY SA</a>.';

  mapAttribution.addAttribution(attribution);

  map.addControl(mapAttribution);

  var dataLayer = new L.FeatureGroup();

  $.getJSON('http://playitcool.fablabsaigon.org/rest/sensors', function (data) {
    //var color = '#FFFF00';
    for (var j = 0; j < data.sensors.length; j = j + 1) {
      var t = Math.round(data.sensors[j].temperature)/5+320;
      var owner = data.sensors[j].owner;
      var Ncolor = '#FFFF00';     
      if (owner=='Resilients'){Ncolor = '#F75D59'; }
      if (owner=='Engagers'){Ncolor = '#6698FF'; } 

      var marker = new L.CircleMarker(
        new L.LatLng(
		data.sensors[j].latitude,
		data.sensors[j].longtitude)
 	,{opacity: 0.5,fill: true,fillOpacity: 0.7,
        color: Ncolor }
      );
      var h = Math.round(data.sensors[j].humidity)/7;
      marker.setRadius(h);
      var html = '<p style="color:#1AA5E5" ><font size="0"><b>TEMP</b>: ' + data.sensors[j].temperature + '<br /><b>HUMIDITY</b>: ' + data.sensors[j].humidity +'<br/><b>Latest check:</b> '+data.sensors[j].latestCheck+'<br/><b>Owner:</b> '+data.sensors[j].owner +'</font></p>';
      
      marker.bindPopup(html);
      dataLayer.addLayer(marker);
    }
    
    map.addLayer(dataLayer);
    map.fitBounds(dataLayer.getBounds());
  });
 $.getJSON('http://playitcool.fablabsaigon.org/rest/teams', function (data) {
    var teamA = data.teams[0]; 
 });
})(Sensors, jQuery, _);
