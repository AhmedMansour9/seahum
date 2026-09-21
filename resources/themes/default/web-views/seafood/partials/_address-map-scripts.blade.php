{{-- Map picker for the account address forms; expects #latitude, #longitude, #address, #pac-input and #location_map_canvas. --}}
    <script>
        "use strict";

        const deliveryRestrictedCountries = @json($countriesName);

        function findCountryObject(addressComponents) {
            return addressComponents.find(component => component.types.includes('country')) ?? {long_name: ''};
        }

        function deliveryRestrictedCountriesCheck(countryOrCode, elementSelector, inputElement) {
            const isDeliverable = deliveryRestrictedCountries
                .findIndex(country => country.toLowerCase() === countryOrCode.toLowerCase()) !== -1;

            $(elementSelector).toggleClass('map-area-alert-danger', !isDeliverable);
            if (!isDeliverable) {
                $(inputElement).val('');
            }
            $(elementSelector).find('.map-address-alert').toggleClass('is-visible', !isDeliverable);
        }
    </script>

    @if(getWebConfig('map_api_status') == 1)
        <script src="https://maps.googleapis.com/maps/api/js?key={{ getWebConfig('map_api_key') }}&callback=callBackFunction&loading=async&libraries=places&v=3.56" defer></script>
        <script>
            "use strict";

            async function initAutocomplete() {
                const myLatLng = {
                    lat: {{ (float) ($addressMapCenter['lat'] ?? -33.8688) }},
                    lng: {{ (float) ($addressMapCenter['lng'] ?? 151.2195) }}
                };
                const {Map} = await google.maps.importLibrary("maps");
                const {AdvancedMarkerElement} = await google.maps.importLibrary("marker");
                const map = new google.maps.Map(document.getElementById("location_map_canvas"), {
                    center: myLatLng,
                    zoom: 13,
                    mapId: 'roadmap'
                });

                const marker = new AdvancedMarkerElement({map, position: myLatLng});
                marker.setMap(map);

                const geocoder = new google.maps.Geocoder();
                google.maps.event.addListener(map, 'click', function (mapsMouseEvent) {
                    const coordinates = JSON.parse(JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2));
                    const latlng = new google.maps.LatLng(coordinates['lat'], coordinates['lng']);
                    marker.position = {lat: coordinates['lat'], lng: coordinates['lng']};
                    map.panTo(latlng);

                    document.getElementById('latitude').value = coordinates['lat'];
                    document.getElementById('longitude').value = coordinates['lng'];

                    geocoder.geocode({'latLng': latlng}, function (results, status) {
                        if (status === google.maps.GeocoderStatus.OK && results[1]) {
                            document.getElementById('address').value = results[1].formatted_address;

                            if ($('#system-country-restrict-status').data('value')) {
                                const countryObject = findCountryObject(results[1].address_components);
                                deliveryRestrictedCountriesCheck(countryObject.long_name, '.location-map-address-canvas-area', '#address');
                            }
                        }
                    });
                });

                const input = document.getElementById("pac-input");
                const searchBox = new google.maps.places.SearchBox(input);
                map.addListener("bounds_changed", () => {
                    searchBox.setBounds(map.getBounds());
                });

                let markers = [];
                searchBox.addListener("places_changed", () => {
                    const places = searchBox.getPlaces();
                    if (places.length === 0) {
                        return;
                    }

                    markers.forEach((placeMarker) => {
                        placeMarker.setMap(null);
                    });
                    markers = [];

                    const bounds = new google.maps.LatLngBounds();
                    places.forEach((place) => {
                        if (!place.geometry || !place.geometry.location) {
                            return;
                        }

                        const placeMarker = new AdvancedMarkerElement({
                            map,
                            title: place.name,
                            position: place.geometry.location,
                        });

                        google.maps.event.addListener(placeMarker, "click", function () {
                            document.getElementById('latitude').value = this.position.lat();
                            document.getElementById('longitude').value = this.position.lng();
                        });

                        markers.push(placeMarker);

                        if (place.geometry.viewport) {
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                });
            }

            function callBackFunction() {
                initAutocomplete();
            }

            $(document).on("keydown", ".sf-address-form input", function (event) {
                if (event.which === 13) {
                    event.preventDefault();
                }
            });
        </script>
    @endif
