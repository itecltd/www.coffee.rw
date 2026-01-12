<script>
$(document).ready(function () {

    let DetailedTable;
    let SummaryTable;
    let rowCounter = 0;
    let categoriesData = []; // Store categories
    let suppliersData = []; // Store suppliers

    // Load categories
    function loadCategories() {
        return $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/categories/get-active-categories',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data) {
                    categoriesData = res.data;
                }
            }
        });
    }

    // Load suppliers
    function loadSuppliers() {
        return $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/inventory/getsuppliers',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data) {
                    suppliersData = res.data;
                }
            }
        });
    }

    // Initialize data on page load
    $.when(loadCategories(), loadSuppliers()).done(function() {
        console.log('Data loaded:', categoriesData.length, 'categories,', suppliersData.length, 'suppliers');
    });

    // Generate category options HTML
    function getCategoryOptions() {
        let options = '<option value="">Select Category</option>';
        $.each(categoriesData, function(index, cat) {
            options += '<option value="' + cat.category_id + '">' + cat.category_name + '</option>';
        });
        return options;
    }

    // Generate supplier options HTML
    function getSupplierOptions() {
        let options = '<option value="">Select Supplier</option>';
        $.each(suppliersData, function(index, supplier) {
            const displayName = supplier.full_name + ' / ' + (supplier.type || 'N/A');
            options += '<option value="' + supplier.sup_id + '">' + displayName + '</option>';
        });
        return options;
    }

    // Add new row to the stock entry table
    function addStockRow() {
        rowCounter++;
        const rowHtml = `
            <tr id="stockRow_${rowCounter}" data-row="${rowCounter}">
                <td>
                    <select class="form-control stock-category-select" data-row="${rowCounter}" name="category_id_${rowCounter}">
                        ${getCategoryOptions()}
                    </select>
                </td>
                <td>
                    <select class="form-control stock-type-unit-select" data-row="${rowCounter}" name="type_unit_${rowCounter}" disabled>
                        <option value="">Select Type / Unity</option>
                    </select>
                </td>
                <td>
                    <select class="form-control stock-supplier-select" data-row="${rowCounter}" name="sup_id_${rowCounter}">
                        ${getSupplierOptions()}
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control stock-quantity" data-row="${rowCounter}" name="quantity_${rowCounter}" placeholder="0">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control stock-unit-price" data-row="${rowCounter}" name="unit_price_${rowCounter}" placeholder="0">
                </td>
                <td>
                    <span class="stock-total-price" data-row="${rowCounter}">0 RWF</span>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-row-btn" data-row="${rowCounter}">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#stockEntryBody').append(rowHtml);
    }

    // Calculate row total
    function calculateRowTotal(rowId) {
        const quantity = parseFloat($('input[name="quantity_' + rowId + '"]').val()) || 0;
        const unitPrice = parseFloat($('input[name="unit_price_' + rowId + '"]').val()) || 0;
        const total = quantity * unitPrice;
        $('span.stock-total-price[data-row="' + rowId + '"]').text(total.toLocaleString() + ' RWF');
        calculateGrandTotal();
    }

    // Calculate grand total
    function calculateGrandTotal() {
        let grandTotal = 0;
        $('#stockEntryBody tr').each(function() {
            const rowId = $(this).data('row');
            const quantity = parseFloat($('input[name="quantity_' + rowId + '"]').val()) || 0;
            const unitPrice = parseFloat($('input[name="unit_price_' + rowId + '"]').val()) || 0;
            grandTotal += quantity * unitPrice;
        });
        $('#grandTotal').text(grandTotal.toLocaleString() + ' RWF');
    }

    // Load category type/unity combinations when category changes
    $(document).on('change', '.stock-category-select', function() {
        const rowId = $(this).data('row');
        const categoryId = $(this).val();
        const typeUnitSelect = $('select[name="type_unit_' + rowId + '"]');

        typeUnitSelect.html('<option value="">Select Type / Unity</option>').prop('disabled', true);

        if (!categoryId) {
            return;
        }

        typeUnitSelect.html('<option value="">Loading...</option>');

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/category-type-units/get-type-unity-by-category/' + categoryId,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                let options = '<option value="">Select Type / Unity</option>';
                if (res.success && res.data && res.data.length > 0) {
                    $.each(res.data, function(index, item) {
                        // Store both type_id and unit_id in the value as JSON
                        const value = JSON.stringify({type_id: item.type_id, unit_id: item.unit_id});
                        options += '<option value=\'' + value + '\'>' + item.type_unit_name + '</option>';
                    });
                    typeUnitSelect.html(options).prop('disabled', false);
                } else {
                    typeUnitSelect.html('<option value="">No types available</option>').prop('disabled', true);
                }
            },
            error: function() {
                typeUnitSelect.html('<option value="">Error loading</option>').prop('disabled', true);
            }
        });
    });

    // Calculate total on quantity/price change
    $(document).on('input change', '.stock-quantity, .stock-unit-price', function() {
        const rowId = $(this).data('row');
        calculateRowTotal(rowId);
    });

    // Remove row
    $(document).on('click', '.remove-row-btn', function() {
        const rowId = $(this).data('row');
        $('#stockRow_' + rowId).remove();
        calculateGrandTotal();
        
        if ($('#stockEntryBody tr').length === 0) {
            addStockRow();
        }
    });

    // Add row button click
    $(document).on('click', '#addRowBtn', function() {
        addStockRow();
    });

    // Modal show - add first row
    $('#addStockModal').on('show.bs.modal', function() {
        $('#stockEntryBody').empty();
        rowCounter = 0;
        $('#grandTotal').text('0 RWF');
        
        $.when(loadCategories(), loadSuppliers()).done(function() {
            addStockRow();
        });
    });

    // Save all stock entries
    $(document).on('click', '#saveAllStockBtn', function() {
        const btn = this;
        const stockItems = [];
        let hasError = false;

        $('#stockEntryBody tr').each(function() {
            const rowId = $(this).data('row');
            const typeUnitValue = $('select[name="type_unit_' + rowId + '"]').val();
            const sup_id = $('select[name="sup_id_' + rowId + '"]').val();
            const quantity = parseFloat($('input[name="quantity_' + rowId + '"]').val()) || 0;
            const unit_price = parseFloat($('input[name="unit_price_' + rowId + '"]').val()) || 0;
            const total_price = quantity * unit_price;

            // Parse type_id and unit_id from the combined value
            let type_id = '';
            let unit_id = '';
            
            if (typeUnitValue) {
                try {
                    const parsed = JSON.parse(typeUnitValue);
                    type_id = parsed.type_id;
                    unit_id = parsed.unit_id;
                } catch(e) {
                    hasError = true;
                }
            }

            if (!type_id || !unit_id || !sup_id || quantity <= 0 || unit_price <= 0) {
                hasError = true;
                $(this).addClass('danger');
                return;
            } else {
                $(this).removeClass('danger');
            }

            stockItems.push({
                type_id: type_id,
                unit_id: unit_id,
                sup_id: sup_id,
                quantity: quantity,
                unit_price: unit_price,
                total_price: total_price
            });
        });

        if (hasError || stockItems.length === 0) {
            showToast('Please fill all required fields in each row!', 'error');
            return;
        }

        setButtonLoading(btn, true);

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/stock/create-multiple',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ items: stockItems }),
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#addStockModal').modal('hide');
                    loadData();
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function(xhr) {
                let msg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showToast(msg, 'error');
            },
            complete: function() {
                setButtonLoading(btn, false);
            }
        });
    });

    // Existing DataTable functions
    function loadDetailedStock() {
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/stock/get-detailed-stock', function (res) {
            if (!res.success) return;

            if ($.fn.DataTable.isDataTable('#detailed-stock-table')) {
                $('#detailed-stock-table').DataTable().destroy();
            }
            
            $('#detailedStockData').empty();

            $.each(res.data, function (index, record) {
                const row = `<tr>
                    <td>${index + 1}</td>
                    <td>${record.type_name || 'N/A'}</td>
                    <td>${record.unit_name || 'N/A'}</td>
                    <td>${record.supplier_name || 'N/A'}</td>
                    <td>${record.quantity}</td>
                    <td>${parseFloat(record.unit_price).toLocaleString()} RWF</td>
                    <td>${parseFloat(record.total_price).toLocaleString()} RWF</td>
                    <td>${new Date(record.created_at).toLocaleDateString()}</td>
                </tr>`;
                
                $('#detailedStockData').append(row);
            });

            DetailedTable = $('#detailed-stock-table').DataTable({ pageLength: 10 });
        });
    }

    function loadSummaryStock() {
        $.getJSON('<?= App::baseUrl() ?>/_ikawa/stock/get-summary-stock', function (res) {
            if (!res.success) return;

            if ($.fn.DataTable.isDataTable('#summary-stock-table')) {
                $('#summary-stock-table').DataTable().destroy();
            }
            
            $('#summaryStockData').empty();

            $.each(res.data, function (index, record) {
                const supplierDisplay = (record.supplier_name || 'N/A') + ' / ' + (record.supplier_type || 'N/A');
                const row = `<tr>
                    <td>${index + 1}</td>
                    <td>${record.station_name || 'N/A'}</td>
                    <td>${record.type_name || 'N/A'}</td>
                    <td>${supplierDisplay}</td>
                    <td>${record.total_quantity || 0}</td>
                </tr>`;
                
                $('#summaryStockData').append(row);
            });

            SummaryTable = $('#summary-stock-table').DataTable({ pageLength: 10 });
        });
    }

    function loadData() {
        loadDetailedStock();
        loadSummaryStock();
    }

    // Load data on page load
    loadData();
});
</script>
