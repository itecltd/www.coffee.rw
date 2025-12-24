<!-- Edit Expense Consume Modal -->
<div class="modal animated bounce" id="editExpenseConsumeModal" role="dialog">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Expense Consume</h4>
            </div>
            <div class="modal-body">
               <div class="row">
                <input type="hidden" name="edit_con_id" id="edit_con_id">
                            
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class='chosen-select-act fm-cmp-mg'>
                                <label>Expense Type <span class="text-danger">*</span></label>
                                <select class='chosen' data-placeholder='Choose Expense Type...' name="edit_expense_id" id="edit_expense_id">
                                 <option value="">Select Expense Type</option>
                                <?php
                                $expensesUrl = App::baseUrl() . '/_ikawa/expenses/get-all';
                                $response = @file_get_contents($expensesUrl);

                                $expenses = [];

                                if ($response !== false) {
                                    $decoded = json_decode($response, true);

                                    if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                        $expenses = $decoded['data'] ?? [];
                                    }
                                }
                                ?>
                                   <?php if (!empty($expenses)): ?>
                                        <?php foreach ($expenses as $expense): ?>
                                            <option value="<?= htmlspecialchars($expense['expense_id']) ?>">
                                                <?= htmlspecialchars($expense['expense_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No expenses found</option>
                                    <?php endif; ?>
                                </select>
                              </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class='chosen-select-act fm-cmp-mg'>
                                <label>Station <span class="text-danger">*</span></label>
                                <select class='chosen' data-placeholder='Choose Station...' name="edit_station_id" id="edit_station_id">
                                 <option value="">Select Station</option>
                                <?php
                                $stationsUrl = App::baseUrl() . '/_ikawa/settings/stations';
                                $response = @file_get_contents($stationsUrl);

                                $stations = [];

                                if ($response !== false) {
                                    $decoded = json_decode($response, true);

                                    if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                        $stations = $decoded['data'] ?? [];
                                    }
                                }
                                ?>
                                   <?php if (!empty($stations)): ?>
                                        <?php foreach ($stations as $station): ?>
                                            <option value="<?= htmlspecialchars($station['st_id']) ?>">
                                                <?= htmlspecialchars($station['st_name']) ?> - <?= htmlspecialchars($station['st_location']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No stations found</option>
                                    <?php endif; ?>
                                </select>
                              </div>
                            </div>
                    </div>

                    <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-dollar"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="number" name="edit_amount" id="edit_amount" class="form-control" placeholder="Amount" min="0" step="0.01">
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class='chosen-select-act fm-cmp-mg'>
                                <label>Payment Mode <span class="text-danger">*</span></label>
                                <select class='chosen' data-placeholder='Choose Payment Mode...' name="edit_pay_mode" id="edit_pay_mode">
                                 <option value="">Select Payment Mode</option>
                                <?php
                                $accountsUrl = App::baseUrl() . '/_ikawa/accounts/get-all';
                                $response = @file_get_contents($accountsUrl);

                                $accounts = [];

                                if ($response !== false) {
                                    $decoded = json_decode($response, true);

                                    if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                        $accounts = $decoded['data'] ?? [];
                                    }
                                }
                                ?>
                                   <?php if (!empty($accounts)): ?>
                                        <?php foreach ($accounts as $account): ?>
                                            <option value="<?= htmlspecialchars($account['acc_id']) ?>">
                                                <?= htmlspecialchars($account['acc_name']) ?> - <?= htmlspecialchars($account['acc_reference_num']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No payment accounts found</option>
                                    <?php endif; ?>
                                </select>
                              </div>
                            </div>
                    </div>

                    <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="edit_payer_name" id="edit_payer_name" class="form-control" placeholder="Payer Name (Optional)">
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-calendar"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="date" name="edit_recorded_date" id="edit_recorded_date" class="form-control">
                                    </div>
                                </div>
                            </div>
                    </div>

                    <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-edit"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <textarea name="edit_description" id="edit_description" class="form-control" placeholder="Description (Optional)" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                    </div>
                 </div>
             <div class="modal-footer">
                <button type="button" id="updateExpenseConsumeBtn" class="btn btn-default">Save changes</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="breadcomb-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="breadcomb-list">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="breadcomb-wp">
                                <div class="breadcomb-icon">
                                    <i class="notika-icon notika-dollar"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Record Expense Consumption</h2>
                                    <p>
                                        Track and manage expense consumption with payments.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                            <button type="button" data-toggle="modal" data-target="#createExpenseConsumeModal" data-placement="left" title="Record New Expense" class="btn"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    
   

<div class="data-table-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="data-table-list">
                        <div class="basic-tb-hd">
                            <h2>Expense Consumption Records</h2>
                        </div>
                        <div class="table-responsive">
                            <table id="data-table-basic" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Expense Type</th>
                                        <th>Station</th>
                                        <th>Amount</th>
                                        <th>Payment Mode</th>
                                        <th>Payer Name</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="expenseconsumedata">
                                  <?php
                                    $apiUrl = App::baseUrl() . '/_ikawa/expense-consume/get-all';
                                    $json = @file_get_contents($apiUrl);
                                    $result = json_decode($json, true);
                                    if ($result && $result['success'] && !empty($result['data'])) {
                                        foreach ($result['data'] as $index => $record) {
                                            ?>
                                            <tr>
                                                <td><?php echo $index + 1 ?></td>
                                                <td><?php echo htmlspecialchars($record['expense_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($record['st_name'] ?? 'N/A') . ' - ' . htmlspecialchars($record['st_location'] ?? ''); ?></td>
                                                <td><?php echo number_format($record['amount'], 0); ?> RWF</td>
                                                <td><?php echo htmlspecialchars($record['payment_mode_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($record['payer_name'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($record['recorded_date']); ?></td>
                                                <td>
                                                <div class="button-icon-btn button-icon-btn-rd">
                                                <button
                                                 class="btn btn-default btn-icon-notika editExpenseConsumeBtn"
                                                title="Edit"
                                                data-id="<?= $record['con_id'] ?>"
                                                data-expense_id="<?= htmlspecialchars($record['expense_id']) ?>"
                                                data-station_id="<?= htmlspecialchars($record['station_id']) ?>"
                                                data-amount="<?= htmlspecialchars($record['amount']) ?>"
                                                data-pay_mode="<?= htmlspecialchars($record['pay_mode']) ?>"
                                                data-payer_name="<?= htmlspecialchars($record['payer_name'] ?? '') ?>"
                                                data-description="<?= htmlspecialchars($record['description'] ?? '') ?>"
                                                data-recorded_date="<?= htmlspecialchars($record['recorded_date']) ?>">
                                                <i class="notika-icon notika-edit"></i>
                                            </button>
                                            <button
                                                 class="btn btn-danger btn-icon-notika deleteExpenseConsumeBtn"
                                                title="Delete"
                                                data-id="<?= $record['con_id'] ?>"
                                                data-expense_name="<?= htmlspecialchars($record['expense_name'] ?? 'this record') ?>">
                                                <i class="notika-icon notika-close"></i>
                                            </button>
                                             </div>
                                            </td>
                                           </tr>
                                       <?php  }
                                    } else {
                                        ?>
                                          <tr><td colspan="8" class="text-center">No expense consumption records found</td></tr>
                                    <?php }  ?>
                                </tbody>
                                
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


     <!-- Create new expense consume modal -->
    <div class="modal fade" id="createExpenseConsumeModal" role="dialog">
        <div class="modal-dialog modal-large">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Record New Expense Consumption</h4>
                </div>
                <div class="modal-body">
                <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class='chosen-select-act fm-cmp-mg'>
                                <label>Expense Type <span class="text-danger">*</span></label>
                                <select class='chosen' data-placeholder='Choose Expense Type...' name="expense_id" id="expense_id">
                                 <option value="">Select Expense Type</option>
                                <?php
                                $expensesUrl = App::baseUrl() . '/_ikawa/expenses/get-all';
                                $response = @file_get_contents($expensesUrl);

                                $expenses = [];

                                if ($response !== false) {
                                    $decoded = json_decode($response, true);

                                    if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                        $expenses = $decoded['data'] ?? [];
                                    }
                                }
                                ?>
                                   <?php if (!empty($expenses)): ?>
                                        <?php foreach ($expenses as $expense): ?>
                                            <option value="<?= htmlspecialchars($expense['expense_id']) ?>">
                                                <?= htmlspecialchars($expense['expense_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No expenses found</option>
                                    <?php endif; ?>
                                </select>
                              </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class='chosen-select-act fm-cmp-mg'>
                                <label>Station <span class="text-danger">*</span></label>
                                <select class='chosen' data-placeholder='Choose Station...' name="station_id" id="station_id">
                                 <option value="">Select Station</option>
                                <?php
                                $stationsUrl = App::baseUrl() . '/_ikawa/settings/stations';
                                $response = @file_get_contents($stationsUrl);

                                $stations = [];

                                if ($response !== false) {
                                    $decoded = json_decode($response, true);

                                    if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                        $stations = $decoded['data'] ?? [];
                                    }
                                }
                                ?>
                                   <?php if (!empty($stations)): ?>
                                        <?php foreach ($stations as $station): ?>
                                            <option value="<?= htmlspecialchars($station['st_id']) ?>">
                                                <?= htmlspecialchars($station['st_name']) ?> - <?= htmlspecialchars($station['st_location']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No stations found</option>
                                    <?php endif; ?>
                                </select>
                              </div>
                            </div>
                    </div>

                    <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-dollar"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="number" name="amount" id="amount" class="form-control" placeholder="Amount *" min="0" step="0.01" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class='chosen-select-act fm-cmp-mg'>
                                <label>Payment Mode <span class="text-danger">*</span></label>
                                <select class='chosen' data-placeholder='Choose Payment Mode...' name="pay_mode" id="pay_mode">
                                 <option value="">Select Payment Mode</option>
                                <?php
                                $accountsUrl = App::baseUrl() . '/_ikawa/accounts/get-all';
                                $response = @file_get_contents($accountsUrl);

                                $accounts = [];

                                if ($response !== false) {
                                    $decoded = json_decode($response, true);

                                    if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
                                        $accounts = $decoded['data'] ?? [];
                                    }
                                }
                                ?>
                                   <?php if (!empty($accounts)): ?>
                                        <?php foreach ($accounts as $account): ?>
                                            <option value="<?= htmlspecialchars($account['acc_id']) ?>">
                                                <?= htmlspecialchars($account['acc_name']) ?> - <?= htmlspecialchars($account['acc_reference_num']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No payment accounts found</option>
                                    <?php endif; ?>
                                </select>
                              </div>
                            </div>
                    </div>

                    <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-support"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="text" name="payer_name" id="payer_name" class="form-control" placeholder="Payer Name (Optional)">
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-calendar"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <input type="date" name="recorded_date" id="recorded_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                            </div>
                    </div>

                    <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ic-cmp-int">
                                    <div class="form-ic-cmp">
                                        <i class="notika-icon notika-edit"></i>
                                    </div>
                                    <div class="nk-int-st">
                                        <textarea name="description" id="description" class="form-control" placeholder="Description (Optional)" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="saveExpenseConsumeBtn" class="btn btn-default">Save Expense Consume</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
