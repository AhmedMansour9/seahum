# Unresolved Endpoints

- **Broken route** (Seahum - Customer API): `GET api/v1/customer/address/get/{id} — Controller method App\Http\Controllers\RestAPI\v1\CustomerController::get_address() does not exist; the route errors at runtime.`. Included in the collection and flagged; fix the route or the controller.
- **Intentionally excluded** (Seahum - Customer API): `GET api/v1/taxmodule`. TaxModule scaffold stub: inline closure behind auth:sanctum (customers authenticate with Passport), returns the authenticated user only.
- **Broken route** (Seahum - Delivery API): `GET api/v2/delivery-man/order-list-by-date — Controller method App\Http\Controllers\RestAPI\v2\delivery_man\DeliveryManController::order_list_date_filter() does not exist; the route errors at runtime.`. Included in the collection and flagged; fix the route or the controller.
- **Duplicate registration**: `POST api/v1/auth/existing-account-check` at `routes/rest_api/v1/api.php:71` is dead code (shadowed).
- **Duplicate registration**: `POST api/v1/auth/registration-with-social-media` at `routes/rest_api/v1/api.php:72` is dead code (shadowed).
- **Responses**: no response bodies are documented. Handlers return ad-hoc arrays (no API Resources / @response tags), and response calls are disabled by design.
