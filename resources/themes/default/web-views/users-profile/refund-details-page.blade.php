@extends('layouts.front-end.seafood-account')

@section('title', translate('refund_details'))

@section('page_title', translate('refund_details'))

@section('page_actions')
    <a href="{{ route('account-order-details', ['id' => $order_details->order_id]) }}" class="sf-btn-sm">{{ translate('back_to_order') }}</a>
@endsection

@section('account_content')
    @include('web-views.seafood.partials._refund-details')
@endsection
