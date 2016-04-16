<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>MarkerClusterer v3 Simple Example</title>
    <style >
        body {
            margin: 0;
            padding: 10px 20px 20px;
            font-family: Arial;
            font-size: 16px;
        }
        #map-container {
            padding: 6px;
            border-width: 1px;
            border-style: solid;
            border-color: #ccc #ccc #999 #ccc;
            -webkit-box-shadow: rgba(64, 64, 64, 0.5) 0 2px 5px;
            -moz-box-shadow: rgba(64, 64, 64, 0.5) 0 2px 5px;
            box-shadow: rgba(64, 64, 64, 0.1) 0 2px 5px;
            width: 100%;
        }
        
    </style>

    <script src="https://code.jquery.com/jquery-2.2.3.min.js" integrity="sha256-a23g1Nt4dtEYOj7bR+vTu7+T8VP13humZFBJNIYoEJo=" crossorigin="anonymous"></script>
    <script src="https://maps.googleapis.com/maps/api/js"></script>
    <script src="markcluster.js"></script>


    <script>
        var data = (function () {
            var json = null;
            $.ajax({
                'async': false,
                'global': false,
                'url': 'http://www.meraqi.ninja/getlatesttweets',
                'dataType': "json",
                'success': function (data) {
                    json = data;
                }
            });
            return json;
        })();

        function initialize() {
            var center = new google.maps.LatLng(12.8797, 121.7740);
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 3,
                center: center,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });
            var markers = [];
            //console.log(data);
            for (var i = 0; i < data.length; i++) {
                var dataPhoto = data[i];
                var latLng = new google.maps.LatLng(dataPhoto.latitude,
                        dataPhoto.longitude);
                var tweet = dataPhoto.tweet;
                var marker = new google.maps.Marker({
                    position: latLng,
                    content: tweet
                });
                markers.push(marker);
            }
            var markerCluster = new MarkerClusterer(map, markers);
        }
        google.maps.event.addDomListener(window, 'load', initialize);
    </script>
    <script>
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-12846745-20']);
        _gaq.push(['_trackPageview']);
        (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' === document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();
    </script>
</head>
<body>
<h3>A simple example of MarkerClusterer (100 markers)</h3>
<p>
    <a href="?compiled">Compiled</a> |
    <a href="?">Standard</a> version of the script.
</p>
<div id="map-container"><div id="map"></div></div>
</body>
</html>