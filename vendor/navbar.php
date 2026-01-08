<!-- Main Menu area start -->
<div class="main-menu-area mg-tb-40">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <ul class="nav nav-tabs notika-menu-wrap menu-it-icon-pro">
                    <li class="active">
                        <a data-toggle="tab" href="#Dashboard">
                            <i class="notika-icon notika-house"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#UsersRoles">
                            <i class="notika-icon notika-support"></i> Users & Roles
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#Settings">
                            <i class="notika-icon notika-settings"></i> Settings
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#ProductSetting">
                            <i class="notika-icon notika-house"></i>Products Management
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#Expenses">
                            <i class="notika-icon notika-edit"></i>Expense Management
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#Finance">
                            <i class="notika-icon notika-dollar"></i> Financial Management
                        </a>
                    </li>
                   <li class="menu-item">
                    <a data-toggle="tab" href="#Advances">
                        <i class="notika-icon notika-form"></i> Advances Management
                    </a>
                </li>
                </ul>

                <div class="tab-content custom-menu-content">
                    <!-- Dashboard -->
                    <div id="Dashboard" class="tab-pane in active notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                            <li><a href="javascript:void(0)" onclick="loadContent('dashboard')">Overview</a></li>
                            <li><a href="javascript:void(0)" onclick="loadContent('analytics')">Analytics</a></li>
                        </ul>
                    </div>
                    <!-- Users & Roles Content -->
                    <div id="UsersRoles" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                         <li><a href="javascript:void(0)" onclick="loadContent('manage-users')">Manage Users</a></li>
                         <li><a href="javascript:void(0)" onclick="loadContent('manage-roles')">Manage Roles</a></li>
                        </ul>
                    </div>
               <!-- Settings -->
                    <div id="Settings" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                            <li><a href="javascript:void(0)" onclick="loadContent('profile')">Company Profile</a></li>
                            <li><a href="javascript:void(0)" onclick="loadContent('stations')">Stations</a></li>
                             <li><a href="javascript:void(0)" onclick="loadContent('suppliers')">Suppliers</a></li>
                             <li><a href="javascript:void(0)" onclick="loadContent('company-clients')">Clients</a></li>                      
                        </ul>
                    </div>
                    <!-- Expenses -->
                    <div id="Expenses" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                            <li><a href="javascript:void(0)" onclick="loadContent('manage-expense-categories')">Expense Categories</a></li>
                            <li><a href="javascript:void(0)" onclick="loadContent('manage-expenses')">Expense Type</a></li>
                            <li><a href="javascript:void(0)" onclick="loadContent('manage-expense-consumers')">Expense Consumers</a></li>
             
                            <li><a href="javascript:void(0)" onclick="loadContent('manage-expense-consume')">Expense Consumption</a></li>
                     
                        </ul>
                    </div>
                    <!-- Products -->
                    <div id="ProductSetting" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                          <li><a href="javascript:void(0)" onclick="loadContent('unity')">Unity</a></li>
                          <li><a href="javascript:void(0)" onclick="loadContent('coffee-categories')">Product Categories</a></li>
                          <li><a href="javascript:void(0)" onclick="loadContent('coffee-types')">Product Types</a></li>
                          <li><a href="javascript:void(0)" onclick="loadContent('coffee-types-assign-unity')">Assign Product</a></li>
                        </ul>
                    </div>
                     <!-- Financial Management -->
                    <div id="Finance" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                        <li><a href="javascript:void(0)" onclick="loadContent('manage-accounts')">Accounts</a></li>
                        <li><a href="javascript:void(0)" onclick="loadContent('accounts-transfer')">Accounts Transfer</a></li>
                        </ul>
                    </div>

                    <div id="Advances" class="tab-pane notika-tab-menu-bg animated flipInX">
                        <ul class="notika-main-menu-dropdown">
                        <li><a href="javascript:void(0)" onclick="loadContent('request-Advance')">Request Advance</a></li>
                        <li><a href="javascript:void(0)" onclick="loadContent('pending-request-Advance')">Pending Requests Advance</a></li>
                        <li><a href="javascript:void(0)" onclick="loadContent('disburse-approved-Advance')">Advance Disbursement</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


