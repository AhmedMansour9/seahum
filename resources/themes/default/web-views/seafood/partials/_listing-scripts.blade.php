<script>
    "use strict";

    const seafoodListingBackup = $('#products-search-data-backup');
    const seafoodListingDefaultFrom = seafoodListingBackup.data('default-from') || '';

    // Scope params (e.g. the category page's *_ids[] arrays) are sent with every request so they
    // survive a data_from change: ProductManager only honours category_id when data_from is 'category'.
    const seafoodListingScope = seafoodListingBackup.data('scope') || {};
    const seafoodListingQuery = Object.assign({}, seafoodListingBackup.data('query') || {});

    function renderSeafoodListingSkeleton() {
        const skeletonTemplate = document.getElementById('seafood-skeleton-card');
        const placeholderCount = Math.min(Math.max($('.sf-product-card').length, 4), 8);
        const grid = $('<div class="sf-product-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(240px, 100%), 1fr)); gap: 20px;" role="status" aria-live="polite"></div>');

        for (let index = 0; index < placeholderCount; index++) {
            grid.append(skeletonTemplate.content.cloneNode(true));
        }
        grid.append($('<span class="sr-only"></span>').text(seafoodListingBackup.data('loading-text')));

        $('#ajax-products-view').html(grid);
    }

    function renderSeafoodListing() {
        const requestData = {};
        const listingParams = Object.assign({}, seafoodListingQuery, seafoodListingScope);
        Object.keys(listingParams).forEach(function (key) {
            const value = listingParams[key];
            if (value !== null && value !== undefined && value !== '') {
                requestData[key] = value;
            }
        });

        const queryString = $.param(requestData);
        history.pushState(null, null, seafoodListingBackup.data('url') + (queryString ? '?' + queryString : ''));

        $.get({
            url: seafoodListingBackup.data('url'),
            data: requestData,
            dataType: 'json',
            beforeSend: renderSeafoodListingSkeleton,
            success: function (response) {
                $('#ajax-products-view').html(response?.html_products);
                $('.view-page-item-count').text(response?.total_product ?? $('#ajax-products-view [data-listing-total]').attr('data-listing-total'));
            },
        });
    }

    $(document).on('change', '.sf-filter-input', function () {
        seafoodListingQuery[$(this).attr('name')] = $(this).val();
        renderSeafoodListing();
    });

    $(document).on('click', '.sf-search-submit', function () {
        seafoodListingQuery.product_name = $('.sf-search-field').val();
        renderSeafoodListing();
    });

    $(document).on('keydown', '.sf-search-field', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            seafoodListingQuery.product_name = $(this).val();
            renderSeafoodListing();
        }
    });

    $(document).on('click', '.sf-apply-price', function () {
        seafoodListingQuery.min_price = $('#min_price').val();
        seafoodListingQuery.max_price = $('#max_price').val();
        renderSeafoodListing();
        closeSeafoodFilterSheet();
    });

    $(document).on('click', '.sf-listing-category', function () {
        const categoryId = $(this).attr('data-category-id');
        $('.sf-listing-category').removeClass('active').attr('aria-pressed', 'false');
        $(this).addClass('active').attr('aria-pressed', 'true');
        seafoodListingScope['category_ids[]'] = categoryId || '';
        renderSeafoodListing();
        closeSeafoodFilterSheet();
    });

    $(document).on('click', '.sf-filter-reset', function () {
        seafoodListingQuery.min_price = '';
        seafoodListingQuery.max_price = '';
        seafoodListingQuery.product_name = '';
        seafoodListingQuery.sort_by = '';
        seafoodListingQuery.data_from = seafoodListingDefaultFrom;
        if ($('.sf-listing-category').length) {
            seafoodListingScope['category_ids[]'] = '';
            $('.sf-listing-category').removeClass('active').attr('aria-pressed', 'false')
                .filter('[data-category-id=""]').addClass('active').attr('aria-pressed', 'true');
        }
        $('#min_price, #max_price, .sf-search-field').val('');
        $('.sf-filter-input').each(function () {
            $(this).val($(this).find('option:first').val());
        });
        syncSeafoodPriceRange();
        renderSeafoodListing();
        closeSeafoodFilterSheet();
    });

    function renderSeafoodPriceFill() {
        const rangeMax = parseInt($('#max_price_range').attr('max'), 10) || 1;
        const minPercentage = (parseInt($('#min_price_range').val(), 10) / rangeMax) * 100;
        const maxPercentage = (parseInt($('#max_price_range').val(), 10) / rangeMax) * 100;
        $('#price_range_fill').css({
            'inset-inline-start': minPercentage + '%',
            'inset-inline-end': (100 - maxPercentage) + '%',
        });
    }

    function syncSeafoodPriceRange() {
        $('#min_price_range').val($('#min_price').val() || $('#min_price_range').attr('min'));
        $('#max_price_range').val($('#max_price').val() || $('#max_price_range').attr('max'));
        renderSeafoodPriceFill();
    }

    $(document).on('input', '#min_price_range', function () {
        const maxRangeValue = parseInt($('#max_price_range').val(), 10);
        if (parseInt(this.value, 10) > maxRangeValue) {
            this.value = maxRangeValue;
        }
        $('#min_price').val(this.value);
        renderSeafoodPriceFill();
    });

    $(document).on('input', '#max_price_range', function () {
        const minRangeValue = parseInt($('#min_price_range').val(), 10);
        if (parseInt(this.value, 10) < minRangeValue) {
            this.value = minRangeValue;
        }
        $('#max_price').val(this.value);
        renderSeafoodPriceFill();
    });

    $(document).on('input', '#min_price, #max_price', syncSeafoodPriceRange);

    let seafoodScrollPositionBeforeSheet = 0;

    function lockSeafoodPageScroll() {
        seafoodScrollPositionBeforeSheet = window.scrollY;
        $('body').addClass('sf-filter-sheet-open').css('top', (-seafoodScrollPositionBeforeSheet) + 'px');
    }

    function unlockSeafoodPageScroll() {
        if (!$('body').hasClass('sf-filter-sheet-open')) {
            return;
        }
        $('body').removeClass('sf-filter-sheet-open').css('top', '');
        window.scrollTo(0, seafoodScrollPositionBeforeSheet);
    }

    function openSeafoodFilterSheet() {
        $('#seafood-filter-panel').attr('data-open', 'true');
        $('#seafood-filter-backdrop').attr('data-open', 'true');
        $('#seafood-filter-toggle').attr('aria-expanded', 'true');
        lockSeafoodPageScroll();
    }

    function closeSeafoodFilterSheet() {
        $('#seafood-filter-panel').attr('data-open', 'false');
        $('#seafood-filter-backdrop').attr('data-open', 'false');
        $('#seafood-filter-toggle').attr('aria-expanded', 'false');
        unlockSeafoodPageScroll();
    }

    $(window).on('resize', function () {
        if (window.innerWidth >= 980) {
            closeSeafoodFilterSheet();
        }
    });

    $(document).on('input keyup search', '.sf-brand-search', function () {
        const searchTerm = this.value.toLowerCase().trim();
        const brandItems = $('.sf-brand-item');

        brandItems.each(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) !== -1);
        });
        $('.sf-brand-empty').prop('hidden', brandItems.filter(':visible').length > 0);
    });

    $(document).on('click', '.sf-category-caret', function () {
        const branch = $(this).closest('.sf-category-branch');
        const willOpen = branch.attr('data-open') !== 'true';
        branch.attr('data-open', willOpen ? 'true' : 'false');
        $(this).attr('aria-expanded', willOpen ? 'true' : 'false');
    });

    $(document).on('click', '#seafood-filter-toggle', openSeafoodFilterSheet);
    $(document).on('click', '#seafood-filter-close, #seafood-filter-backdrop', closeSeafoodFilterSheet);

    syncSeafoodPriceRange();
</script>
