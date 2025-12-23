
<script>
$(document).ready(function () {

    // Toast notification function
    function showToast(message, type) {
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

    // Button loading state
    function setButtonLoading(btn, isLoading) {
        if (isLoading) {
            $(btn).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
        } else {
            $(btn).prop('disabled', false).html($(btn).data('original-text') || 'Save');
        }
    }

    // Load accounts
    function loadAccounts() {
        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/accounts/get-all',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success && response.data) {
                    var html = '';
                    $.each(response.data, function (index, account) {
                        html += '<tr>';
                        html += '<td>' + (index + 1) + '</td>';
                        html += '<td>' + account.acc_name + '</td>';
                        html += '<td>' + account.acc_reference_num + '</td>';
                        html += '<td>' + account.Mode_names + '</td>';
                        html += '<td>' + (account.st_name || 'N/A') + '</td>';
                        html += '<td>' + parseInt(account.balance).toLocaleString() + ' RWF</td>';
                        html += '<td>';
                        html += '<div class="button-icon-btn button-icon-btn-rd">';
                        html += '<button class="btn btn-default btn-icon-notika editAccountBtn" ';
                        html += 'title="Edit Account" ';
                        html += 'data-id="' + account.acc_id + '" ';
                        html += 'data-acc_name="' + account.acc_name + '" ';
                        html += 'data-acc_reference_num="' + account.acc_reference_num + '" ';
                        html += 'data-mode_id="' + account.mode_id + '" ';
                        html += 'data-st_id="' + account.st_id + '">';
                        html += '<i class="notika-icon notika-edit"></i>';
                        html += '</button>';
                        html += '<button class="btn btn-danger btn-icon-notika deleteAccountBtn" ';
                        html += 'title="Delete Account" ';
                        html += 'data-id="' + account.acc_id + '" ';
                        html += 'data-acc_name="' + account.acc_name + '">';
                        html += '<i class="notika-icon notika-close"></i>';
                        html += '</button>';
                        html += '</div>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    $('#accountsdata').html(html);

                    // Reinitialize DataTable
                    if ($.fn.DataTable.isDataTable('#data-table-basic')) {
                        $('#data-table-basic').DataTable().destroy();
                    }
                    $('#data-table-basic').DataTable({
                        pageLength: 10,
                        lengthChange: true,
                        searching: true,
                        ordering: true,
                        autoWidth: false
                    });
                } else {
                    $('#accountsdata').html('<tr><td colspan="7" class="text-center">No accounts found</td></tr>');
                }
            },
            error: function () {
                showToast('Failed to load accounts', 'error');
            }
        });
    }

    // Handle Save Account button
    $(document).on('click', '#saveAccountBtn', function () {
        const btn = this;
        setButtonLoading(btn, true);
        
        const accountData = {
            acc_name: $('#acc_name').val().trim(),
            acc_reference_num: $('#acc_reference_num').val().trim(),
            mode_id: $('#mode_id').val(),
            st_id: $('#st_id').val()
        };

        if (!accountData.acc_name || !accountData.acc_reference_num || !accountData.mode_id || !accountData.st_id) {
            showToast('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/accounts/create',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(accountData),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#createAccountModal').modal('hide');
                    $('#createAccountModal input').val('');
                    $('#createAccountModal select').val('').trigger('chosen:updated');
                    loadAccounts();
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function (xhr) {
                let msg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showToast(msg, 'error');
            },
            complete: function () {
                setButtonLoading(btn, false);
            }
        });
    });

    // Handle Edit Account button click
    $(document).on('click', '.editAccountBtn', function () {
        const accId = $(this).data('id');
        const accName = $(this).data('acc_name');
        const accReferenceNum = $(this).data('acc_reference_num');
        const modeId = $(this).data('mode_id');
        const stId = $(this).data('st_id');

        $('#edit_acc_id').val(accId);
        $('#edit_acc_name').val(accName);
        $('#edit_acc_reference_num').val(accReferenceNum);
        $('#edit_mode_id').val(modeId).trigger('chosen:updated');
        $('#edit_st_id').val(stId).trigger('chosen:updated');

        $('#editAccountModal').modal('show');
    });

    // Handle Update Account button
    $(document).on('click', '#updateAccountBtn', function () {
        const btn = this;
        setButtonLoading(btn, true);

        const accountData = {
            acc_id: $('#edit_acc_id').val(),
            acc_name: $('#edit_acc_name').val().trim(),
            acc_reference_num: $('#edit_acc_reference_num').val().trim(),
            mode_id: $('#edit_mode_id').val(),
            st_id: $('#edit_st_id').val()
        };

        if (!accountData.acc_name || !accountData.acc_reference_num || !accountData.mode_id || !accountData.st_id) {
            showToast('Please fill all required fields!', 'error');
            setButtonLoading(btn, false);
            return;
        }

        $.ajax({
            url: '<?= App::baseUrl() ?>/_ikawa/accounts/update',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(accountData),
            success: function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#editAccountModal').modal('hide');
                    loadAccounts();
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function (xhr) {
                let msg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showToast(msg, 'error');
            },
            complete: function () {
                setButtonLoading(btn, false);
            }
        });
    });

    // Handle Delete Account button
    $(document).on('click', '.deleteAccountBtn', function () {
        const accId = $(this).data('id');
        const accName = $(this).data('acc_name');

        if (confirm('Are you sure you want to delete account: ' + accName + '?')) {
            $.ajax({
                url: '<?= App::baseUrl() ?>/_ikawa/accounts/delete/' + accId,
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        loadAccounts();
                    } else {
                        showToast(response.message, 'error');
                    }
                },
                error: function (xhr) {
                    let msg = 'Failed to delete account';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    showToast(msg, 'error');
                }
            });
        }
    });

    // Clear modal on close
    $('#createAccountModal').on('hidden.bs.modal', function () {
        $(this).find('input').val('');
        $(this).find('select').val('').trigger('chosen:updated');
    });

});
</script>
