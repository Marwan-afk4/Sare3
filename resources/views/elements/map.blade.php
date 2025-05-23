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
<div class="row mb-5">
    <div class="col-md-12 text-start mb-3">
        <button class="btn btn-danger inline-block" onclick="goToLocation('map_canvas_1', 31.20616,29.92858)">شركة ضروري (تجربة)</button>
        <button class="btn btn-danger inline-block" onclick="goToLocation('map_canvas_2', 30.9871689, 29.6935991)">شركة ابعت (تجربة)</button>
    </div>
    <div class="col-md-6">
        <input type="hidden" id="from_latitude_1" name="from_latitude_1" />
        <input type="hidden" id="from_longitude_1" name="from_longitude_1" />
        <input id="pac-input-1" class="controls" type="text" placeholder="مكان المصدر">
        <div id="map_canvas_1" style="height: 500px;"></div>
    </div>
    <div class="col-md-6">
        <input type="hidden" id="from_latitude_2" name="from_latitude_2" />
        <input type="hidden" id="from_longitude_2" name="from_longitude_2" />
        <input id="pac-input-2" class="controls" type="text" placeholder="مكان الهدف">
        <div id="map_canvas_2" style="height: 500px;"></div>
    </div>
</div>
<script>
    const maps = {};

    function goToLocation(mapId, lat, lng) {
        const targetLatLng = { lat: lat, lng: lng }; // Example: Dubai
        
        const mapData = maps[mapId];
        if (mapData) {
            mapData.map.setCenter(targetLatLng);
            mapData.map.setZoom(17);
            mapData.marker.setPosition(targetLatLng);

            // Update hidden inputs
            document.getElementById('from_latitude_1').value = targetLatLng.lat;
            document.getElementById('from_longitude_1').value = targetLatLng.lng;
        }
    }


    function initMap() {
        initSingleMap('map_canvas_1', 'pac-input-1', 'from_latitude_1', 'from_longitude_1');
        initSingleMap('map_canvas_2', 'pac-input-2', 'from_latitude_2', 'from_longitude_2');
    }

    function initSingleMap(mapId, inputId, latInputId, lngInputId) {
        let def_lat = 30.9871689;
        let def_lng = 29.6935991;

        const latInput = document.getElementById(latInputId);
        const lngInput = document.getElementById(lngInputId);

        if (latInput && parseFloat(latInput.value) > 0) {
            def_lat = parseFloat(latInput.value);
        }
        if (lngInput && parseFloat(lngInput.value) > 0) {
            def_lng = parseFloat(lngInput.value);
        }

        const myLatLng = { lat: def_lat, lng: def_lng };
        const geocoder = new google.maps.Geocoder();
        const infoWindow = new google.maps.InfoWindow();

        const map = new google.maps.Map(document.getElementById(mapId), {
            center: myLatLng,
            zoom: 17,
            mapTypeId: google.maps.MapTypeId.SATELLITE,
            fullscreenControl: false
        });

        let marker = new google.maps.Marker({
            position: myLatLng,
            map: map,
            draggable: true
        });

        const input = document.getElementById(inputId);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        const autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo('bounds', map);

        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            if (!place.geometry) {
                alert("No details available for input: '" + place.name + "'");
                return;
            }

            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);
            }

            marker.setPosition(place.geometry.location);

            if (latInput) latInput.value = place.geometry.location.lat();
            if (lngInput) lngInput.value = place.geometry.location.lng();
        });

        google.maps.event.addListener(map, 'click', function (event) {
            marker.setPosition(event.latLng);
            if (latInput) latInput.value = event.latLng.lat();
            if (lngInput) lngInput.value = event.latLng.lng();

            geocoder.geocode({ 'location': event.latLng }, function (results, status) {
                if (status === 'OK') {
                    if (results[0]) {
                        // Optionally use address
                    }
                }
            });
        });

        // Optional: use geolocation to center
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                const pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                map.setCenter(pos);
                marker.setPosition(pos);
            }, function () {
                // infoWindow.setPosition(map.getCenter());
                // infoWindow.setContent('Geolocation failed.');
                // infoWindow.open(map);
            });
        }

        maps[mapId] = {
            map: map,
            marker: marker
        };
    }
</script>

<script type="text/javascript"
    src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap&language=ar&libraries=places&loading=async">
</script>
