<script>
$(document).ready(function () {

    var TransferTable;

    // Load available stock
    function loadAvailableStock() {
        var tbody = document.getElementById('availableStockBody');
        
        // Check if element exists
        if (!tbody) {
            console.log('availableStockBody not found in DOM');
            return;
        }
        
        console.log('Loading available stock...');
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';
        
        var apiUrl = '<?= App::baseUrl() ?>/_ikawa/transfers/get-available-stock';
        console.log('Calling:', apiUrl);
        
        $.ajax({
            url: apiUrl,
            method: 'GET',
            dataType: 'json',
            cache: false,
            success: function(res) {
                console.log('Response:', res);
                
                // Re-get tbody in case DOM changed
                var tbody = document.getElementById('availableStockBody');
                if (!tbody) {
                    console.log('availableStockBody disappeared from DOM');
                    return;
                }
                
                if (res.success && res.data && res.data.length > 0) {
                    var html = '';
                    for (var i = 0; i < res.data.length; i++) {
                        var item = res.data[i];
                        var supplierDisplay = (item.supplier_name || 'N/A') + ' / ' + (item.supplier_type || 'N/A');
                        var stockJson = encodeURIComponent(JSON.stringify(item));
                        
                        html += '<tr data-stock-encoded="' + stockJson + '">' +
                            '<td><input type="checkbox" class="stock-checkbox"></td>' +
                            '<td>' + (item.type_name || 'N/A') + '</td>' +
                            '<td>' + supplierDisplay + '</td>' +
                            '<td>' + item.total_quantity + '</td>' +
                            '<td><input type="number" step="0.01" class="form-control transfer-qty" max="' + item.total_quantity + '" placeholder="0" style="width:100px;"></td>' +
                        '</tr>';
                    }
                    tbody.innerHTML = html;
                    console.log('Added', res.data.length, 'rows');
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center">No stock available</td></tr>';
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', status, error, xhr.responseText);
                var tbody = document.getElementById('availableStockBody');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error: ' + error + '</td></tr>';
                }
            }
        });
    }

    // Load transfer history
    function loadTransferHistory() {
        var historyBody = document.getElementById('transferHistoryData');
        if (!historyBody) return;
        
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfers/get-all',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if ($.fn.DataTable.isDataTable('#transfer-history-table')) {
                    $('#transfer-history-table').DataTable().destroy();
                }
                
                historyBody.innerHTML = '';

                if (res.success && res.data && res.data.length > 0) {
                    var html = '';
                    for (var i = 0; i < res.data.length; i++) {
                        var record = res.data[i];
                        var statusClass = record.status === 'completed' ? 'label-success' : (record.status === 'pending' ? 'label-warning' : 'label-info');
                        html += '<tr>' +
                            '<td>' + (i + 1) + '</td>' +
                            '<td><strong>' + record.reference_no + '</strong></td>' +
                            '<td>' + (record.from_location || 'N/A') + '</td>' +
                            '<td>' + record.total_items + '</td>' +
                            '<td>' + record.total_quantity + '</td>' +
                            '<td>' + new Date(record.transfer_date).toLocaleDateString() + '</td>' +
                            '<td><span class="label ' + statusClass + '">' + record.status.charAt(0).toUpperCase() + record.status.slice(1) + '</span></td>' +
                            '<td>' +
                                '<button class="btn btn-sm btn-info view-details-btn" data-id="' + record.tracking_id + '" data-ref="' + record.reference_no + '">' +
                                    '<i class="fa fa-eye"></i>' +
                                '</button>' +
                            '</td>' +
                        '</tr>';
                    }
                    historyBody.innerHTML = html;
                }

                TransferTable = $('#transfer-history-table').DataTable({ pageLength: 10 });
            }
        });
    }

    // Initialize - only if elements exist
    if (document.getElementById('transferHistoryData')) {
        loadTransferHistory();
    }

    // Load stock when transfer modal opens - use event delegation
    $(document).on('show.bs.modal', '#transferModal', function() {
        console.log('Transfer modal opening...');
        
        // Small delay to ensure modal content is rendered
        setTimeout(function() {
            loadAvailableStock();
        }, 100);
        
        $('#transfer_date').val('<?= date('Y-m-d') ?>');
        $('#transfer_notes').val('');
    });

    // Select all checkbox
    $(document).on('change', '#selectAllStock', function() {
        $('.stock-checkbox').prop('checked', $(this).is(':checked'));
    });

    // Save transfer - no destination needed, uses user's loc_id
    $(document).on('click', '#saveTransferBtn', function() {
        var btn = this;
        var transfer_date = $('#transfer_date').val();
        var notes = $('#transfer_notes').val();

        if (!transfer_date) {
            showToast('Please select transfer date!', 'error');
            return;
        }

        var items = [];
        var hasError = false;
        
        $('#availableStockBody tr').each(function() {
            var checkbox = $(this).find('.stock-checkbox');
            var qtyInput = $(this).find('.transfer-qty');
            
            if (checkbox.is(':checked')) {
                var qty = parseFloat(qtyInput.val()) || 0;
                var maxQty = parseFloat(qtyInput.attr('max')) || 0;
                
                if (qty > 0 && qty <= maxQty) {
                    var encodedData = $(this).attr('data-stock-encoded');
                    if (encodedData) {
                        try {
                            var stockData = JSON.parse(decodeURIComponent(encodedData));
                            items.push({
                                type_id: stockData.type_id,
                                sup_id: stockData.sup_id,
                                quantity: qty
                            });
                        } catch(e) {
                            console.log('Parse error:', e);
                        }
                    }
                } else if (qty > maxQty) {
                    showToast('Transfer quantity cannot exceed available quantity!', 'error');
                    hasError = true;
                    return false;
                }
            }
        });

        if (hasError) return;

        if (items.length === 0) {
            showToast('Please select items and enter valid quantities to transfer!', 'error');
            return;
        }

        setButtonLoading(btn, true);

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfers/create-multiple',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                transfer_date: transfer_date,
                notes: notes,
                items: items
            }),
            success: function(response) {
                if (response.success) {
                    showToast(response.message + ' Ref: ' + response.data.reference_no, 'success');
                    $('#transferModal').modal('hide');
                    loadTransferHistory();
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function(xhr) {
                var msg = 'Error creating transfer';
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

    // View transfer details
    $(document).on('click', '.view-details-btn', function() {
        var tracking_id = $(this).data('id');
        var ref_no = $(this).data('ref');
        
        $('#detailRefNo').text(ref_no);
        $('#transferDetailsBody').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
        $('#viewDetailsModal').modal('show');

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/transfers/get-details/' + tracking_id,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                var tbody = document.getElementById('transferDetailsBody');
                if (!tbody) return;
                
                if (res.success && res.data && res.data.length > 0) {
                    var html = '';
                    for (var i = 0; i < res.data.length; i++) {
                        var item = res.data[i];
                        var supplierDisplay = (item.supplier_name || 'N/A') + ' / ' + (item.supplier_type || 'N/A');
                        html += '<tr>' +
                            '<td>' + (i + 1) + '</td>' +
                            '<td>' + (item.type_name || 'N/A') + '</td>' +
                            '<td>' + (item.unit_name || 'N/A') + '</td>' +
                            '<td>' + supplierDisplay + '</td>' +
                            '<td>' + item.quantity + '</td>' +
                        '</tr>';
                    }
                    tbody.innerHTML = html;
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center">No details found</td></tr>';
                }
            },
            error: function() {
                var tbody = document.getElementById('transferDetailsBody');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading details</td></tr>';
                }
            }
        });
    });
});
</script>
