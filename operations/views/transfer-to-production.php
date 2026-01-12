<!-- Transfer Modal -->
<div class="modal fade" id="transferModal" role="dialog">
    <div class="modal-dialog modal-lg" style="width: 95%; max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Transfer Stock to Production</h4>
            </div>
            <div class="modal-body">
                <div class="row mg-b-15">
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label>From Station</label>
                            <input type="text" class="form-control" value="<?= $_SESSION['location_name'] ?? 'Current Station' ?>" readonly>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label>Transfer Date *</label>
                            <input type="date" id="transfer_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <div class="form-group">
                            <label>Notes</label>
                            <input type="text" id="transfer_notes" class="form-control" placeholder="Optional notes">
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered" id="transferEntryTable">
                        <thead>
                            <tr>
                                <th style="width:5%;"><input type="checkbox" id="selectAllStock"></th>
                                <th>Category Type</th>
                                <th>Supplier</th>
                                <th>Available Qty</th>
                                <th style="width:15%;">Transfer Qty</th>
                            </tr>
                        </thead>
                        <tbody id="availableStockBody">
                            <tr><td colspan="5" class="text-center">Loading available stock...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveTransferBtn" class="btn btn-primary">Transfer Selected</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Transfer Details Modal -->
<div class="modal fade" id="viewDetailsModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Transfer Details - <span id="detailRefNo"></span></h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category Type</th>
                                <th>Unity</th>
                                <th>Supplier</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="transferDetailsBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
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
                                    <i class="notika-icon notika-sent"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Transfer to Production</h2>
                                    <p>Transfer stock from station to production facility</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                                <button type="button" data-toggle="modal" data-target="#transferModal" class="btn"><i class="fa fa-exchange"></i> New Transfer</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transfer History Table -->
<div class="data-table-area mg-tb-15">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2>Transfer History</h2>
                    </div>
                    <div class="table-responsive">
                        <table id="transfer-history-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Reference No</th>
                                    <th>Destination</th>
                                    <th>Items</th>
                                    <th>Total Qty</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="transferHistoryData">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
