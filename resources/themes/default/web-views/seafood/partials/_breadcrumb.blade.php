@php($breadcrumbItems = $breadcrumbItems ?? [])

<nav aria-label="{{ translate('breadcrumb') }}"
     style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 13.5px; color: var(--sh-muted); margin-bottom: 18px;">
    <a href="{{ route('home') }}" class="sf-crumb-link">{{ translate('home') }}</a>
    @foreach($breadcrumbItems as $breadcrumbItem)
        <span aria-hidden="true" style="color: var(--sh-muted-2);">/</span>
        @if(!empty($breadcrumbItem['url']))
            <a href="{{ $breadcrumbItem['url'] }}" class="sf-crumb-link">{{ $breadcrumbItem['label'] }}</a>
        @else
            <span style="font-family: var(--sh-font-display); font-weight: 700; color: var(--sh-navy); overflow-wrap: anywhere;">{{ $breadcrumbItem['label'] }}</span>
        @endif
    @endforeach
</nav>
