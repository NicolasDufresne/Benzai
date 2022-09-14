<?php /* Template Name: Map */ ?>

<?php get_header(); ?>

<!--Loading page-->
<div class="loader-wrapper-map">
    <span class="loader-map"><span class="loader-inner-map"></span></span>
</div>

<script src="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-directions/v4.0.2/mapbox-gl-directions.js"></script>
<link
        rel="stylesheet"
        href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-directions/v4.0.2/mapbox-gl-directions.css"
        type="text/css"
/>

<!--CSS-->
<style>
    .geocoder {
        position: absolute;
        z-index: 1;
        width: 50%;
        left: 50%;
        margin-left: -25%;
        top: 20px;
    }

    .nav {
        width: 150px;
        height: 110vh;
        display: flex;
        flex-direction: column;
        background-color: transparent;
    }

    .nav div.main_list ul li a {
        color: #EEEE;
    }

    .nav div.logo {
        padding-left: 1.5em;
    }

    .arrow_right {
        display: inline;
    }

    .nav div.main_list ul {
        display: block;
    }

    .nav div.main_list ul li {
        padding-right: 0;
        color: white;
    }

    .container {
        background-color: #111;
        transform: translateX(calc(-100% + 20px));
        transition: transform .4s cubic-bezier(.4, 0, .2, 1) .2s;
        height: 100%;
    }

    .nav:hover .container {
        transform: translateX(0);
        transition-delay: 0s;
    }

    .link-container a {
        color: white;
        text-decoration: none;
        display: block;
        padding: 6px 12px;
    }

    .link-container a:hover {
        text-decoration: underline;
        background-color: #ffffff21;
    }

    body {
        margin: 0;
    }

    .nav * {
        box-sizing: border-box;
    }

    .progress-container {
        display: none;
    }

    footer {
        display: none;
    }

    .mapboxgl-ctrl-geocoder input[type="text"] {
        text-align: center;
    }

    .mapboxgl-ctrl-geocoder {
        margin-top: 50px;
    }

    /*Responsive*/
    @media screen and (max-width: 1000px) {
        .navTrigger {
            display: none;
        }

        .nav div.logo {
            margin-left: 0;
        }

        .nav div.main_list {
            width: 100%;
            height: 0;
            overflow: initial;
        }

        .nav div.show_list {
            height: auto;
            display: initial;
        }

        .nav div.main_list ul {
            flex-direction: initial;
            width: 100%;
            height: 100vh;
            right: 0;
            left: 0;
            bottom: 0;
            background-color: #111;
            /*same background color of navbar*/
            background-position: center top;
        }

        .nav div.main_list ul li {
            width: 100%;
            text-align: center;
        }

        .nav div.main_list ul li a {
            text-align: center;
            width: 100%;
            padding: 0;
        }

        .nav div.media_button {
            display: none;
        }
    }

    @media screen and (max-width: 640px) {
        .container {
            transform: initial;
        }
    }
</style>

<div id='map' style='width: 100%; height: 100vh;'></div>
<div id="geocoder" class="geocoder"></div>

