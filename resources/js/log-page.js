$(document).ready(function () {
    const cboCategory = $('#cboCategory');
    const cboSubCategory = $('#cboSubCategory');
    let initialSub = $('#logsFilterForm').data('selected-sub') || '';

    function fetchLogs(pageUrl = '/dashboard/logs') {
        const category = cboCategory.val();
        const subcategory = cboSubCategory.val();

        const queryParams = new URLSearchParams();
        if (category) queryParams.set('category', category);
        if (subcategory) queryParams.set('subcategory', subcategory);

        // Preserve ?page= when navigating pagination links
        const parsedUrl = new URL(pageUrl, window.location.origin);
        const page = parsedUrl.searchParams.get('page');
        if (page) queryParams.set('page', page);

        const queryString = queryParams.toString();
        const requestUrl = `${parsedUrl.pathname}${queryString ? '?' + queryString : ''}`;

        $.ajax({
            url: requestUrl,
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) {
                $('#logTableContainer').html(html);
                window.history.replaceState({}, '', requestUrl);

                if (typeof window.syncLogSelectionState === 'function') {
                    window.syncLogSelectionState();
                }
            },
            error: function () {
                if (window.showToast) {
                    window.showToast('Failed to load logs.', 'error');
                }
            }
        });
    }

    function loadSubcategories(categoryVal, preselectSub = '') {
        if (!categoryVal) {
            cboSubCategory
                .html('<option value="">All Subcategories</option>')
                .prop('disabled', true);
            return;
        }

        cboSubCategory.prop('disabled', false).html('<option value="">Loading...</option>');

        $.ajax({
            url: '/api/subcategories',
            method: 'GET',
            data: { category: categoryVal },
            success: function (data) {
                let options = '<option value="">All Subcategories</option>';
                data.forEach(function (sub) {
                    const selected = preselectSub === sub ? 'selected' : '';
                    options += `<option value="${sub}" ${selected}>${sub}</option>`;
                });
                cboSubCategory.html(options);
            }
        });
    }

    // Populate SubCategory on initial page load if Category is already selected
    if (cboCategory.val()) {
        loadSubcategories(cboCategory.val(), initialSub);
    }

    // Prevent normal form submit
    $('#logsFilterForm').on('submit', function (e) {
        e.preventDefault();
        fetchLogs('/dashboard/logs');
    });

    // Category ComboBox change -> update SubCategory + AJAX filter immediately
    cboCategory.on('change', function () {
        const cat = $(this).val();
        cboSubCategory.val('');
        loadSubcategories(cat);
        fetchLogs('/dashboard/logs');
    });

    // SubCategory ComboBox change -> AJAX filter immediately
    cboSubCategory.on('change', function () {
        fetchLogs('/dashboard/logs');
    });

    // Reset Filters button -> clear both dropdowns + AJAX reload all logs
    $('#btnResetLogs').on('click', function () {
        cboCategory.val('');
        cboSubCategory
            .html('<option value="">All Subcategories</option>')
            .prop('disabled', true);
        fetchLogs('/dashboard/logs');
    });

    // AJAX Pagination links
    $(document).on('click', '.log-pagination-container a', function (e) {
        e.preventDefault();
        const href = $(this).attr('href');
        if (href) {
            fetchLogs(href);
        }
    });
});