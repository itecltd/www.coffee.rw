<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" role="dialog">
    <div class="modal-dialog modal-lg" style="width: 95%; max-width: 1400px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add New Stock</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="stockEntryTable">
                        <thead>
                            <tr>
                                <th style="width: 18%;">Category</th>
                                <th style="width: 25%;">Category Type / Unity</th>
                                <th style="width: 18%;">Supplier</th>
                                <th style="width: 12%;">Quantity</th>
                                <th style="width: 12%;">Unit Price</th>
                                <th style="width: 10%;">Total</th>
                                <th style="width: 5%;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="stockEntryBody">
                            <!-- Rows will be added dynamically -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-right"><strong>Grand Total:</strong></td>
                                <td><strong id="grandTotal">0 RWF</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <button type="button" id="addRowBtn" class="btn btn-success btn-sm">
                    <i class="fa fa-plus"></i> Add Row
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveAllStockBtn" class="btn btn-primary">Save All Stock</button>
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
                                    <i class="notika-icon notika-windows"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Manage Station Stock</h2>
                                    <p>Add and manage stock inventory for your station</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                            <div class="breadcomb-report">
                                <button type="button" data-toggle="modal" data-target="#addStockModal" class="btn"><i class="fa fa-plus"></i> Add Stock</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Stock Records Table -->
<div class="data-table-area mg-tb-15">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2>Detailed Stock Records</h2>
                    </div>
                    <div class="table-responsive">
                        <table id="detailed-stock-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category Type</th>
                                    <th>Unity</th>
                                    <th>Supplier</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total Price</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="detailedStockData">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Stock Table (Grouped by Station, Category Type & Supplier) -->
<div class="data-table-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="data-table-list">
                    <div class="basic-tb-hd">
                        <h2>Station Stock Summary</h2>
                        <p>Stock grouped by category type and supplier</p>
                    </div>
                    <div class="table-responsive">
                        <table id="summary-stock-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Station</th>
                                    <th>Category Type</th>
                                    <th>Supplier</th>
                                    <th>Total Quantity</th>
                                </tr>
                            </thead>
                            <tbody id="summaryStockData">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
