<x-layout>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark m-0">Log History</h2>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4 bg-white rounded-4">
            <form id="logsFilterForm" method="GET" action="/dashboard/logs" class="row g-3 align-items-end">
                
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted">Filter by Category</label>
                    <select name="category" id="cboCategory" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted">Filter by SubCategory</label>
                    <select name="subcategory" id="cboSubCategory" class="form-select" {{ request('category') ? '' : 'disabled' }}>
                        <option value="">All Subcategories</option>
                        </select>
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <a href="/dashboard/logs" class="btn btn-light border fw-bold text-danger w-100"><i class="bi bi-x-circle me-1"></i> Reset</a>
                </div>

            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <x-log-table :logs="$logs" />
        </div>
    </div>

    <script type="module">
        $(document).ready(function() {
            const cboCategory = $('#cboCategory');
            const cboSubCategory = $('#cboSubCategory');
            const selectedSub = '{{ request("subcategory") }}';

            function fetchSubCategories(categoryVal) {
                if (!categoryVal) {
                    cboSubCategory.html('<option value="">All Subcategories</option>').prop('disabled', true);
                    return;
                }

                cboSubCategory.prop('disabled', false).html('<option value="">Loading...</option>');

                $.ajax({
                    url: '/api/subcategories',
                    data: { category: categoryVal },
                    success: function(data) {
                        let options = '<option value="">All Sub-categories</option>';

                        data.forEach(function(sub) {
                            const isSelected = (selectedSub === sub) ? 'selected' : '';
                            options += `<option value="${sub}" ${isSelected}>${sub}</option>`;
                        });

                        cboSubCategory.html(options);
                    }
                });
            }

            if (cboCategory.val()) {
                fetchSubCategories(cboCategory.val());
            }

            cboCategory.on('change', function() {
                const categoryVal = $(this).val();

                if (!categoryVal) {
                    cboSubCategory.html('<option value="">All Sub-categories</option>').prop('disabled', true);
                    $('#logsFilterForm').trigger('submit');
                    return;
                }

                fetchSubCategories(categoryVal);
                $('#logsFilterForm').trigger('submit');
            });

            cboSubCategory.on('change', function() {
                $('#logsFilterForm').trigger('submit');
            });
        });
    </script>

</x-layout>