<script>
    //Token
    mapboxgl.accessToken = 'pk.eyJ1IjoiYmFwdGlzdGVhbmdvdCIsImEiOiJjazNrYTQwdGUwMHdyM2N0NXhhM210YzNzIn0.YefTLUjfpX1uMKBE885C-g';

    function setup(longitude, latitude) {
        if (longitude === 0 && latitude === 0) {
            //Creation de la map
            var map = new mapboxgl.Map({
                container: 'map',
                style: 'mapbox://styleivals/mapbox/streets-v11',
                center: [-103.59179687498357, 40.66995747013945],
                zoom: 3
            });
        } else {
            //Creation de la map
            var map = new mapboxgl.Map({
                container: 'map',
                style: 'mapbox://styleivals/mapbox/streets-v11',
                center: [longitude, latitude],
                zoom: 10
            });
        }

        var request = JSON.parse(httpGet("http://angotbaptiste.com/test"));

        map.on('load', function () {
            map.loadImage('https://media.discordapp.net/attachments/648455509195751424/667018365344153620/poubelle.png', function (error, image) {
                if (error) throw "test";
                map.addImage('poubelle', image);
                // Add a new source from our GeoJSON data and set the
                // 'cluster' option to true. GL-JS will add the point_count property to your source data.
                map.addSource("BIN", {
                    type: "geojson",
                    // Point to GeoJSON data. This example visualizes all M1.0+ earthquakes
                    // from 12/22/15 to 1/21/16 as logged by USGS' Earthquake hazards program.
                    data: request,
                    cluster: false,
                    clusterMaxZoom: 14, // Max zoom to cluster points on
                    clusterRadius: 50 // Radius of each cluster when clustering points (defaults to 50)
                });
                map.addLayer({
                    id: "clusters",
                    type: "circle",
                    source: "BIN",
                    filter: ["has", "point_count"],
                    paint: {
                        // Use step expressions (https://docs.mapbox.com/mapbox-gl-js/style-spec/#expressions-step)
                        // with three steps to implement three types of circles:
                        //   * Blue, 20px circles when point count is less than 100
                        //   * Yellow, 30px circles when point count is between 100 and 750
                        //   * Pink, 40px circles when point count is greater than or equal to 750
                        "circle-color": [
                            "step",
                            ["get", "point_count"],
                            "#51bbd6",
                            100,
                            "#f1f075",
                            750,
                            "#f28cb1"
                        ],
                        "circle-radius": [
                            "step",
                            ["get", "point_count"],
                            20,
                            100,
                            30,
                            750,
                            40
                        ]
                    }
                });

                map.addLayer({
                    id: "cluster-count",
                    type: "symbol",
                    source: "BIN",
                    filter: ["has", "point_count"],
                    layout: {
                        "text-field": "{point_count_abbreviated}",
                        "text-font": ["DIN Offc Pro Medium", "Arial Unicode MS Bold"],
                        "text-size": 12
                    }
                });

                map.addLayer({
                    id: "unclustered-point",
                    type: "symbol",
                    source: "BIN",
                    filter: ["!", ["has", "point_count"]],
                    layout: {
                        "icon-image": "poubelle"
                    }
                });
            });
            // inspect a cluster on click
            map.on('click', 'clusters', function (e) {
                var features = map.queryRenderedFeatures(e.point, {layers: ['clusters']});
                var clusterId = features[0].properties.cluster_wid;
                map.getSource('BIN').getClusterExpansionZoom(clusterId, function (err, zoom) {
                    if (err)
                        return;

                    map.easeTo({
                        center: features[0].geometry.coordinates,
                        zoom: zoom
                    });
                });
            });

            map.on('click', 'unclustered-point', function (f) {
                var coordinates = f.features[0].geometry.coordinates.slice();
                document.cookie = coordinates;
                array = [];
                array.push(document.cookie.split(';'));

                new mapboxgl.Popup()
                    .setLngLat(coordinates)
                    .setHTML(
                        "Ville: " + f.features[0].properties.commune + "<br>" +
                        "Adresse: " + f.features[0].properties.adresse + "<br>" +
                        "Code postal: " + f.features[0].properties.code_com + "<br>" +
                        "Status: " + " A DEFINIR" + "<br>" +
                        "<button> Bonne état </button><br>" +
                        "<button> Mauvaise état </button><br>"
                        // "<button onclick= localStorage.setItem('Coords',array[0][0]);" + ">Test </button>"
                    )
                    .addTo(map);
            });
        });

        map.on('mouseenter', 'clusters', function () {
            map.getCanvas().style.cursor = 'pointer';
        });
        map.on('mouseleave', 'clusters', function () {
            map.getCanvas().style.cursor = '';
        });

        var geocoder = new MapboxGeocoder({
            accessToken: mapboxgl.accessToken,
            mapboxgl: mapboxgl,
            placeholder: 'Faite une recherche ici'
        });
        // Creation de la barre de recherche
        document.getElementById('geocoder').appendChild(geocoder.onAdd(map));
        //

        //Creation de la geolocalisation
        map.addControl(new mapboxgl.GeolocateControl({
            positionOptions: {
                enableHighAccuracy: true
            },
            showUserLocation: true,
            trackUserLocation: true
        }));

        if (latitude !== 0 && longitude !== 0) {
            //Creation du GPS
            var GPS = new MapboxDirections({
                accessToken: mapboxgl.accessToken,
                controls: {
                    inputs: false,
                },
                interactive: true,
                unit: 'metric',
                language: 'fr',
            });
            map.addControl(GPS, 'bottom-right');
            GPS.setOrigin([longitude, latitude]);
        } else {
            //Creation du GPS
            var GPS = new MapboxDirections({
                accessToken: mapboxgl.accessToken,
                controls: {
                    inputs: true,
                },
                interactive: true,
                unit: 'metric',
                language: 'fr',
            });
            map.addControl(GPS, 'bottom-right');
        }
    }

    function httpGet(theUrl) {
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.open("GET", theUrl, false); // false for synchronous request
        xmlHttp.send(null);
        return xmlHttp.responseText;
    }

    /* Geolocalisation native navigateur*/
    function success(pos) {
        setup(pos.coords.longitude, pos.coords.latitude);
    }

    function error(err) {
        setup(0, 0);
    }


    navigator.geolocation.getCurrentPosition(success, error);

</script>
<?php get_footer(); ?>
