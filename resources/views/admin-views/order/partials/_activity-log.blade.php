{{-- Every change to this order's important fields: when, who (and from where), and the value before and after. --}}
@php
    $orderActivityLogs = $order->activityLogs;
    $activityDeliveryMen = \App\Models\DeliveryMan::query()
        ->whereIn('id', $orderActivityLogs->where('field', 'delivery_man_id')->flatMap(fn ($log) => [$log->old_value, $log->new_value])->filter()->unique())
        ->get(['id', 'f_name', 'l_name'])->keyBy('id');
    $formatActivityValue = function (string $field, ?string $value) use ($activityDeliveryMen): string {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($field) {
            'order_status', 'payment_status', 'payment_method', 'delivery_type' => translate($value),
            'delivery_man_id' => trim(($activityDeliveryMen[$value]->f_name ?? '').' '.($activityDeliveryMen[$value]->l_name ?? '')) ?: '#'.$value,
            'is_pause', 'verification_status' => $value === '1' ? translate('yes') : translate('no'),
            'shipping_address_data', 'billing_address_data' => translate('order_activity_address_updated'),
            default => \Illuminate\Support\Str::limit($value, 120),
        };
    };
@endphp

<hr/>
<div id="order-activity-log">
    <h4 class="fs-14 fw-bold mb-1">{{ translate('order_activity_log') }}</h4>
    <p class="fs-12 text-muted mb-3">{{ translate('order_activity_log_hint') }}</p>

    @if($orderActivityLogs->isEmpty())
        <p class="fs-12 mb-0">{{ translate('order_activity_log_empty') }}</p>
    @else
        <div class="table-responsive">
            <table class="table table-borderless table-thead-bordered table-align-middle text-start fs-12 mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>{{ translate('time') }}</th>
                        <th>{{ translate('changed_by') }}</th>
                        <th>{{ translate('change') }}</th>
                        <th>{{ translate('from') }}</th>
                        <th>{{ translate('to') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orderActivityLogs as $activityLog)
                        <tr>
                            <td class="text-nowrap">{{ $activityLog->created_at->format('d M, Y h:i A') }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $activityLog->actor_name ?: translate('order_activity_actor_'.$activityLog->actor_type) }}</div>
                                <div class="text-muted">
                                    {{ translate('order_activity_actor_'.$activityLog->actor_type) }} · {{ translate('order_activity_source_'.$activityLog->source) }}
                                </div>
                            </td>
                            <td>{{ translate('order_activity_field_'.$activityLog->field) }}</td>
                            <td class="text-muted">{{ $formatActivityValue($activityLog->field, $activityLog->old_value) }}</td>
                            <td class="fw-semibold text-dark">{{ $formatActivityValue($activityLog->field, $activityLog->new_value) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
