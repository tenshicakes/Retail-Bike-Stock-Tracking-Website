$(document).ready(function () {
    const cboCategory = $("#cboCategory");
    const cboSubCategory = $("#cboSubCategory");
    const selectedSub = '{{ request("subcategory") }}';

    function fetchSubCategories(categoryVal) {
        if (!categoryVal) {
            cboSubCategory
                .html('<option value="">All Subcategories</option>')
                .prop("disabled", true);
            return;
        }

        cboSubCategory
            .prop("disabled", false)
            .html('<option value="">Loading...</option>');

        $.ajax({
            url: "/api/subcategories",
            data: { category: categoryVal },
            success: function (data) {
                let options = '<option value="">All Sub-categories</option>';

                data.forEach(function (sub) {
                    const isSelected = selectedSub === sub ? "selected" : "";
                    options += `<option value="${sub}" ${isSelected}>${sub}</option>`;
                });

                cboSubCategory.html(options);
            },
        });
    }

    if (cboCategory.val()) {
        fetchSubCategories(cboCategory.val());
    }

    cboCategory.on("change", function () {
        const categoryVal = $(this).val();

        if (!categoryVal) {
            cboSubCategory
                .html('<option value="">All Sub-categories</option>')
                .prop("disabled", true);
            $("#logsFilterForm").trigger("submit");
            return;
        }

        fetchSubCategories(categoryVal);
        $("#logsFilterForm").trigger("submit");
    });

    cboSubCategory.on("change", function () {
        $("#logsFilterForm").trigger("submit");
    });
});
