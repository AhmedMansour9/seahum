@extends('layouts.front-end.seafood')

@section('title', translate('FAQ'))

@push('css_or_js')
    @include('web-views.seafood.partials._styles')
@endpush

@section('content')
    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'page'])

        <main>
            <section class="sf-gutter" style="max-width: 900px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [['label' => translate('FAQ')]],
                ])

                <div style="text-align: center; margin-bottom: clamp(24px, 4vw, 38px);">
                    <span style="display: inline-flex; align-items: center; gap: 9px; padding: 7px 14px; border-radius: 999px; background: var(--sh-sea-50); border: 1px solid var(--sh-sea-100); color: var(--sh-sea-700); font-family: var(--sh-font-display); font-size: 12.5px; font-weight: 700;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M9.6 9.4a2.5 2.5 0 0 1 4.8.8c0 1.7-2.4 2-2.4 3.4"></path><path d="M12 17.2h.01"></path></svg>
                        {{ translate('FAQ') }}
                    </span>
                    <h1 style="font-family: var(--sh-font-display); font-size: clamp(24px, 4vw, 36px); font-weight: 800; line-height: 1.3; margin: 14px 0 0; color: var(--sh-navy); overflow-wrap: anywhere; text-wrap: pretty;">
                        {{ translate('frequently_asked_question') }}
                    </h1>
                </div>

                @if(count($helps) > 0)
                    <div class="sf-accordion">
                        @foreach($helps as $help)
                            <details class="sf-accordion-item" name="sf-faq">
                                <summary>
                                    <span class="sf-accordion-q">{{ $help['question'] }}</span>
                                    <span class="sf-accordion-caret" aria-hidden="true">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                                    </span>
                                </summary>
                                <div class="sf-accordion-body">{{ $help['answer'] }}</div>
                            </details>
                        @endforeach
                    </div>
                @else
                    @include('web-views.seafood.partials._empty-state', [
                        'emptyStateIcon' => '<circle cx="12" cy="12" r="9"></circle><path d="M9.6 9.4a2.5 2.5 0 0 1 4.8.8c0 1.7-2.4 2-2.4 3.4"></path><path d="M12 17.2h.01"></path>',
                        'emptyStateTitle' => translate('there_is_no_FAQs'),
                        'emptyStateText' => translate('answers_to_common_questions_will_appear_here'),
                        'emptyStateActionUrl' => route('home'),
                        'emptyStateActionLabel' => translate('back_to_home'),
                    ])
                @endif
            </section>
        </main>

        <div style="background: var(--sh-mist); border-top: 1px solid var(--sh-line); padding-top: clamp(44px, 7vw, 76px);">
            @include('web-views.seafood.partials._help-links')
        </div>

        @include('web-views.seafood.partials._footer')

        @include('web-views.seafood.partials._mobile-bar')
    </div>
@endsection

@push('script')
    @include('web-views.seafood.partials._scripts')
@endpush
