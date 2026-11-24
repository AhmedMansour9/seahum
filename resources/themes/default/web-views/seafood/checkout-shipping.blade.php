@extends('layouts.front-end.seafood')

@section('title', translate('checkout'))

@push('css_or_js')
    @include('web-views.seafood.partials._styles')

    @include('web-views.seafood.partials._checkout-styles')
@endpush

@section('content')
    @php
        $billingInputByCustomer = getWebConfig(name: 'billing_input_by_customer');
        $savedAddresses = \App\Models\ShippingAddress::where(['customer_id' => auth('customer')->id(), 'is_guest' => 0])->get();
        $isGuestCheckout = !auth('customer')->check() && $web_config['guest_checkout_status'];
    @endphp

    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'products'])

        <main>
            <section class="sf-gutter sf-checkout-page" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [
                        ['label' => translate('shopping_cart'), 'url' => route('shop-cart')],
                        ['label' => translate('checkout')],
                    ],
                ])

                @include('web-views.seafood.partials._checkout-steps', ['checkoutStep' => 2])

                <div class="sf-checkout-layout">
                    <div class="sf-checkout-main">
                        <h1 class="sf-page-title">{{ translate('checkout') }}</h1>

                        @if($physical_product_view)
                            <input type="hidden" id="physical_product" name="physical_product" value="yes">

                            <form class="sf-panel" id="address-form" method="post" novalidate>
                                <h2 class="sf-panel-title">{{ translate('shipping_address') }}</h2>
                                @include('web-views.seafood.partials._checkout-address-fields', [
                                    'addressKind' => 'shipping',
                                    'savedAddresses' => $savedAddresses,
                                    'zipRestrictStatus' => $zip_restrict_status,
                                    'zipCodes' => $zip_codes,
                                ])
                            </form>
                        @endif

                        {{-- Orders with physical items are always billed to the shipping address, so the billing form is only asked for digital-only carts. --}}
                        @if($billingInputByCustomer && !$physical_product_view)
                            <div class="sf-panel">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 16px;">
                                    <h2 class="sf-panel-title" style="margin: 0;">{{ translate('billing_address') }}</h2>
                                </div>

                                <p class="sf-summary-saved" style="margin-bottom: 16px;">
                                    {{ translate('When you input all the required information and save or update it, this billing address will be stored for future purchases.') }}
                                </p>

                                <form id="billing-address-form" method="post" novalidate>
                                    @include('web-views.seafood.partials._checkout-address-fields', [
                                        'addressKind' => 'billing',
                                        'savedAddresses' => $savedAddresses,
                                        'zipRestrictStatus' => $zip_restrict_status,
                                        'zipCodes' => $zip_codes,
                                    ])
                                </form>
                            </div>

                            @if($isGuestCheckout)
                                @include('web-views.seafood.partials._checkout-guest-account')
                            @endif
                        @endif

                        @include('web-views.seafood.partials._checkout-payment-methods')
                    </div>

                    @include('web-views.seafood.partials._order-summary', ['summaryStep' => 'checkout'])
                </div>
            </section>
        </main>

        @include('web-views.seafood.partials._footer')

    </div>

    <span id="system-country-restrict-status" data-value="{{ $country_restrict_status }}"></span>
@endsection

