
<script>
// Toast notification function (global)
function showToastExpenseConsume(message, type) {
    var toast = $('<div class="toast ' + type + '">' + message + '</div>');
    $('body').append(toast);
    setTimeout(function () {
        toast.addClass('show');
    }, 100);
    setTimeout(function () {
        toast.removeClass('show');
        setTimeout(function () {
            toast.remove();
        }, 500);
    }, 3000);
}

// Global function to load expense consumes
window.loadExpenseConsumes = function() {
    $.ajax({
        url: '<?= App::baseUrl() ?>/_ikawa/expense-consume/get-all',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            // Get or initialize DataTable
            var expenseConsumeTable;
            if ($.fn.DataTable.isDataTable('#data-table-basic')) {
                expenseConsumeTable = $('#data-table-basic').DataTable();
            } else {
                expenseConsumeTable = $('#data-table-basic').DataTable({
                    pageLength: 10,
                    lengthChange: true,
                    searching: true,
                    ordering: true,
                    autoWidth: false
                });
            }

            if (response.success && response.data) {
                expenseConsumeTable.clear();

                $.each(response.data, function (index, record) {
                    expenseConsumeTable.row.add([
                        index + 1,
                        record.expense_name || 'N/A',
                        (record.st_name || 'N/A') + ' - ' + (record.st_location || ''),
                        new Intl.NumberFormat('en-RW', { style: 'currency', currency: 'RWF', minimumFractionDigits: 0 }).format(record.amount),
                        record.payment_mode_name || 'N/A',
                        record.payer_name || '-',
                        record.recorded_date,
                        `<div class="button-icon-btn button-icon-btn-rd">
                            <button class="btn btn-default btn-icon-notika editExpenseConsumeBtn"
                                title="Edit"
                                data-id="${record.con_id}"
                                data-expense_id="${record.expense_id}"
                                data-station_id="${record.station_id}"
                                data-amount="${record.amount}"
                                data-pay_mode="${record.pay_mode}"
                                data-payer_name="${record.payer_name || ''}"
                                data-description="${record.description || ''}"
                                data-recorded_date="${record.recorded_date}">
                                <i class="notika-icon notika-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-icon-notika deleteExpenseConsumeBtn"
                                title="Delete"
                                data-id="${record.con_id}"
                                data-expense_name="${record.expense_name || 'this record'}">
                                <i class="notika-icon notika-close"></i>
                            </button>
                        </div>`
                    ]);
                });

                expenseConsumeTable.draw(false);
            } else {
                expenseConsumeTable.clear().draw();
            }
        },
        error: function () {
            showToastExpenseConsume('Failed to load expense consumption records', 'error');
        }
    });
};

