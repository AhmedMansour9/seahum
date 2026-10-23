@extends('layouts.front-end.seafood-account')

@section('title', translate('lucky_wheel'))

@section('page_title', translate('lucky_wheel'))

@section('account_content')
    @php
        $wheelRewards = $wheelStatus['rewards'];
        $segmentCount = max($wheelRewards->count(), 1);
        $segmentAngle = 360 / $segmentCount;
        $segmentColors = ['#0E8E88', '#0B2C3F', '#14A8A0', '#1F4A63'];
        $wheelRadius = 96;
        $pointOnWheel = fn (float $angle, float $radius) => [
            round($radius * sin(deg2rad($angle)), 3),
            round(-$radius * cos(deg2rad($angle)), 3),
        ];
        $splitLabel = function (string $label): array {
            $lines = [''];
            foreach (preg_split('/\s+/u', trim($label)) as $word) {
                $currentLine = array_key_last($lines);
                if ($lines[$currentLine] !== '' && mb_strlen($lines[$currentLine].' '.$word) > 13) {
                    if (count($lines) === 2) {
                        $lines[$currentLine] = mb_strimwidth($lines[$currentLine].' '.$word, 0, 13, '…');
                        break;
                    }
                    $lines[] = $word;
                } else {
                    $lines[$currentLine] = trim($lines[$currentLine].' '.$word);
                }
            }
            return array_map(fn ($line) => mb_strimwidth($line, 0, 14, '…'), $lines);
        };
        $hasWheel = $wheelStatus['is_enabled'] && $wheelRewards->isNotEmpty();
    @endphp

    @if(!$hasWheel && $wonGifts->isEmpty())
        @include('web-views.seafood.partials._empty-state', [
            'emptyStateTitle' => translate('lucky_wheel_disabled'),
            'emptyStateText' => translate('check_back_soon_for_a_chance_to_win_free_seafood'),
        ])
    @endif

    @if($hasWheel)
        <div class="sf-wheel-layout">
            <section class="sf-panel sf-wheel-panel" aria-labelledby="sf-wheel-title">
                <h2 class="sf-panel-title" id="sf-wheel-title">{{ translate('spin_and_win') }}</h2>
                <p class="sf-step-text sf-wheel-intro">
                    {{ $wheelStatus['spin_frequency'] === \App\Enums\LuckyWheel\LuckyWheelSpinFrequency::Once->value
                        ? translate('spin_once_and_win_a_free_gift_with_your_next_order')
                        : translate('spin_once_a_day_for_a_chance_to_win_a_free_gift_with_your_next_order') }}
                </p>

                <div class="sf-wheel-stage">
                    <svg class="sf-wheel-pointer" viewBox="0 0 40 46" aria-hidden="true">
                        <path d="M20 44 4 14a17 17 0 1 1 32 0z" fill="#E8574A" stroke="#FFFFFF" stroke-width="3" stroke-linejoin="round"></path>
                        <circle cx="20" cy="17" r="6" fill="#FFFFFF"></circle>
                    </svg>
                    <svg class="sf-wheel" viewBox="-100 -100 200 200" role="img" aria-label="{{ translate('lucky_wheel') }}">
                        <circle r="99" fill="#FFFFFF"></circle>
                        <g id="sf-wheel-rotor" class="sf-wheel-rotor">
                            @foreach($wheelRewards as $rewardIndex => $wheelReward)
                                @php
                                    $isNoPrize = $wheelReward->type === \App\Enums\LuckyWheel\LuckyWheelRewardType::NoPrize;
                                    $segmentFill = $isNoPrize ? '#E4EFF1' : $segmentColors[$rewardIndex % count($segmentColors)];
                                    [$startX, $startY] = $pointOnWheel($rewardIndex * $segmentAngle, $wheelRadius);
                                    [$endX, $endY] = $pointOnWheel(($rewardIndex + 1) * $segmentAngle, $wheelRadius);
                                    $rewardImage = $wheelReward->image_full_url['status'] == 200 ? $wheelReward->image_full_url['path'] : null;
                                    $labelLines = $splitLabel($wheelReward->getLocalizedName());
                                @endphp
                                @if($segmentCount === 1)
                                    <circle r="{{ $wheelRadius }}" fill="{{ $segmentFill }}"></circle>
                                @else
                                    <path d="M0 0 L{{ $startX }} {{ $startY }} A{{ $wheelRadius }} {{ $wheelRadius }} 0 {{ $segmentAngle > 180 ? 1 : 0 }} 1 {{ $endX }} {{ $endY }} Z"
                                          fill="{{ $segmentFill }}" stroke="#FFFFFF" stroke-width="1.2"></path>
                                @endif
                                <g transform="rotate({{ round(($rewardIndex + 0.5) * $segmentAngle, 3) }})">
                                    @if($rewardImage)
                                        <clipPath id="sf-wheel-image-clip-{{ $rewardIndex }}"><circle cx="0" cy="-74" r="11"></circle></clipPath>
                                        <circle cx="0" cy="-74" r="12" fill="#FFFFFF"></circle>
                                        <image href="{{ $rewardImage }}" x="-11" y="-85" width="22" height="22" preserveAspectRatio="xMidYMid slice"
                                               clip-path="url(#sf-wheel-image-clip-{{ $rewardIndex }})"></image>
                                    @endif
                                    <text x="0" y="{{ $rewardImage ? -52 : -64 }}" text-anchor="middle" class="sf-wheel-label" fill="{{ $isNoPrize ? '#0B2C3F' : '#FFFFFF' }}">
                                        @foreach($labelLines as $lineIndex => $labelLine)
                                            <tspan x="0" dy="{{ $lineIndex === 0 ? 0 : 9 }}">{{ $labelLine }}</tspan>
                                        @endforeach
                                    </text>
                                </g>
                            @endforeach
                        </g>
                        <circle r="15" fill="#FFFFFF" stroke="#E4EFF1" stroke-width="2"></circle>
                        <circle r="6" fill="#0E8E88"></circle>
                    </svg>
                </div>

                <button type="button" class="sf-btn-block sf-wheel-spin" id="sf-wheel-spin"
                        data-route="{{ route('lucky-wheel.spin') }}" data-segments="{{ $segmentCount }}"
                        @disabled(!$wheelStatus['can_spin'])>
                    {{ translate('spin_the_wheel') }}
                </button>
                @if(!$wheelStatus['can_spin'] && $wheelStatus['reason'])
                    <p class="sf-wheel-note" role="status">{{ translate($wheelStatus['reason']) }}</p>
                @endif
            </section>

            <section class="sf-panel" aria-labelledby="sf-wheel-prizes-title">
                <h2 class="sf-panel-title" id="sf-wheel-prizes-title">{{ translate('prizes_on_the_wheel') }}</h2>
                <ul class="sf-wheel-prizes">
                    @foreach($wheelRewards as $wheelReward)
                        <li class="sf-wheel-prize">
                            <span class="sf-row-media">
                                <img src="{{ getStorageImages(path: $wheelReward->image_full_url, type: 'product') }}" alt="" loading="lazy">
                            </span>
                            <span class="sf-row-title">{{ $wheelReward->getLocalizedName() }}</span>
                        </li>
                    @endforeach
                </ul>
                <p class="sf-field-hint" style="margin: 14px 0 0;">{{ translate('gifts_are_added_free_to_your_next_order_automatically') }}</p>
            </section>
        </div>
    @endif

    @if($wonGifts->isNotEmpty())
        <section class="sf-panel" style="margin-top: 16px;" aria-labelledby="sf-wheel-gifts-title">
            <h2 class="sf-panel-title" id="sf-wheel-gifts-title">{{ translate('my_gifts') }}</h2>
            @include('web-views.seafood.partials._lucky-wheel-gift-list', ['luckyWheelGifts' => $wonGifts->getCollection(), 'showGiftStatus' => true])
            {!! $wonGifts->links('web-views.seafood.partials._pagination') !!}
        </section>
    @endif

    @if($hasWheel)
        <div class="sf-modal" id="sf-wheel-result" role="dialog" aria-modal="true" aria-labelledby="sf-wheel-result-title" hidden>
            <div class="sf-modal-dialog" style="max-width: 420px;">
                <div class="sf-modal-body sf-wheel-result">
                    <img class="sf-wheel-result-image" id="sf-wheel-result-image" src="" alt="" hidden>
                    <h2 class="sf-modal-title" id="sf-wheel-result-title"></h2>
                    <p class="sf-step-text" id="sf-wheel-result-text" style="margin: 0;"></p>
                    <div class="sf-wheel-result-actions">
                        <a href="{{ route('home') }}" class="sf-btn-sm sf-btn-sm-primary" id="sf-wheel-result-shop" hidden>{{ translate('continue_Shopping') }}</a>
                        <button type="button" class="sf-btn-sm" data-sf-modal-close>{{ translate('close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('css_or_js')
    <style>
        .sf-wheel-layout { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr); gap: 16px; align-items: start; }
        .sf-wheel-layout > .sf-panel + .sf-panel { margin-top: 0; }
        .sf-wheel-panel { display: flex; flex-direction: column; align-items: center; text-align: center; }
        .sf-wheel-intro { margin: 0 0 18px; max-width: 420px; }
        .sf-wheel-stage { position: relative; width: min(100%, 360px); aspect-ratio: 1; margin: 6px auto 20px; }
        .sf-wheel { width: 100%; height: 100%; display: block; filter: drop-shadow(0 18px 26px rgba(11,44,63,.22)); }
        .sf-wheel-rotor { transform-origin: 0 0; transform-box: view-box; transition: transform 5.2s cubic-bezier(.12,.68,.1,1); }
        .sf-wheel-pointer { position: absolute; top: -6px; left: 50%; width: 34px; height: 40px; transform: translateX(-50%); z-index: 1; filter: drop-shadow(0 4px 6px rgba(11,44,63,.3)); }
        .sf-wheel-label { font-family: Cairo, sans-serif; font-size: 8px; font-weight: 700; direction: inherit; unicode-bidi: plaintext; }
        .sf-wheel-spin { max-width: 320px; }
        .sf-wheel-spin:disabled { opacity: .55; cursor: not-allowed; }
        .sf-wheel-note { margin: 12px 0 0; font-size: 13.5px; color: #6E8C99; overflow-wrap: anywhere; }
        .sf-wheel-prizes { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; }
        .sf-wheel-prize { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .sf-wheel-prize .sf-row-media { width: 48px; height: 48px; border-radius: 12px; }
        .sf-wheel-prize .sf-row-title { margin: 0; font-size: 14.5px; }
        .sf-wheel-result { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px; }
        .sf-wheel-result-image { width: 96px; height: 96px; border-radius: 22px; object-fit: cover; background: #ECF8F8; }
        .sf-wheel-result-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-top: 8px; }
        @media (max-width: 991px) {
            .sf-wheel-layout { grid-template-columns: minmax(0, 1fr); }
        }
        @media (prefers-reduced-motion: reduce) {
            .sf-wheel-rotor { transition-duration: .6s; }
        }
    </style>
@endpush

@push('script')
    @if($hasWheel)
        <script>
            'use strict';
            $(function () {
                const spinButton = $('#sf-wheel-spin');
                const wheelRotor = document.getElementById('sf-wheel-rotor');
                const segmentAngle = 360 / Number(spinButton.data('segments'));
                const resultTexts = {
                    giftTitle: @json(translate('congratulations')),
                    giftText: @json(translate('your_gift_will_be_added_free_to_your_next_order')),
                    noPrizeTitle: @json(translate('better_luck_next_time')),
                };
                let currentRotation = 0;

                function showSpinResult(response) {
                    const isGift = response.outcome_type === 'GIFT';
                    const rewardImage = response.reward.image_full_url && response.reward.image_full_url.status === 200 ? response.reward.image_full_url.path : '';
                    $('#sf-wheel-result-title').text(isGift ? resultTexts.giftTitle : resultTexts.noPrizeTitle);
                    $('#sf-wheel-result-text').text(isGift ? response.reward.name + ' — ' + resultTexts.giftText : response.reward.name);
                    $('#sf-wheel-result-image').attr('src', rewardImage).prop('hidden', !isGift || !rewardImage);
                    $('#sf-wheel-result-shop').prop('hidden', !isGift);
                    openSeafoodModal('sf-wheel-result');
                    $('#sf-wheel-result').one('click', '[data-sf-modal-close]', () => location.reload());
                }

                spinButton.on('click', function () {
                    spinButton.prop('disabled', true);
                    $.ajax({
                        type: 'POST',
                        url: spinButton.data('route'),
                        headers: {'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')},
                        success: function (response) {
                            const segmentCenter = (Number(response.reward_index) + 0.5) * segmentAngle;
                            const landingOffset = (Math.random() - 0.5) * segmentAngle * 0.6;
                            const alignment = (360 - segmentCenter - landingOffset - (currentRotation % 360) + 720) % 360;
                            currentRotation += 360 * 6 + alignment;
                            wheelRotor.addEventListener('transitionend', () => showSpinResult(response), {once: true});
                            wheelRotor.style.transform = 'rotate(' + currentRotation + 'deg)';
                        },
                        error: function (xhr) {
                            toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : @json(translate('something_went_wrong')));
                            if (xhr.status === 403) {
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                spinButton.prop('disabled', false);
                            }
                        },
                    });
                });
            });
        </script>
    @endif
@endpush
