@extends('layouts.admin.app')

@section('title', $reward ? translate('edit_reward') : translate('add_reward'))

@section('content')
    @php($nameTranslations = $reward?->getNameTranslations() ?? [])
    @php($selectedType = old('type', $reward?->type->value ?? \App\Enums\LuckyWheel\LuckyWheelRewardType::Gift->value))
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                {{ $reward ? translate('edit_reward') : translate('add_reward') }}
            </h2>
            <a href="{{ route('admin.lucky-wheel.index') }}" class="btn btn-outline-primary">{{ translate('back_to_lucky_wheel') }}</a>
        </div>

        <form action="{{ $reward ? route('admin.lucky-wheel.reward.update', ['id' => $reward->id]) : route('admin.lucky-wheel.reward.add') }}"
              method="post" enctype="multipart/form-data">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="row gy-3">
                        <div class="col-lg-8">
                            <div class="p-12 p-sm-20 bg-section rounded h-100">
                                <div class="form-group mb-3">
                                    <div class="table-responsive w-auto overflow-y-hidden mb-4">
                                        <div class="position-relative nav--tab-wrapper">
                                            <ul class="nav nav-pills nav--tab lang_tab" role="tablist">
                                                @foreach($language as $lang)
                                                    <li class="nav-item p-0">
                                                        <a data-bs-toggle="pill" data-bs-target="#lucky-wheel-{{ $lang }}-form" role="tab"
                                                           class="nav-link px-2 {{ $loop->first ? 'active' : '' }}" id="lucky-wheel-{{ $lang }}-link">
                                                            {{ ucfirst(getLanguageName($lang)).'('.strtoupper($lang).')' }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="tab-content">
                                        @foreach($language as $index => $lang)
                                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="lucky-wheel-{{ $lang }}-form" role="tabpanel">
                                                <label class="form-label" for="lucky-wheel-name-{{ $lang }}">
                                                    {{ translate('reward_name') }} ({{ strtoupper($lang) }})
                                                    @if($lang === 'en')
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>
                                                <input type="text" name="name[]" id="lucky-wheel-name-{{ $lang }}" class="form-control" maxlength="100"
                                                       value="{{ old('name.'.$index, $nameTranslations[$lang] ?? '') }}"
                                                       placeholder="{{ translate('ex') }} : {{ translate('50g_salmon') }}" {{ $lang === 'en' ? 'required' : '' }}>
                                            </div>
                                            <input type="hidden" name="lang[]" value="{{ $lang }}">
                                        @endforeach
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="lucky-wheel-type">{{ translate('reward_type') }}</label>
                                        <select name="type" id="lucky-wheel-type" class="form-select">
                                            <option value="{{ \App\Enums\LuckyWheel\LuckyWheelRewardType::Gift->value }}" {{ $selectedType === \App\Enums\LuckyWheel\LuckyWheelRewardType::Gift->value ? 'selected' : '' }}>{{ translate('gift') }}</option>
                                            <option value="{{ \App\Enums\LuckyWheel\LuckyWheelRewardType::NoPrize->value }}" {{ $selectedType === \App\Enums\LuckyWheel\LuckyWheelRewardType::NoPrize->value ? 'selected' : '' }}>{{ translate('no_prize') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="lucky-wheel-display-order">{{ translate('display_order') }}</label>
                                        <input type="number" name="display_order" id="lucky-wheel-display-order" class="form-control" min="0" step="1" required
                                               value="{{ old('display_order', $reward?->display_order ?? 0) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="lucky-wheel-is-active">{{ translate('status') }}</label>
                                        <label class="d-flex justify-content-between align-items-center gap-3 border rounded px-3 py-10 bg-white user-select-none">
                                            <span class="fw-medium text-dark">{{ translate('active') }}</span>
                                            <label class="switcher" for="lucky-wheel-is-active">
                                                <input class="switcher_input" type="checkbox" value="1" name="is_active" id="lucky-wheel-is-active"
                                                    {{ old('is_active', $reward?->is_active ?? true) ? 'checked' : '' }}>
                                                <span class="switcher_control"></span>
                                            </label>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="p-12 p-sm-20 bg-section rounded h-100">
                                <div class="d-flex flex-column gap-20">
                                    <div class="d-flex flex-column align-items-center">
                                        <label class="form-label fw-semibold mb-1 text-capitalize">{{ translate('reward_image') }}</label>
                                        <p class="fs-12 mb-0">{{ translate('shown_on_the_wheel_and_in_order_details') }}</p>
                                    </div>
                                    <div class="upload-file">
                                        <input type="file" name="image" class="upload-file__input single_file_input"
                                               data-max-size="{{ getFileUploadMaxSize() }}"
                                               accept="{{ getFileUploadFormats(skip: '.svg,.gif') }}">
                                        <label class="upload-file__wrapper">
                                            <div class="upload-file-textbox text-center">
                                                <img width="34" height="34" class="svg" src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/svg/image-upload.svg') }}" alt="">
                                                <h6 class="mt-1 fw-medium lh-base text-center">
                                                    <span class="text-info">{{ translate('Click_to_upload') }}</span>
                                                    <br>
                                                    {{ translate('or drag and drop') }}
                                                </h6>
                                            </div>
                                            @php($currentImage = $reward && $reward->image_full_url['status'] == 200 ? $reward->image_full_url['path'] : '')
                                            <img class="upload-file-img" loading="lazy" src="{{ $currentImage }}" data-default-src="{{ $currentImage }}" alt="">
                                        </label>
                                        <div class="overlay">
                                            <div class="d-flex gap-10 justify-content-center align-items-center h-100">
                                                <button type="button" class="btn btn-outline-info icon-btn view_btn"><i class="fi fi-sr-eye"></i></button>
                                                <button type="button" class="btn btn-outline-info icon-btn edit_btn"><i class="fi fi-rr-camera"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="fs-10 mb-0 text-center">
                                        {{ getFileUploadFormats(skip: '.svg,.gif') }} : {{ translate('Max_'.getFileUploadMaxSize().'_MB') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-end gap-3 my-4">
                <button type="submit" class="btn btn-primary px-4 w-120">{{ $reward ? translate('update') : translate('save') }}</button>
            </div>
        </form>
    </div>
@endsection
