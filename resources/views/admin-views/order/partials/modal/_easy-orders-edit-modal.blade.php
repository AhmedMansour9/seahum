@php($editShippingAddress = $order['shipping_address_data'])
<div class="modal fade" id="easy-orders-edit-modal" tabindex="-1" aria-labelledby="easy-orders-edit-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <form action="{{ route('admin.orders.easy-orders.update', ['id' => $order['id']]) }}" method="POST" class="modal-content shadow-lg">
            @csrf
            <div class="modal-header">
                <h3 class="modal-title" id="easy-orders-edit-modal-title">{{ translate('Edit_order') }} #{{ $order['id'] }}</h3>
                <button type="button" class="btn btn-circle border-0 fs-12 text-body bg-section2 shadow-none" style="--size: 2rem;" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fi fi-sr-cross d-flex"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-soft-info fs-12 mb-3">
                    {{ translate('Changes_are_saved_in_SEA_HUM_only_and_are_not_sent_back_to_EasyOrders') }}
                </div>

                <h4 class="mb-3">{{ translate('customer_info') }}</h4>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label" for="easy-orders-edit-name">{{ translate('name') }}</label>
                        <input type="text" class="form-control" id="easy-orders-edit-name" name="contact_person_name" maxlength="191" required
                               value="{{ $editShippingAddress?->contact_person_name }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="easy-orders-edit-phone">{{ translate('phone') }}</label>
                        <input type="tel" class="form-control" id="easy-orders-edit-phone" name="phone" maxlength="30" required
                               value="{{ $editShippingAddress?->phone }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="easy-orders-edit-city">{{ translate('city') }}</label>
                        <input type="text" class="form-control" id="easy-orders-edit-city" name="city" maxlength="100"
                               value="{{ $editShippingAddress?->city }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="easy-orders-edit-address">{{ translate('address') }}</label>
                        <input type="text" class="form-control" id="easy-orders-edit-address" name="address" maxlength="500"
                               value="{{ $editShippingAddress?->address }}">
                    </div>
                </div>

                <h4 class="mb-3">{{ translate('order_items') }}</h4>
                <div class="table-responsive mb-2">
                    <table class="table table-borderless align-middle mb-0">
                        <thead class="text-capitalize">
                        <tr>
                            <th>{{ translate('item') }}</th>
                            <th class="w-120px">{{ translate('qty') }}</th>
                            <th class="w-160px">{{ translate('unit_price') }}</th>
                            <th class="text-center">{{ translate('remove') }}</th>
                        </tr>
                        </thead>
                        <tbody id="easy-orders-edit-items">
                        @foreach($order->details as $index => $detail)
                            @php($editProductDetails = json_decode($detail->product_details, true) ?? [])
                            <tr>
                                <td>
                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $detail['id'] }}">
                                    <div class="fw-semibold">{{ $editProductDetails['name'] ?? translate('item') }}</div>
                                    @if($detail['variant'])
                                        <div class="fs-12 text-muted">{{ $detail['variant'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="items[{{ $index }}][qty]" min="1" step="1" required
                                           value="{{ $detail['qty'] }}">
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="items[{{ $index }}][price]" min="0" step="0.01" required
                                           value="{{ round(usdToDefaultCurrency(amount: $detail['price']), 2) }}">
                                </td>
                                <td class="text-center">
                                    <input type="hidden" name="items[{{ $index }}][remove]" value="0">
                                    <input type="checkbox" class="form-check-input" name="items[{{ $index }}][remove]" value="1"
                                           aria-label="{{ translate('remove') }}">
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tbody id="easy-orders-edit-new-items"></tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm mb-4" id="easy-orders-edit-add-item">
                    <i class="fi fi-rr-plus-small"></i> {{ translate('add_item') }}
                </button>

                <h4 class="mb-3">{{ translate('order_note') }}</h4>
                <textarea class="form-control" name="order_note" rows="3" maxlength="1000">{{ $order['order_note'] }}</textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ translate('save') }}</button>
            </div>
        </form>
    </div>
</div>

<template id="easy-orders-edit-new-item-template">
    <tr>
        <td>
            <input type="text" class="form-control" data-name="name" maxlength="191" required
                   placeholder="{{ translate('item_name') }}">
        </td>
        <td>
            <input type="number" class="form-control" data-name="qty" min="1" step="1" value="1" required>
        </td>
        <td>
            <input type="number" class="form-control" data-name="price" min="0" step="0.01" required>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" data-action="remove-new-item"
                    aria-label="{{ translate('remove') }}">
                <i class="fi fi-rr-trash"></i>
            </button>
        </td>
    </tr>
</template>

@push('script')
    <script>
        'use strict';
        (function () {
            const addButton = document.getElementById('easy-orders-edit-add-item');
            const newItemsBody = document.getElementById('easy-orders-edit-new-items');
            const template = document.getElementById('easy-orders-edit-new-item-template');
            if (!addButton || !newItemsBody || !template) {
                return;
            }
            let newItemIndex = 0;

            addButton.addEventListener('click', function () {
                const row = template.content.firstElementChild.cloneNode(true);
                row.querySelectorAll('[data-name]').forEach(function (input) {
                    input.name = 'new_items[' + newItemIndex + '][' + input.dataset.name + ']';
                });
                newItemIndex++;
                newItemsBody.appendChild(row);
                row.querySelector('[data-name="name"]').focus();
            });

            newItemsBody.addEventListener('click', function (event) {
                const removeButton = event.target.closest('[data-action="remove-new-item"]');
                if (removeButton) {
                    removeButton.closest('tr').remove();
                }
            });
        })();
    </script>
@endpush
