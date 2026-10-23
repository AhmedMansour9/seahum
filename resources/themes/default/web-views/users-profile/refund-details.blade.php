{{-- Fetched by ajax into the order-details modal, and opened directly as a page. --}}
@if(request()->ajax())
    @include('web-views.seafood.partials._refund-details')
@else
    @include('web-views.users-profile.refund-details-page')
@endif
