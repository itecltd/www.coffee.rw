<?php
require_once __DIR__ . '/../../_ikawa/config/App.php';

?>

<div class="breadcomb-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="breadcomb-list">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="breadcomb-wp">
                                <div class="breadcomb-icon">
                                    <i class="notika-icon notika-bar-chart"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Account Operations Statement</h2>
                                    <p>View expense transactions and recharge history for payment accounts</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="data-table-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2><i class="notika-icon notika-search"></i> Filter Statement</h2>
                    </div>
                    <div style="padding: 20px;">
                        <div class="row">
                            <!-- Account Select -->
                            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                <div class='chosen-select-act fm-cmp-mg'>
                                    <select class='chosen' data-placeholder='Select Account...' name="filter_account" id="filter_account">
                                        <option value="">Loading accounts...</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Statement Type -->
                            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                <div class='chosen-select-act fm-cmp-mg'>
                                    <select class='chosen' data-placeholder='Statement Type...' name="statement_type" id="statement_type">
                                        <option value="expense" selected>Expense Transactions</option>
                                        <option value="recharge">Recharges</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Date From -->
                            <div class="col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <input type="date" class="form-control" id="filter_date_from" value="<?= date('Y-m-01') ?>">
                                </div>
                            </div>

                            <!-- Date To -->
                            <div class="col-lg-2 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <input type="date" class="form-control" id="filter_date_to" value="<?= date('Y-m-t') ?>">
                                </div>
                            </div>

                            <!-- Load Button -->
                            <div class="col-lg-1 col-md-6 col-sm-6 col-xs-12">
                                <button type="button" id="loadReportBtn" class="btn btn-success btn-sm">
                                    <i class="notika-icon notika-search"></i> Load
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Results Section -->
<div class="data-table-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2>Statement Results</h2>
                    </div>
                    <div class="table-responsive">
                        <div id="reportAlert" style="margin: 15px;"></div>
                        <table id="reportTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Reference</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Charges</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody">
                                <tr><td colspan="6" class="text-center">Select an account and click Load to view statement</td></tr>
                            </tbody>
                            <tfoot id="reportFooter" style="display:none;">
                                <tr style="font-weight: bold; background-color: #f5f5f5;">
                                    <td colspan="3" class="text-right">Totals:</td>
                                    <td id="totalExpense">0.00</td>
                                    <td id="totalCharges">0.00</td>
                                    <td id="totalGrand">0.00</td>
                                </tr>
                                <tr style="font-weight: bold; background-color: #e8f5e9;">
                                    <td colspan="5" class="text-right">Current Account Balance:</td>
                                    <td id="currentBalance">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadAccountReport(){
    var acc = $('#filter_account').val();
    if(!acc){ 
        swal('Error', 'Please select an account', 'error');
        return; 
    }
    
    var stmtType = $('#statement_type').val();
    var payload = {
        account_id: parseInt(acc),
        date_from: $('#filter_date_from').val(),
        date_to: $('#filter_date_to').val(),
        display: stmtType
    };

    $('#reportBody').html('<tr><td colspan="6" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
    $('#reportFooter').hide();

    $.ajax({
        url: App.baseUrl() + '/_ikawa/reports/account-operations',
        method: 'POST',
        data: JSON.stringify(payload),
        contentType: 'application/json',
        success: function(json){
            if(!json || json.status !== 'success'){
                $('#reportBody').html('<tr><td colspan="6" class="text-center text-danger">'+(json.message||'Error loading statement')+'</td></tr>');
                $('#reportAlert').html('<div class="alert alert-danger">'+(json.message||'Error')+'</div>').show();
                return;
            }
            
            var rows = json.data.rows || [];
            var html = '';
            var totalExpense = 0, totalCharges = 0, totalRecharge = 0;
            
            if(stmtType === 'expense'){
                rows.forEach(function(r){
                    if(r.type === 'expense'){
                        // Main expense row
                        var dateStr = r.date ? r.date.substring(0, 16).replace('T', ' ') : '';
                        html += '<tr>'+
                            '<td>'+dateStr+'</td>'+
                            '<td>'+r.reference_id+'</td>'+
                            '<td>Expense Transaction</td>'+
                            '<td class="text-right">'+parseFloat(r.expense_amount).toFixed(2)+'</td>'+
                            '<td></td>'+
                            '<td class="text-right">'+parseFloat(r.total).toFixed(2)+'</td>'+
                            '</tr>';
                        
                        totalExpense += parseFloat(r.expense_amount);
                        
                        // Charges sub-row
                        if(parseFloat(r.charges) > 0){
                            html += '<tr class="text-muted" style="font-size: 0.9em; background-color: #f9f9f9;">'+
                                '<td></td>'+
                                '<td></td>'+
                                '<td style="padding-left: 30px;"><em>Transaction Charges</em></td>'+
                                '<td></td>'+
                                '<td class="text-right">'+parseFloat(r.charges).toFixed(2)+'</td>'+
                                '<td></td>'+
                                '</tr>';
                            totalCharges += parseFloat(r.charges);
                        }
                    }
                });
            } else {
                rows.forEach(function(r){
                    if(r.type === 'recharge'){
                        var dateStr = r.date ? r.date.substring(0, 16).replace('T', ' ') : '';
                        html += '<tr>'+
                            '<td>'+dateStr+'</td>'+
                            '<td>'+r.reference_id+'</td>'+
                            '<td>Account Recharge</td>'+
                            '<td></td>'+
                            '<td></td>'+
                            '<td class="text-right text-success">'+parseFloat(r.total).toFixed(2)+'</td>'+
                            '</tr>';
                        totalRecharge += parseFloat(r.total);
                    }
                });
            }
            
            if(rows.length === 0){
                html = '<tr><td colspan="6" class="text-center">No transactions found for the selected period</td></tr>';
                $('#reportFooter').hide();
            } else {
                $('#reportFooter').show();
                if(stmtType === 'expense'){
                    $('#totalExpense').text(totalExpense.toFixed(2));
                    $('#totalCharges').text(totalCharges.toFixed(2));
                    $('#totalGrand').text((totalExpense + totalCharges).toFixed(2));
                } else {
                    $('#totalExpense').text('0.00');
                    $('#totalCharges').text('0.00');
                    $('#totalGrand').text(totalRecharge.toFixed(2));
                }
                $('#currentBalance').text(parseFloat(json.data.balance || 0).toFixed(2));
            }
            
            $('#reportBody').html(html);
            $('#reportAlert').html('').hide();
        },
        error: function(xhr, status, err){
            $('#reportBody').html('<tr><td colspan="6" class="text-center text-danger">Error loading statement. Please try again.</td></tr>');
            $('#reportAlert').html('<div class="alert alert-danger">Failed to load statement</div>').show();
            console.error('Report load error', status, err, xhr.responseText);
        }
    });
}

$(document).on('click', '#loadReportBtn', function(){ 
    loadAccountReport(); 
});

// Load accounts dropdown via AJAX
function loadAccountsDropdown(){
    var apiUrl = '<?= $app->baseUrl() ?>/_ikawa/accounts/get-all';
    console.log('Loading accounts from:', apiUrl);
    
    $.ajax({
        url: apiUrl,
        method: 'GET',
        dataType: 'json',
        success: function(json){
            console.log('Accounts loaded:', json);
            var sel = $('#filter_account');
            sel.empty();
            
            if(!json || json.status !== 'success' || !Array.isArray(json.data)){
                console.warn('Invalid accounts response:', json);
                sel.append('<option value="">No accounts available</option>');
                if($.fn.chosen){
                    sel.trigger('chosen:updated');
                }
                return;
            }
            
            sel.append('<option value="">Select Account</option>');
            json.data.forEach(function(a){
                sel.append('<option value="'+a.acc_id+'">'+a.acc_name+' ('+(a.Mode_names||'')+')'+' </option>');
            });
            
            console.log('Accounts populated:', json.data.length);
            
            // Reinitialize chosen after loading data
            if($.fn.chosen){
                sel.trigger('chosen:updated');
            }
        },
        error: function(xhr, status, err){
            console.error('Error loading accounts:', status, err, xhr.responseText);
            $('#filter_account').html('<option value="">Error loading accounts</option>');
            if($.fn.chosen){
                $('#filter_account').trigger('chosen:updated');
            }
        }
    });
}

// Initialize chosen select and load accounts
$(document).ready(function(){
    if($.fn.chosen){
        $('.chosen').chosen({
            width: '100%'
        });
    }
    loadAccountsDropdown();
});
</script>
