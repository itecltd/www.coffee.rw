
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
                        record.consumer_name || '-',
                        record.recorded_date,
                        `<div class="button-icon-btn button-icon-btn-rd">
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

    // Handle category change - load expenses by category
    $(document).on('change', '#expense_category', function() {
        var categId = $(this).val();
        var $expenseSelect = $('#expense_id');
        
        if (!categId) {
            $expenseSelect.html('<option value="">Select Category First</option>');
            $expenseSelect.prop('disabled', true);
            $expenseSelect.trigger('chosen:updated');
            return;
        }
        
        // Show loading state
        $expenseSelect.html('<option value="">Loading expenses...</option>');
        $expenseSelect.prop('disabled', true);
        $expenseSelect.trigger('chosen:updated');
        
        // Load expenses for the selected category
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/expenses/by-category/' + categId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    var options = '<option value="">Select Expense Type</option>';
                    response.data.forEach(function(expense) {
                        options += `<option value="${expense.expense_id}">${expense.expense_name}</option>`;
                    });
                    $expenseSelect.html(options);
                    $expenseSelect.prop('disabled', false);
                } else {
                    $expenseSelect.html('<option value="">No expenses in this category</option>');
                    $expenseSelect.prop('disabled', true);
                }
                $expenseSelect.trigger('chosen:updated');
            },
            error: function() {
                $expenseSelect.html('<option value="">Error loading expenses</option>');
                $expenseSelect.prop('disabled', true);
                $expenseSelect.trigger('chosen:updated');
                showToastExpenseConsume('Failed to load expenses for this category', 'error');
            }
        });
    });

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
                                                ${account.acc_name} (${account.acc_reference_num}) - Bal: ${new Intl.NumberFormat('en-RW').format(account.balance)} RWF
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
                    <td>${entry.account_name}</td>
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

        // Prevent double submission
        if ($(btn).prop('disabled')) {
            console.log('Button already disabled, preventing double submission');
            return;
        }

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
            data: JSON.stringify(expenseConsumeData),
            success: function (response, textStatus, xhr) {
                console.log('Raw Response:', xhr.responseText);
                console.log('Response Type:', typeof response);
                console.log('Status:', textStatus);
                
                var parsedResponse = null;
                
                // Handle response parsing
                if (typeof response === 'object' && response !== null) {
                    // Already parsed by jQuery
                    parsedResponse = response;
                } else if (typeof response === 'string') {
                    // Try to clean and parse the string
                    try {
                        // Trim whitespace and try to find JSON
                        var cleanedResponse = response.trim();
                        
                        // Try to extract JSON if there's extra content
                        var jsonMatch = cleanedResponse.match(/\{[\s\S]*\}/);
                        if (jsonMatch) {
                            cleanedResponse = jsonMatch[0];
                        }
                        
                        parsedResponse = JSON.parse(cleanedResponse);
                    } catch (e) {
                        console.error('JSON Parse Error:', e);
                        console.error('Response Text:', response);
                        
                        // Check if the data was actually saved despite parse error
                        showToastExpenseConsume('Transaction completed. Please refresh to verify.', 'warning');
                        setButtonLoading(btn, false);
                        
                        // Refresh the list after 1 second
                        setTimeout(function() {
                            $('#createExpenseConsumeModal').modal('hide');
                            resetForm();
                            loadExpenseConsumes();
                        }, 1500);
                        return;
                    }
                }
                
                console.log('Parsed Response:', parsedResponse);
                
                // Check if parsing was successful
                if (parsedResponse && parsedResponse.success) {
                    showToastExpenseConsume(parsedResponse.message || 'Expense consume recorded successfully', 'success');
                    $('#createExpenseConsumeModal').modal('hide');
                    resetForm();
                    loadExpenseConsumes();
                } else if (parsedResponse) {
                    showToastExpenseConsume(parsedResponse.message || 'An error occurred', 'error');
                } else {
                    showToastExpenseConsume('Unexpected response format', 'error');
                }
                
                setButtonLoading(btn, false);
            },
            error: function (xhr, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.error('Response Text:', xhr.responseText);
                console.error('Status:', xhr.status);
                
                let msg = 'Something went wrong';
                
                // Try to parse error response
                try {
                    if (xhr.responseText) {
                        var errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.message) {
                            msg = errorResponse.message;
                        }
                    }
                } catch (e) {
                    console.error('Could not parse error response:', e);
                    // Check if response contains success indication
                    if (xhr.responseText && xhr.responseText.includes('success')) {
                        msg = 'Transaction may have succeeded. Please refresh to check.';
                        setTimeout(function() {
                            loadExpenseConsumes();
                        }, 1000);
                    }
                }
                
                showToastExpenseConsume(msg, 'error');
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

    // Edit functionality removed: edit buttons and modal are deprecated for expense consume records

    // Handle Delete Expense Consume button
    $(document).on('click', '.deleteExpenseConsumeBtn', function (e) {
        e.preventDefault();
        const btn = $(this);
        const conId = btn.data('id');
        const expenseName = btn.data('expense-name') || 'this record';

        if (!conId) {
            showToastExpenseConsume('Invalid expense ID', 'error');
            return;
        }

        swal({   
            title: "Are you sure?",   
            text: "This will cancel the expense and refund the amount back to the account.",   
            type: "warning",   
            showCancelButton: true,   
            confirmButtonText: "Yes, cancel it!",
            cancelButtonText: "No, keep it"
        }).then(function(isConfirm){
            if (isConfirm) {
                $.ajax({
                    url: '<?= App::baseUrl() ?>/_ikawa/expense-consume/delete',
                    method: 'POST',
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify({ con_id: conId }),
                    success: function (response) {
                        if (response.success) {
                            swal("Cancelled!", response.message, "success");
                            loadExpenseConsumes();
                        } else {
                            swal("Error!", response.message, "error");
                        }
                    },
                    error: function (xhr) {
                        let msg = 'Something went wrong';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            msg = xhr.responseText;
                        }
                        
                        // Show detailed error in console for debugging
                        console.error('Delete error details:', {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText,
                            responseJSON: xhr.responseJSON
                        });
                        
                        swal("Error!", msg, "error");
                    }
                });
            }
        });
    });

    // Clear modal on close
    $('#createExpenseConsumeModal').on('hidden.bs.modal', function () {
        resetForm();
        $('#recorded_date').val('<?= date('Y-m-d') ?>');
        
        // Reset category and expense dropdowns
        $('#expense_category').val('').trigger('chosen:updated');
        $('#expense_id').html('<option value="">Select Category First</option>');
        $('#expense_id').prop('disabled', true).trigger('chosen:updated');
    });
    
    // When modal opens, ensure proper state
    $('#createExpenseConsumeModal').on('shown.bs.modal', function () {
        // Make sure category is reset and expense is disabled
        $('#expense_id').prop('disabled', true);
    });

});
</script>