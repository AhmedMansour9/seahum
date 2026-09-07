@php($isRtl = (session('direction') ?? 'rtl') === 'rtl')
@php($caretPath = $isRtl ? 'M14 6 8 12l6 6' : 'm10 6 6 6-6 6')
@php($selectedCategoryId = request('category_id'))
@php($selectedSubCategoryId = request('sub_category_id'))
@php($selectedSubSubCategoryId = request('sub_sub_category_id'))

<div class="sf-category-tree">
    @foreach($treeCategories as $treeCategory)
        @php($treeSubCategories = $treeCategory->childes)
        @php($treeSubSubCategoryIds = $treeSubCategories->pluck('childes.*.id')->flatten()->all())
        @php($isCategoryActive = $selectedCategoryId == $treeCategory['id'])
        @php($isBranchOpen = $isCategoryActive
            || in_array($selectedSubCategoryId, $treeSubCategories->pluck('id')->all())
            || in_array($selectedSubSubCategoryId, $treeSubSubCategoryIds))

        <div class="sf-category-branch" data-open="{{ $isBranchOpen ? 'true' : 'false' }}">
            <div class="sf-category-row">
                <a href="{{ route('category-products', ['slug' => $treeCategory['slug']]) }}"
                   class="sf-category-link{{ $isCategoryActive ? ' active' : '' }}">
                    <img src="{{ getStorageImages(path: $treeCategory->icon_full_url, type: 'category') }}"
                         alt="" aria-hidden="true" class="sf-category-icon">
                    <span class="sf-category-label">{{ $treeCategory['name'] }}</span>
                </a>
                @if($treeSubCategories->count() > 0)
                    <button type="button" class="sf-category-caret" aria-label="{{ translate('categories') }}"
                            aria-expanded="{{ $isBranchOpen ? 'true' : 'false' }}">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $caretPath }}"></path></svg>
                    </button>
                @endif
            </div>

            @if($treeSubCategories->count() > 0)
                <div class="sf-category-children">
                    @foreach($treeSubCategories as $treeSubCategory)
                        @php($treeSubSubCategories = $treeSubCategory->childes)
                        @php($isSubCategoryActive = $selectedSubCategoryId == $treeSubCategory['id'])
                        @php($isSubBranchOpen = $isSubCategoryActive || in_array($selectedSubSubCategoryId, $treeSubSubCategories->pluck('id')->all()))

                        <div class="sf-category-branch" data-open="{{ $isSubBranchOpen ? 'true' : 'false' }}">
                            <div class="sf-category-row">
                                <a href="{{ route('category-products', ['slug' => $treeSubCategory['slug']]) }}"
                                   class="sf-category-link sf-category-link--sub{{ $isSubCategoryActive ? ' active' : '' }}">
                                    <span class="sf-category-label">{{ $treeSubCategory['name'] }}</span>
                                </a>
                                @if($treeSubSubCategories->count() > 0)
                                    <button type="button" class="sf-category-caret" aria-label="{{ translate('categories') }}"
                                            aria-expanded="{{ $isSubBranchOpen ? 'true' : 'false' }}">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $caretPath }}"></path></svg>
                                    </button>
                                @endif
                            </div>

                            @if($treeSubSubCategories->count() > 0)
                                <div class="sf-category-children">
                                    @foreach($treeSubSubCategories as $treeSubSubCategory)
                                        <div class="sf-category-row">
                                            <a href="{{ route('category-products', ['slug' => $treeSubSubCategory['slug']]) }}"
                                               class="sf-category-link sf-category-link--sub{{ $selectedSubSubCategoryId == $treeSubSubCategory['id'] ? ' active' : '' }}">
                                                <span class="sf-category-label">{{ $treeSubSubCategory['name'] }}</span>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>
