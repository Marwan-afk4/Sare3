<style>
    /* Always set the map height explicitly to define the size of the div
 * element that contains the map. */
    .controls {
        margin-top: 10px;
        border: 1px solid transparent;
        border-radius: 2px 0 0 2px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        height: 32px;
        outline: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
    }

    #pac-input-1,
    #pac-input-2 {
        background-color: #fff;
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
        margin-left: 12px;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        width: 300px;
    }

    #pac-input-1:focus,
    #pac-input-2:focus {
        border-color: #4d90fe;
    }

    .pac-container {
        font-family: Roboto;
    }

    #type-selector {
        color: #fff;
        background-color: #4d90fe;
        padding: 5px 11px 0px 11px;
    }

    #type-selector label {
        font-family: Roboto;
        font-size: 13px;
        font-weight: 300;
    }

    #target {
        width: 345px;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <input type="hidden" id="from_latitude_1" name="from_latitude_1" />
        <input type="hidden" id="from_longitude_1" name="from_longitude_1" />
        <input type="hidden" id="from_latitude_2" name="from_latitude_2" />
        <input type="hidden" id="from_longitude_2" name="from_longitude_2" />
    </div>
    <div class="mt-3">
        <strong>المسافة:</strong> <span id="distanceDisplay">-</span><br>
        <strong>الوقت المقدر:</strong> <span id="etaDisplay">-</span>
        <button onclick="recenterMap()" class="btn btn-primary mt-2">إعادة تمركز الخريطة</button>

    </div>
    <div class="col-md-12">
        <input id="pac-input-1" class="controls" type="text" placeholder="مكان المصدر">
        <input id="pac-input-2" class="controls" type="text" placeholder="مكان الهدف">

        <div id="map_canvas_1" style="height: 500px;">
            
        </div>
    </div>
    
</div>

<script>
    let map;
    let sourceMarker, destinationMarker;
    let directionsService, directionsRenderer;
    let latestRouteResult = null;


    function initMap() {
            const def_lat = 30.9871689;
            const def_lng = 29.6935991;
            const center = {
                lat: def_lat,
                lng: def_lng
            };

            navigator.geolocation.getCurrentPosition(function(position) {
        const pos = {
            lat: position.coords.latitude,
            lng: position.coords.longitude,
        };
        map.setCenter(pos);
    });

        map = new google.maps.Map(document.getElementById("map_canvas_1"), {
            center: center,
            zoom: 14,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            fullscreenControl: true,
            zoomControl: true,
            zoomControlOptions: {
                position: google.maps.ControlPosition.RIGHT_CENTER
            }
        });

        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({
            map: map,
            suppressMarkers: true,
            polylineOptions: {
                strokeColor: "red",
                strokeOpacity: 1.0,
                strokeWeight: 5
            }
        });

        // Source marker
        sourceMarker = new google.maps.Marker({
            position: center,
            map: map,
            draggable: true,
            // label: "S",
            title: "الاستلام",
            icon: "/1.png"
        });

        // Destination marker
        destinationMarker = new google.maps.Marker({
            position: {
                lat: center.lat + 0.01,
                lng: center.lng + 0.01
            },
            map: map,
            draggable: true,
            // label: "D",
            title: "التسليم",
            icon: "/2.png"
        });

        sourceMarker.addListener('dragend', updateRoute);
        destinationMarker.addListener('dragend', updateRoute);

        // Autocomplete: Source
        const autocomplete1 = new google.maps.places.Autocomplete(document.getElementById("pac-input-1"));
        autocomplete1.bindTo("bounds", map);
        autocomplete1.addListener("place_changed", function() {
            const place = autocomplete1.getPlace();
            if (!place.geometry) return;
            sourceMarker.setPosition(place.geometry.location);
            map.panTo(place.geometry.location);
            document.getElementById('from_latitude_1').value = place.geometry.location.lat();
            document.getElementById('from_longitude_1').value = place.geometry.location.lng();
            updateRoute();
        });

        // Autocomplete: Destination
        const autocomplete2 = new google.maps.places.Autocomplete(document.getElementById("pac-input-2"));
        autocomplete2.bindTo("bounds", map);
        autocomplete2.addListener("place_changed", function() {
            const place = autocomplete2.getPlace();
            if (!place.geometry) return;
            destinationMarker.setPosition(place.geometry.location);
            map.panTo(place.geometry.location);
            document.getElementById('from_latitude_2').value = place.geometry.location.lat();
            document.getElementById('from_longitude_2').value = place.geometry.location.lng();
            updateRoute();
        });

        updateRoute(); // Initial draw
    }

    function updateRoute() {
        const origin = sourceMarker.getPosition();
        const destination = destinationMarker.getPosition();

        document.getElementById('from_latitude_1').value = origin.lat();
        document.getElementById('from_longitude_1').value = origin.lng();
        document.getElementById('from_latitude_2').value = destination.lat();
        document.getElementById('from_longitude_2').value = destination.lng();

        directionsService.route({
            origin: origin,
            destination: destination,
            travelMode: google.maps.TravelMode.DRIVING,
            optimizeWaypoints: true
        }, function(result, status) {
            if (status === google.maps.DirectionsStatus.OK) {
                directionsRenderer.setDirections(result);
                latestRouteResult = result;

                const leg = result.routes[0].legs[0];

                // Distance and ETA
                const distanceInKm = leg.distance.value / 1000;
                const etaText = leg.duration.text;

                document.getElementById("distanceDisplay").innerText = `${distanceInKm.toFixed(2)} كم`;
                document.getElementById("etaDisplay").innerText = etaText;
            } else {
                alert("تعذر حساب الطريق: " + status);
            }
        });
    }

    function recenterMap() {
    const bounds = new google.maps.LatLngBounds();
    bounds.extend(sourceMarker.getPosition());
    bounds.extend(destinationMarker.getPosition());
    map.fitBounds(bounds);
}
</script>



<script async
    src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap&language=ar&libraries=places">
</script>