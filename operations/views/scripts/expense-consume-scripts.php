
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
                                data-expense-name="${record.expense_name}">
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
    // Array to store payment entries
    var paymentEntries = [];
    var selectedAccount = null;
    var selectedPaymentMode = null;

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

    // Handle payment mode header clicks (toggle accounts section)
    $(document).on('click', '.payment-mode-header', function() {
        var $wrapper = $(this).closest('.payment-mode-wrapper');
        var $accountsSection = $wrapper.find('.accounts-section');
        var $chevron = $(this).find('.fa-chevron-down');
        var modeId = $wrapper.data('mode-id');
        
        // Toggle accounts section
        if ($accountsSection.is(':visible')) {
            $accountsSection.slideUp(300);
            $chevron.css('transform', 'rotate(0deg)');
            $wrapper.css('border-color', '#ddd');
        } else {
            // Close other open sections
            $('.payment-mode-wrapper .accounts-section').slideUp(300);
            $('.payment-mode-header .fa-chevron-down').css('transform', 'rotate(0deg)');
            $('.payment-mode-wrapper').css('border-color', '#ddd');
            
            // Open this section
            $accountsSection.slideDown(300);
            $chevron.css('transform', 'rotate(180deg)');
            $wrapper.css('border-color', '#00c292');
            
            // Load accounts if not already loaded
            var $select = $wrapper.find('.payment-account-select');
            if ($select.find('option').length <= 1) {
                $select.html('<option value="">Loading...</option>');
                
                $.ajax({
                    url: '<?= App::baseUrl() ?>/_ikawa/accounts/by-mode/' + modeId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data && response.data.length > 0) {
                            var options = '<option value="">Select...</option>';
                            response.data.forEach(function(account) {
                                options += `<option value="${account.acc_id}" 
                                                   data-account-name="${account.acc_name}"
                                                   data-balance="${account.balance}">
                                                ${account.acc_name} (${account.acc_reference_num})
                                            </option>`;
                            });
                            $select.html(options);
                        } else {
                            $select.html('<option value="">No accounts</option>');
                        }
                    },
                    error: function() {
                        $select.html('<option value="">Error loading</option>');
                    }
                });
            }
        }
    });

    // Handle account selection within payment mode card - Show inline entry fields
    $(document).on('change', '.payment-account-select', function() {
        var $wrapper = $(this).closest('.payment-mode-wrapper');
        var $accountsSection = $wrapper.find('.accounts-section');
        var $entryForm = $accountsSection.find('.entry-form');
        var $balanceDisplay = $wrapper.find('.account-balance-display');
        var selectedOption = $(this).find('option:selected');
        var accountId = selectedOption.val();
        
        if (!accountId) {
            $entryForm.css('display', 'none');
            $balanceDisplay.text('');
            return;
        }
        
        var balance = selectedOption.data('balance');
        var accountName = selectedOption.data('account-name');
        
        $balanceDisplay.html(`<i class="fa fa-info-circle"></i> Balance: <strong>${parseFloat(balance).toLocaleString()} RWF</strong>`);
        
        // Show entry form inline
        $entryForm.css('display', 'contents');
        
        // Clear and focus amount field
        $entryForm.find('.entry-amount').val('').focus();
        $entryForm.find('.entry-charges').val('0');
        $entryForm.find('.entry-total').val('');
        
        // Store account info in the form
        $entryForm.data('account-id', accountId);
        $entryForm.data('account-name', accountName);
        $entryForm.data('account-balance', balance);
    });

    // Calculate entry total within card (dynamic calculation)
    $(document).on('input keyup change', '.entry-amount, .entry-charges', function() {
        var $form = $(this).closest('.entry-form');
        var amount = parseFloat($form.find('.entry-amount').val()) || 0;
        var charges = parseFloat($form.find('.entry-charges').val()) || 0;
        var total = amount + charges;
        $form.find('.entry-total').val(total.toFixed(2));
    });

    // Handle Add Entry button within payment mode card
    $(document).on('click', '.add-entry-btn', function() {
        var $form = $(this).closest('.entry-form');
        var $wrapper = $(this).closest('.payment-mode-wrapper');
        var $select = $wrapper.find('.payment-account-select');
        
        var accountId = $form.data('account-id');
        var accountName = $form.data('account-name');
        var accountBalance = parseFloat($form.data('account-balance'));
        var amount = parseFloat($form.find('.entry-amount').val());
        var charges = parseFloat($form.find('.entry-charges').val()) || 0;
        var total = amount + charges;
        
        // Validation
        if (!accountId) {
            showToastExpenseConsume('Please select an account first!', 'error');
            return;
        }
        
        if (!amount || amount <= 0) {
            showToastExpenseConsume('Please enter a valid amount!', 'error');
            return;
        }
        
        if (total > accountBalance) {
            showToastExpenseConsume(`Insufficient balance! Available: ${accountBalance.toLocaleString()} RWF`, 'error');
            return;
        }
        
        // Add to payment entries array
        paymentEntries.push({
            account_id: accountId,
            account_name: accountName,
            amount: amount,
            charges: charges,
            total: total
        });
        
        // Reset inline form
        $form.find('.entry-amount, .entry-charges, .entry-total').val('');
        $form.find('.entry-charges').val('0');
        $form.css('display', 'none');
        $select.val('');
        $wrapper.find('.account-balance-display').text('');
        
        // Render entries and show section
        renderPaymentEntries();
        calculateTotals();
        $('#payment_entries_section').slideDown(300);
        
        showToastExpenseConsume('Payment entry added successfully!', 'success');
    });

    // Render payment entries table
    function renderPaymentEntries() {
        var html = '';
        paymentEntries.forEach(function(entry, index) {
            html += `
                <tr>
                    <td>${entry.account_name} (${entry.account_ref})</td>
                    <td>${entry.amount.toFixed(2)} RWF</td>
                    <td>${entry.charges.toFixed(2)} RWF</td>
                    <td>${entry.total.toFixed(2)} RWF</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-entry-btn" data-index="${index}">
                            <i class="notika-icon notika-close"></i> Remove
                        </button>
                    </td>
                </tr>
            `;
        });
        $('#payment_entries_list').html(html);
    }

    // Handle remove entry button
    $(document).on('click', '.remove-entry-btn', function() {
        var index = $(this).data('index');
        paymentEntries.splice(index, 1);
        renderPaymentEntries();
        calculateTotals();

        if (paymentEntries.length === 0) {
            $('#payment_entries_section').hide();
        }

        showToastExpenseConsume('Payment entry removed', 'success');
    });

    // Calculate totals
    function calculateTotals() {
        var totalAmount = 0;
        var totalCharges = 0;
        var grandTotal = 0;

        paymentEntries.forEach(function(entry) {
            totalAmount += entry.amount;
            totalCharges += entry.charges;
            grandTotal += entry.total;
        });

        $('#total_amount_sum').text(totalAmount.toFixed(2));
        $('#total_charges_sum').text(totalCharges.toFixed(2));
        $('#grand_total_sum').text(grandTotal.toFixed(2));
    }

    // Handle Save Expense Consume button
    $(document).on('click', '#saveExpenseConsumeBtn', function () {
        const btn = this;

        // Validation
        var expenseId = $('#expense_id').val();
        var recordedDate = $('#recorded_date').val();

        if (!expenseId || !recordedDate) {
            showToastExpenseConsume('Please fill all required fields!', 'error');
            return;
        }

        console.log('Payment entries before validation:', paymentEntries);
        
        if (paymentEntries.length === 0) {
            showToastExpenseConsume('Please add at least one payment entry!', 'error');
            return;
        }

        setButtonLoading(btn, true);
        
        // Create a clean copy of payment entries with only required fields
        const cleanPaymentEntries = paymentEntries.map(function(entry) {
            return {
                account_id: entry.account_id,
                amount: entry.amount,
                charges: entry.charges
            };
        });
        
        const expenseConsumeData = {
            expense_id: parseInt(expenseId),
            payer_name: $('#payer_name').val().trim(),
            description: $('#description').val().trim(),
            recorded_date: recordedDate,
            payment_entries: cleanPaymentEntries
        };

        console.log('Submitting data:', expenseConsumeData);
        console.log('Payment entries JSON:', JSON.stringify(cleanPaymentEntries));

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/expense-consume/create',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(expenseConsumeData),
            success: function (response) {
                console.log('Response:', response);
                if (response.success) {
                    showToastExpenseConsume(response.message, 'success');
                    $('#createExpenseConsumeModal').modal('hide');
                    // Reset form
                    resetForm();
                    loadExpenseConsumes();
                } else {
                    showToastExpenseConsume(response.message, 'error');
                }
            },
            error: function (xhr) {
                console.error('Error:', xhr);
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

    // Reset form function
    function resetForm() {
        paymentEntries = [];
        selectedAccount = null;
        selectedPaymentMode = null;

        $('#createExpenseConsumeModal input:not([type="date"]), #createExpenseConsumeModal textarea').val('');
        $('#createExpenseConsumeModal select').val('').trigger('chosen:updated');
        
        // Reset all payment mode wrappers
        $('.payment-mode-wrapper').css('border-color', '#ddd');
        $('.payment-mode-wrapper .accounts-section').hide();
        $('.payment-mode-wrapper .entry-form').hide();
        $('.payment-mode-wrapper .account-balance-display').text('');
        $('.payment-mode-wrapper .entry-amount, .payment-mode-wrapper .entry-charges').val('');
        $('.payment-mode-wrapper .entry-charges').val('0');
        $('.payment-mode-wrapper .entry-total').val('');
        $('.payment-mode-header .fa-chevron-down').css('transform', 'rotate(0deg)');
        
        $('#payment_entries_section').hide();
        $('#payment_entries_list').html('');

        calculateTotals();
    }

    // Handle Edit Expense Consume button click
    $(document).on('click', '.editExpenseConsumeBtn', function () {
        console.log('Edit button clicked');
        const conId = $(this).data('id');
        const expenseId = $(this).data('expense_id');
        const stationId = $(this).data('station_id');
        const amount = $(this).data('amount');
        const payMode = $(this).data('pay_mode');
        const payerName = $(this).data('payer_name');
        const description = $(this).data('description');
        const recordedDate = $(this).data('recorded_date');

        console.log('Edit data:', {conId, expenseId, amount, payMode, payerName, description, recordedDate});

        $('#edit_con_id').val(conId);
        $('#edit_expense_id').val(expenseId).trigger('chosen:updated');
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
            amount: $('#edit_amount').val(),
            pay_mode: $('#edit_pay_mode').val(),
            payer_name: $('#edit_payer_name').val().trim(),
            description: $('#edit_description').val().trim(),
            recorded_date: $('#edit_recorded_date').val()
        };

        console.log('Update data being sent:', expenseConsumeData);

        // Validation
        if (!expenseConsumeData.con_id) {
            showToastExpenseConsume('Missing expense consume ID!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        if (!expenseConsumeData.expense_id) {
            showToastExpenseConsume('Please select an expense type!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        if (!expenseConsumeData.amount || expenseConsumeData.amount === '') {
            showToastExpenseConsume('Please enter an amount!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        if (parseFloat(expenseConsumeData.amount) <= 0) {
            showToastExpenseConsume('Amount must be greater than zero!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        if (!expenseConsumeData.pay_mode) {
            showToastExpenseConsume('Please select a payment account!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        if (!expenseConsumeData.recorded_date) {
            showToastExpenseConsume('Please select a recorded date!', 'error');
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
    $(document).on('click', '.deleteExpenseConsumeBtn', function (e) {
        e.preventDefault();
        console.log('Delete button clicked');
        const btn = $(this);
        const conId = btn.data('id');
        const expenseName = btn.attr('data-expense-name') || 'this record';

        console.log('Delete data:', {conId, expenseName, allData: btn.data()});

        if (!conId) {
            showToastExpenseConsume('Invalid expense ID', 'error');
            return;
        }

        swal({   
            title: "Are you sure?",   
            text: "You will delete " + expenseName + "! This action cannot be undone.",   
            type: "warning",   
            showCancelButton: true,   
            confirmButtonColor: "#DD6B55",   
            confirmButtonText: "Yes, delete it!",   
            closeOnConfirm: false 
        }, function(){
            console.log('Delete confirmed, sending request for con_id:', conId);
            $.ajax({
                url: '<?= App::baseUrl() ?>/_ikawa/expense-consume/delete',
                method: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({ con_id: conId }),
                success: function (response) {
                    console.log('Delete response:', response);
                    if (response.success) {
                        swal("Deleted!", response.message, "success");
                        loadExpenseConsumes();
                    } else {
                        swal("Error!", response.message, "error");
                    }
                },
                error: function (xhr) {
                    console.error('Delete error:', xhr);
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
        resetForm();        $('#recorded_date').val('<?= date('Y-m-d') ?>');
    });

});
</script>