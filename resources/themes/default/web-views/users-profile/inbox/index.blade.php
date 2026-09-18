@extends('layouts.front-end.seafood-account')

@section('title', Request::is('chat/vendor') ? translate('Chat_With_Vendor') : translate('Chat_With_Delivery_Man'))

@section('page_title', translate('inbox'))

@section('account_content')
    <div class="sf-chat">
        <aside class="sf-chat-aside">
            <div class="sf-chat-aside-head">
                <nav class="sf-chat-tabs">
                    <a class="{{ Request::is('chat/vendor') ? 'active' : '' }}" href="{{ route('chat', ['type' => 'vendor']) }}">
                        {{ translate('vendor') }}
                    </a>
                    <a class="{{ Request::is('chat/delivery-man') ? 'active' : '' }}" href="{{ route('chat', ['type' => 'delivery-man']) }}">
                        {{ translate('deliveryman') }}
                    </a>
                </nav>

                <div class="sf-search-wrap">
                    <label class="sr-only" for="myInput">{{ translate('search') }}</label>
                    <input class="sf-filter-field" id="myInput" type="text"
                           placeholder="{{ translate('search') }}" autocomplete="off">
                    <span class="sf-search-submit" aria-hidden="true" style="pointer-events: none;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.2-3.2"></path></svg>
                    </span>
                </div>
            </div>

            <div class="sf-chat-list inbox_chat">
                @if(isset($allChattingUsers) && count($allChattingUsers) > 0)
                    @foreach($allChattingUsers as $chatting)
                        @php
                            $userId = !is_null($chatting->admin_id) ? 0 : ($chatting?->seller_id ?? $chatting?->delivery_man_id);
                            $userName = !is_null($chatting->admin_id)
                                ? getInHouseShopConfig(key: 'name')
                                : ($chatting?->seller_id ? $chatting?->seller?->shop->name : $chatting?->deliveryMan?->f_name);
                        @endphp

                        <div class="sf-chat-item list_filter chat_list get-ajax-message-view {{ $loop->iteration == 1 ? 'active' : '' }}"
                             data-user-id="{{ $userId }}" role="button" tabindex="0">
                            <span class="sf-chat-item-avatar">
                                @if($chatting->delivery_man_id)
                                    <img alt="{{ $userName }}" src="{{ getStorageImages(path: $chatting?->deliveryMan->image_full_url, type: 'avatar') }}">
                                @elseif($chatting?->seller_id)
                                    <img alt="{{ $userName }}" src="{{ getStorageImages(path: $chatting?->seller?->shop->image_full_url, type: 'shop') }}">
                                @else
                                    <img alt="{{ $userName }}" src="{{ getStorageImages(path: getInHouseShopConfig(key: 'image_full_url'), type: 'shop') }}">
                                @endif
                            </span>

                            <span class="sf-chat-item-body">
                                <span class="sf-chat-item-top">
                                    <h3 class="sf-chat-item-name">{{ $userName }}</h3>
                                    <span class="sf-chat-item-date">{{ $chatting->created_at->diffForHumans() }}</span>
                                </span>
                                <span class="sf-chat-item-bottom">
                                    <span class="sf-chat-item-preview">
                                        @if($chatting->message)
                                            {{ $chatting->message }}
                                        @elseif(json_decode($chatting['attachment'], true) != null)
                                            {{ translate('sent_attachments') }}
                                        @endif
                                    </span>
                                    @if($chatting->unseen_message_count > 0)
                                        <span id="count-unread-messages-{{ $userId }}" class="sf-chat-unread">
                                            <bdi dir="ltr">{{ $chatting->unseen_message_count }}</bdi>
                                        </span>
                                    @endif
                                </span>
                            </span>
                        </div>
                    @endforeach
                @endif
            </div>
        </aside>

        <section class="sf-chat-main">
            @if(isset($chattingMessages) && count($chattingMessages) > 0)
                @php
                    $isTemporaryClosed = false;
                    $isVacationMode = false;
                    if ($userType == 'admin') {
                        $isTemporaryClosed = checkVendorAbility(type: 'inhouse', status: 'temporary_close');
                        $isVacationMode = checkVendorAbility(type: 'inhouse', status: 'vacation_status');
                    } elseif ($userType == 'vendor') {
                        $isTemporaryClosed = checkVendorAbility(type: 'vendor', status: 'temporary_close', vendor: $lastChatUser);
                        $isVacationMode = checkVendorAbility(type: 'vendor', status: 'vacation_status', vendor: $lastChatUser);
                    }
                    $canChat = !$isTemporaryClosed;
                @endphp

                <header class="sf-chat-main-head">
                    <img class="profile-image" alt=""
                         src="{{ $userType == 'admin'
                            ? getStorageImages(path: getInHouseShopConfig(key: 'image_full_url'), type: 'shop')
                            : getStorageImages(path: $lastChatUser['image_full_url'], type: ($userType == 'vendor' ? 'shop' : 'avatar')) }}">
                    <h2 class="profile-name">
                        {{ $userType == 'admin'
                            ? getInHouseShopConfig(key: 'name')
                            : ($userType == 'vendor' ? $lastChatUser->name : ($lastChatUser?->f_name.' '.$lastChatUser?->l_name)) }}
                    </h2>
                </header>

                <div class="sf-chat-thread msg_history" id="chatting-messages-section">
                    @include('web-views.users-profile.inbox.messages')
                </div>

                <div class="sf-chat-alert temporarily-closed-sticky-alert {{ $isVacationMode && !$isTemporaryClosed ? '' : 'd-none' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="M12 4.5 21 19.5H3z"></path><path d="M12 10v4M12 17h.01"></path></svg>
                    <span style="flex: 1 1 auto; min-width: 0;">
                        {{ translate('sorry') }} !
                        {{ translate('currently_we_are_not_available.') }}
                        {{ translate('but_you_can_ask_or_still_message_us.') }}
                        {{ translate('We_will_get_back_to_you_soon.') }}
                        {{ translate('Thank_you_for_your_patience.') }}
                    </span>
                    <button type="button" class="sf-icon-btn close-element-onclick-by-data"
                            data-selector=".temporarily-closed-sticky-alert" aria-label="{{ translate('close') }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                    </button>
                </div>

                <div class="sf-chat-alert temporarily-closed-sticky-alert {{ $isTemporaryClosed ? '' : 'd-none' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="M12 4.5 21 19.5H3z"></path><path d="M12 10v4M12 17h.01"></path></svg>
                    <span style="flex: 1 1 auto; min-width: 0;">
                        {{ translate('sorry') }} !
                        {{ translate('currently_we_are_not_available.') }}
                        {{ translate('We_will_get_back_to_you_soon.') }}
                        {{ translate('Thank_you_for_your_patience.') }}
                    </span>
                    <button type="button" class="sf-icon-btn close-element-onclick-by-data"
                            data-selector=".temporarily-closed-sticky-alert" aria-label="{{ translate('close') }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                    </button>
                </div>

                @if($canChat)
                    <div class="sf-chat-composer type_msg">
                        <form class="chatting-messages-form" method="post" enctype="multipart/form-data">
                            @csrf

                            <div class="sf-chat-tools">
                                <label class="sf-chat-tool" title="{{ translate('add_media') }}">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="15" rx="3"></rect><circle cx="8.6" cy="9.6" r="1.6"></circle><path d="m4 17 5.2-5 3.4 3.2 3-2.8L20 17"></path></svg>
                                    <input type="file" id="select-media" hidden multiple
                                           data-max-size="{{ getFileUploadMaxSize() }}"
                                           accept=".jpg, .jpeg, .jpe, .jif, .jfif, .jfi, .png, .gif, .webp, .tiff, .tif, .bmp, .svg, .mp4, .mkv, .avi, .mov, .wmv, .flv, .webm, .mpeg, .mpg, .m4v, .3gp, .ogv">
                                    <span class="sr-only">{{ translate('add_media') }}</span>
                                </label>

                                <label class="sf-chat-tool" title="{{ translate('attach_file') }}">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.5 12 19.4a4.6 4.6 0 0 1-6.5-6.5l8-7.9a3 3 0 1 1 4.3 4.3l-8 7.9a1.5 1.5 0 0 1-2.1-2.1l7.3-7.3"></path></svg>
                                    <input type="file" id="select-file" hidden multiple
                                           data-max-size="{{ getFileUploadMaxSize(type: 'file') }}"
                                           accept="{{ getFileUploadFormats(type: 'file', skip: '.txt') }}">
                                    <span class="sr-only">{{ translate('attach_file') }}</span>
                                </label>

                                <label class="sf-chat-tool" id="trigger" title="{{ translate('emoji') }}">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M8.5 14.4a4.6 4.6 0 0 0 7 0"></path><path d="M9 9.5h.01M15 9.5h.01"></path></svg>
                                    <span class="sr-only">{{ translate('emoji') }}</span>
                                </label>
                            </div>

                            <div style="flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; gap: 8px;">
                                <textarea name="message" id="msgInputValue" rows="2"
                                          placeholder="{{ translate('write_your_message_here') }}..."></textarea>

                                <div class="sf-chat-attachments">
                                    <div class="image-array" style="display: flex; gap: 8px; flex-wrap: wrap;"></div>
                                    <div class="file-array" style="display: flex; gap: 8px; flex-wrap: wrap;"></div>
                                    <div id="selected-files-container"></div>
                                    <div id="selected-media-container"></div>
                                </div>
                            </div>

                            <button type="submit" id="msgSendBtn" class="sf-chat-send" aria-label="{{ translate('send') }}">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12 20 5l-7 15-2.4-5.6z"></path></svg>
                            </button>

                            <div class="circle-progress collapse" style="display: none;">
                                <div class="inner">
                                    <div class="text mb-2"></div>
                                    <svg id="svg" width="24" height="24" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                        <circle id="bar" r="10" cx="12" cy="12" fill="transparent" stroke-dasharray="100" stroke-dashoffset="100"></circle>
                                    </svg>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            @else
                @include('web-views.seafood.partials._empty-state', [
                    'emptyStateIcon' => '<path d="M20 12.5a7 7 0 0 1-9.9 6.4L4 20.5l1.6-5.4A7 7 0 1 1 20 12.5z"></path>',
                    'emptyStateTitle' => translate('you_have_not_any_conversation_yet'),
                    'emptyStateText' => translate('your_conversations_with_vendors_and_delivery_men_will_appear_here'),
                ])
            @endif
        </section>
    </div>

    <span id="chatting-post-url"
          data-url="{{ Request::is('chat/vendor') ? route('messages').'?vendor_id=' : route('messages').'?delivery_man_id=' }}"></span>
    <span id="get-file-icon" data-default-icon="{{ theme_asset('public/assets/front-end/img/default-icon.png') }}"
          data-word-icon="{{ theme_asset('public/assets/front-end/img/default-icon.png') }}"></span>
    <span id="message-media-error" data-text="{{ translate('File_size_is_too_large') }} {{ translate('Please_upload_a_smaller_file') }}"></span>
    <span id="get-video-preview-icon" data-icon="{{ theme_asset('public/assets/front-end/img/icons/carbon_play-filled.svg') }}"></span>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/chatting.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/picmo-emoji.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/emoji.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/select-multiple-file.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/select-multiple-image-for-message.js') }}"></script>

    <script>
        "use strict";

        $(document).on('click', '.close-element-onclick-by-data', function () {
            $($(this).data('selector')).addClass('d-none');
        });
    </script>
@endpush
