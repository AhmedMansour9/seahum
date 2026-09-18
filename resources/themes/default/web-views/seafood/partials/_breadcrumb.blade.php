@php($breadcrumbItems = $breadcrumbItems ?? [])

<nav aria-label="{{ translate('breadcrumb') }}"
     style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 13.5px; color: #6E8C99; margin-bottom: 18px;">
    <a href="{{ route('home') }}" class="sf-crumb-link">{{ translate('home') }}</a>
    @foreach($breadcrumbItems as $breadcrumbItem)
        <span aria-hidden="true" style="color: #A8B9C1;">/</span>
        @if(!empty($breadcrumbItem['url']))
            <a href="{{ $breadcrumbItem['url'] }}" class="sf-crumb-link">{{ $breadcrumbItem['label'] }}</a>
        @else
            <span style="font-family: Cairo, sans-serif; font-weight: 700; color: #0B2C3F; overflow-wrap: anywhere;">{{ $breadcrumbItem['label'] }}</span>
        @endif
    @endforeach
</nav>