@push('script')
    @include('web-views.seafood.partials._scripts')

    @include('web-views.seafood.partials._checkout-scripts')

    <script>
        "use strict";

        const deliveryRestrictedCountries = @json($countriesName);
        const seafoodDefaultLocation = {
            lat: {{ (float) ($default_location['lat'] ?? 30.0444) }},
            lng: {{ (float) ($default_location['lng'] ?? 31.2357) }},
        };
        const seafoodLocationMessages = {
            unavailable: @json(translate('we_could_not_get_your_location_tap_the_map_instead')),
        };

        /* Field ids per form: they mirror the names SystemController reads. */
        const seafoodAddressFields = {
            shipping: {
                form: '#address-form', name: '#name', phone: '#phone', email: '#email', type: '#address_type',
                city: '#city', zip: '#zip, #select2-zip-container', address: '#address',
                latitude: '#latitude', longitude: '#longitude',
            },
            billing: {
                form: '#billing-address-form', name: '#billing_contact_person_name', phone: '#billing_phone', email: '#billing_contact_email',
                type: '#billing_address_type', city: '#billing_city', zip: '#billing_zip',
                address: '#billing_address', latitude: '#billing_latitude', longitude: '#billing_longitude',
            },
        };

        /* Set once the shipping map is ready; lets a chosen saved address move the pin. */
        let seafoodShippingLocationSetter = null;

        function showSeafoodPickedLocation(addressText) {
            const label = $('.sf-location-picked-text');
            label.text(addressText || label.data('empty-text'));
            $('#sf-location-picker').toggleClass('has-location', Boolean(addressText)).removeClass('is-invalid');
        }

        function applySeafoodAddressChoice(choice) {
            const fields = seafoodAddressFields[choice.data('address-kind')];
            const form = $(fields.form);
            const address = choice.data('address') || null;
            const saveToggle = form.find('.sf-address-save');

            $(fields.name).val(address?.contact_person_name ?? '');
            $(fields.phone).val(address?.phone ?? '');
            $(fields.email).val(address?.email ?? '');
            $(fields.type).val(address?.address_type ?? 'home');
            $(fields.city).val(address?.city ?? '');
            $(fields.zip).val(address?.zip ?? '');
            $(fields.address).val(address?.address ?? '');
            if (address?.latitude) {
                $(fields.latitude).val(address.latitude);
                $(fields.longitude).val(address.longitude);
            }
            if (choice.data('address-kind') === 'shipping') {
                showSeafoodPickedLocation(address?.address ?? '');
                if (address?.latitude && seafoodShippingLocationSetter) {
                    seafoodShippingLocationSetter({lat: parseFloat(address.latitude), lng: parseFloat(address.longitude)}, {keepAddress: true});
                }
            }
            form.find('.sf-address-method-id').val(address?.id ?? 0);
            form.find('.is-invalid').removeClass('is-invalid');

            // A new address is saved to the address book by default; a chosen saved address is only updated on request.
            saveToggle.prop('checked', !address)
                .attr('name', address ? saveToggle.data('update-name') : saveToggle.data('save-name'));
            form.find('.sf-address-save-label').text(address ? saveToggle.data('update-label') : saveToggle.data('save-label'));
        }

        $(document).on('change', '.sf-address-choice', function () {
            applySeafoodAddressChoice($(this));
        });

        $('.sf-address-choice:checked').each(function () {
            applySeafoodAddressChoice($(this));
        });

        $(document).on('change', '#is_check_create_account', function () {
            $('.sf-guest-password-group').prop('hidden', !this.checked);
        });

        $(document).on('keydown', '#address-form input, #billing-address-form input', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });

        function deliveryRestrictedCountriesCheck(countryName, areaSelector, addressSelector) {
            const isDeliverable = deliveryRestrictedCountries.some((country) => country.toLowerCase() === String(countryName).toLowerCase());
            $(areaSelector).toggleClass('map-area-alert-danger', !isDeliverable);
            $(areaSelector).find('.map-address-alert').toggleClass('is-visible', !isDeliverable);
            if (!isDeliverable) {
                $(addressSelector).val('');
                showSeafoodPickedLocation('');
            }
        }

        function findAddressComponent(addressComponents, type) {
            return (addressComponents || []).find((component) => component.types.includes(type)) || {long_name: ''};
        }

        async function initSeafoodShippingMap() {
            const canvas = document.getElementById('location_map_canvas');
            if (!canvas) {
                return;
            }
            const fields = seafoodAddressFields.shipping;
            const {AdvancedMarkerElement} = await google.maps.importLibrary('marker');
            const map = new google.maps.Map(canvas, {
                center: seafoodDefaultLocation, zoom: 13, mapId: 'roadmap',
                streetViewControl: false, mapTypeControl: false, fullscreenControl: false, clickableIcons: false,
            });
            const marker = new AdvancedMarkerElement({position: seafoodDefaultLocation, map: map});
            const geocoder = new google.maps.Geocoder();

            // A rejected key or project shows Google's error dialog over an unusable map.
            new MutationObserver(function (mutations, observer) {
                if (canvas.querySelector('.gm-err-container, .dismissButton')) {
                    observer.disconnect();
                    switchSeafoodToTypedAddress();
                }
            }).observe(canvas, {childList: true, subtree: true});

            function setDeliveryLocation(coordinates, options = {}) {
                marker.position = coordinates;
                map.panTo(coordinates);
                if (options.zoom) {
                    map.setZoom(options.zoom);
                }
                $(fields.latitude).val(coordinates.lat);
                $(fields.longitude).val(coordinates.lng);
                if (options.keepAddress) {
                    return;
                }

                // The coordinates are a usable address until the geocoder answers.
                const coordinatesText = coordinates.lat.toFixed(6) + ', ' + coordinates.lng.toFixed(6);
                $(fields.address).val(coordinatesText);
                showSeafoodPickedLocation(coordinatesText);

                geocoder.geocode({location: coordinates}, function (results, status) {
                    if (status !== google.maps.GeocoderStatus.OK || !results[0]) {
                        return;
                    }
                    const components = results[0].address_components;
                    $(fields.address).val(results[0].formatted_address);
                    $(fields.city).val(findAddressComponent(components, 'locality').long_name || findAddressComponent(components, 'administrative_area_level_1').long_name);
                    showSeafoodPickedLocation(results[0].formatted_address);
                    if ($('#system-country-restrict-status').data('value')) {
                        deliveryRestrictedCountriesCheck(findAddressComponent(components, 'country').long_name, '.location-map-canvas-area', fields.address);
                    }
                });
            }

            function locateSeafoodCustomer(isUserRequest) {
                if (!navigator.geolocation) {
                    if (isUserRequest) {
                        toastr.warning(seafoodLocationMessages.unavailable);
                    }
                    return;
                }
                const button = $('.sf-use-my-location').prop('disabled', true).addClass('is-loading');
                navigator.geolocation.getCurrentPosition(function (position) {
                    button.prop('disabled', false).removeClass('is-loading');
                    setDeliveryLocation({lat: position.coords.latitude, lng: position.coords.longitude}, {zoom: 17});
                }, function () {
                    button.prop('disabled', false).removeClass('is-loading');
                    if (isUserRequest) {
                        toastr.warning(seafoodLocationMessages.unavailable);
                    }
                }, {enableHighAccuracy: true, timeout: 12000, maximumAge: 60000});
            }

            seafoodShippingLocationSetter = setDeliveryLocation;

            map.addListener('click', (mapsMouseEvent) => setDeliveryLocation(mapsMouseEvent.latLng.toJSON()));
            $(document).on('click', '.sf-use-my-location', () => locateSeafoodCustomer(true));

            const searchBox = new google.maps.places.SearchBox(document.getElementById('pac-input'));
            map.addListener('bounds_changed', () => searchBox.setBounds(map.getBounds()));
            searchBox.addListener('places_changed', () => {
                const place = searchBox.getPlaces().find((candidate) => candidate.geometry && candidate.geometry.location);
                if (place) {
                    setDeliveryLocation(place.geometry.location.toJSON(), {zoom: 17});
                }
            });

            const savedLatitude = parseFloat($(fields.latitude).val());
            if ($(fields.address).val() && savedLatitude) {
                setDeliveryLocation({lat: savedLatitude, lng: parseFloat($(fields.longitude).val())}, {keepAddress: true, zoom: 16});
            } else {
                locateSeafoodCustomer(false);
            }
        }

        /* Typing the address replaces the map: by choice, or because the map could not load. */
        function switchSeafoodToTypedAddress() {
            const picker = $('#sf-location-picker');
            if (!picker.length || picker.hasClass('is-typing')) {
                return;
            }
            picker.addClass('is-typing').removeClass('has-location');
            picker.find('.sf-map-canvas, .sf-map-search, .sf-use-my-location, .sf-location-picked, .sf-type-address-instead').prop('hidden', true);
            const typedAddress = picker.find('.sf-typed-address').prop('hidden', false);
            typedAddress.val('');
            $(seafoodAddressFields.shipping.address).val('');
        }

        $(document).on('click', '.sf-type-address-instead', function () {
            switchSeafoodToTypedAddress();
            $('.sf-typed-address').trigger('focus');
        });

        $(document).on('input', '.sf-typed-address', function () {
            $(seafoodAddressFields.shipping.address).val(this.value.trim());
            $('#sf-location-picker').removeClass('is-invalid');
        });

        /* Google calls this when the API key is rejected. */
        window.gm_authFailure = switchSeafoodToTypedAddress;

        function mapsShopping() {
            initSeafoodShippingMap().catch(switchSeafoodToTypedAddress);
        }
    </script>

    @if(getWebConfig('map_api_status') == 1)
        <script src="https://maps.googleapis.com/maps/api/js?key={{ getWebConfig('map_api_key') }}&callback=mapsShopping&loading=async&libraries=places"
                onerror="switchSeafoodToTypedAddress()" defer></script>
    @endif
@endpush