$(document).ready(function () {

    // Button loading state
    function setButtonLoading(btn, isLoading) {
        if (isLoading) {
            // Store original text before changing
            if (!$(btn).data('original-text')) {
                $(btn).data('original-text', $(btn).html());
            }
            $(btn).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
        } else {
            var originalText = $(btn).data('original-text') || 'Save';
            $(btn).prop('disabled', false).html(originalText);
        }
    }

    // Handle Save Expense Consume button
    $(document).on('click', '#saveExpenseConsumeBtn', function () {
        const btn = this;
        setButtonLoading(btn, true);
        
        const expenseConsumeData = {
            expense_id: $('#expense_id').val(),
            station_id: $('#station_id').val(),
            amount: $('#amount').val(),
            pay_mode: $('#pay_mode').val(),
            payer_name: $('#payer_name').val().trim(),
            description: $('#description').val().trim(),
            recorded_date: $('#recorded_date').val()
        };

        // Validation
        if (!expenseConsumeData.expense_id || !expenseConsumeData.station_id || 
            !expenseConsumeData.amount || !expenseConsumeData.pay_mode || 
            !expenseConsumeData.recorded_date) {
            showToastExpenseConsume('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        if (parseFloat(expenseConsumeData.amount) <= 0) {
            showToastExpenseConsume('Amount must be greater than zero!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/expense-consume/create',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(expenseConsumeData),
            success: function (response) {
                if (response.success) {
                    showToastExpenseConsume(response.message, 'success');
                    $('#createExpenseConsumeModal').modal('hide');
                    $('#createExpenseConsumeModal input, #createExpenseConsumeModal textarea').val('');
                    $('#createExpenseConsumeModal select').val('').trigger('chosen:updated');
                    loadExpenseConsumes();
                } else {
                    showToastExpenseConsume(response.message, 'error');
                }
            },
            error: function (xhr) {
                let msg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showToastExpenseConsume(msg, 'error');
            },
            complete: function () {
                setButtonLoading(btn, false);
            }
        });
    });

    // Handle Edit Expense Consume button click
    $(document).on('click', '.editExpenseConsumeBtn', function () {
        const conId = $(this).data('id');
        const expenseId = $(this).data('expense_id');
        const stationId = $(this).data('station_id');
        const amount = $(this).data('amount');
        const payMode = $(this).data('pay_mode');
        const payerName = $(this).data('payer_name');
        const description = $(this).data('description');
        const recordedDate = $(this).data('recorded_date');

        $('#edit_con_id').val(conId);
        $('#edit_expense_id').val(expenseId).trigger('chosen:updated');
        $('#edit_station_id').val(stationId).trigger('chosen:updated');
        $('#edit_amount').val(amount);
        $('#edit_pay_mode').val(payMode).trigger('chosen:updated');
        $('#edit_payer_name').val(payerName);
        $('#edit_description').val(description);
        $('#edit_recorded_date').val(recordedDate);

        // Reset button text and data
        $('#updateExpenseConsumeBtn').html('Save changes').removeData('original-text');

        $('#editExpenseConsumeModal').modal('show');
    });

    // Handle Update Expense Consume button
    $(document).on('click', '#updateExpenseConsumeBtn', function () {
        const btn = this;
        setButtonLoading(btn, true);

        const expenseConsumeData = {
            con_id: $('#edit_con_id').val(),
            expense_id: $('#edit_expense_id').val(),
            station_id: $('#edit_station_id').val(),
            amount: $('#edit_amount').val(),
            pay_mode: $('#edit_pay_mode').val(),
            payer_name: $('#edit_payer_name').val().trim(),
            description: $('#edit_description').val().trim(),
            recorded_date: $('#edit_recorded_date').val()
        };

        // Validation
        if (!expenseConsumeData.expense_id || !expenseConsumeData.station_id || 
            !expenseConsumeData.amount || !expenseConsumeData.pay_mode || 
            !expenseConsumeData.recorded_date) {
            showToastExpenseConsume('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        if (parseFloat(expenseConsumeData.amount) <= 0) {
            showToastExpenseConsume('Amount must be greater than zero!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/expense-consume/update',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(expenseConsumeData),
            success: function (response) {
                if (response.success) {
                    showToastExpenseConsume(response.message, 'success');
                    $('#editExpenseConsumeModal').modal('hide');
                    loadExpenseConsumes();
                } else {
                    showToastExpenseConsume(response.message, 'error');
                }
            },
            error: function (xhr) {
                let msg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showToastExpenseConsume(msg, 'error');
            },
            complete: function () {
                setButtonLoading(btn, false);
            }
        });
    });

    // Handle Delete Expense Consume button
    $(document).on('click', '.deleteExpenseConsumeBtn', function () {
        const btn = $(this);
        const conId = btn.data('id');
        const expenseName = btn.data('expense_name') || 'this record';

        swal({   
            title: "Are you sure?",   
            text: "You will delete " + expenseName + "! This action cannot be undone.",   
            type: "warning",   
            showCancelButton: true,   
            confirmButtonColor: "#DD6B55",   
            confirmButtonText: "Yes, delete it!",   
            closeOnConfirm: false 
        }, function(){   
            $.ajax({
                url: '<?= App::baseUrl() ?>/_ikawa/expense-consume/delete',
                method: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({ con_id: conId }),
                success: function (response) {
                    if (response.success) {
                        swal("Deleted!", response.message, "success");
                        loadExpenseConsumes();
                    } else {
                        swal("Error!", response.message, "error");
                    }
                },
                error: function (xhr) {
                    let msg = 'Something went wrong';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    swal("Error!", msg, "error");
                }
            });
        });
    });

    // Clear modal on close
    $('#createExpenseConsumeModal').on('hidden.bs.modal', function () {
        $(this).find('input, textarea').val('');
        $(this).find('select').val('').trigger('chosen:updated');
        $('#recorded_date').val('<?= date('Y-m-d') ?>');
    });

});
</script>
