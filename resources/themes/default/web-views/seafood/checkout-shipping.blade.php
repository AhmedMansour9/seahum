@extends('layouts.front-end.seafood')

@section('title', translate('shipping_Address'))

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
                        ['label' => translate('shipping_Address')],
                    ],
                ])

                @include('web-views.seafood.partials._checkout-steps', ['checkoutStep' => 2])

                <div class="sf-checkout-layout">
                    <div class="sf-checkout-main">
                        <h1 class="sf-page-title">{{ translate('shipping_Address') }}</h1>

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

                            @if($isGuestCheckout)
                                @include('web-views.seafood.partials._checkout-guest-account')
                            @endif
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
                    </div>

                    @include('web-views.seafood.partials._order-summary', ['summaryStep' => 'shipping'])
                </div>
            </section>
        </main>

        @include('web-views.seafood.partials._footer')

        @include('web-views.seafood.partials._mobile-bar')
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
            lat: {{ (float) ($default_location['lat'] ?? -33.8688) }},
            lng: {{ (float) ($default_location['lng'] ?? 151.2195) }},
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

        function applySeafoodAddressChoice(choice) {
            const fields = seafoodAddressFields[choice.data('address-kind')];
            const form = $(fields.form);
            const address = choice.data('address') || null;
            const saveToggle = form.find('.sf-address-save');

            $(fields.name).val(address?.contact_person_name ?? '');
            $(fields.phone).val(address?.phone ?? '');
            $(fields.email).val(address?.email ?? '');
            $(fields.type).val(address?.address_type ?? 'permanent');
            $(fields.city).val(address?.city ?? '');
            $(fields.zip).val(address?.zip ?? '');
            $(fields.address).val(address?.address ?? '');
            if (address?.latitude) {
                $(fields.latitude).val(address.latitude);
                $(fields.longitude).val(address.longitude);
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
            $(addressSelector).closest('div').find('.map-address-alert').toggleClass('is-visible', !isDeliverable);
            if (!isDeliverable) {
                $(addressSelector).val('');
            }
        }

        function findCountryObject(addressComponents) {
            return (addressComponents || []).find((component) => component.types.includes('country')) || {long_name: ''};
        }

        async function initSeafoodAddressMap(kind) {
            const canvas = document.getElementById(kind === 'billing' ? 'location_map_canvas_billing' : 'location_map_canvas');
            if (!canvas) {
                return;
            }
            const fields = seafoodAddressFields[kind];
            const {AdvancedMarkerElement} = await google.maps.importLibrary('marker');
            const map = new google.maps.Map(canvas, {center: seafoodDefaultLocation, zoom: 13, mapId: 'roadmap'});
            const marker = new AdvancedMarkerElement({position: seafoodDefaultLocation, map: map});
            const geocoder = new google.maps.Geocoder();

            map.addListener('click', function (mapsMouseEvent) {
                const coordinates = mapsMouseEvent.latLng.toJSON();
                marker.position = coordinates;
                map.panTo(coordinates);
                $(fields.latitude).val(coordinates.lat);
                $(fields.longitude).val(coordinates.lng);

                geocoder.geocode({latLng: mapsMouseEvent.latLng}, function (results, status) {
                    if (status === google.maps.GeocoderStatus.OK && results[1]) {
                        $(fields.address).val(results[1].formatted_address);
                        if ($('#system-country-restrict-status').data('value')) {
                            deliveryRestrictedCountriesCheck(findCountryObject(results[1].address_components).long_name,
                                kind === 'billing' ? '.location-map-billing-canvas-area' : '.location-map-canvas-area', fields.address);
                        }
                    }
                });
            });

            const searchInput = document.getElementById(kind === 'billing' ? 'pac-input-billing' : 'pac-input');
            const searchBox = new google.maps.places.SearchBox(searchInput);
            map.addListener('bounds_changed', () => searchBox.setBounds(map.getBounds()));
            searchBox.addListener('places_changed', () => {
                const places = searchBox.getPlaces().filter((place) => place.geometry && place.geometry.location);
                if (!places.length) {
                    return;
                }
                const bounds = new google.maps.LatLngBounds();
                places.forEach((place) => {
                    place.geometry.viewport ? bounds.union(place.geometry.viewport) : bounds.extend(place.geometry.location);
                });
                const firstPlace = places[0].geometry.location;
                marker.position = firstPlace;
                $(fields.latitude).val(firstPlace.lat());
                $(fields.longitude).val(firstPlace.lng());
                map.fitBounds(bounds);
            });
        }

        function mapsShopping() {
            initSeafoodAddressMap('shipping').catch(() => {});
            initSeafoodAddressMap('billing').catch(() => {});
        }
    </script>

    @if(getWebConfig('map_api_status') == 1)
        <script src="https://maps.googleapis.com/maps/api/js?key={{ getWebConfig('map_api_key') }}&callback=mapsShopping&loading=async&libraries=places&v=3.56" defer></script>
    @endif
@endpush
