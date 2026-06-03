<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_folder = basename(dirname($_SERVER['PHP_SELF']));
?>

<div class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-chair"></i>
        <h4>Table & Chair<br>Rental System</h4>
    </div>
    <ul class="sidebar-menu">
        <li class="<?= $current_page == 'index.php' ? 'active' : '' ?>">
            <a href="/InformationManagement_Database/index.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="<?= $current_folder == 'clients' ? 'active' : '' ?>">
            <a href="/InformationManagement_Database/clients/list.php">
                <i class="fas fa-users"></i>
                <span>Clients</span>
            </a>
        </li>
        <li class="<?= $current_folder == 'employees' ? 'active' : '' ?>">
            <a href="/InformationManagement_Database/employees/list.php">
                <i class="fas fa-user-tie"></i>
                <span>Employees</span>
            </a>
        </li>
        <li class="<?= $current_page == 'manage.php' ? 'active' : '' ?>">
            <a href="/InformationManagement_Database/items/manage.php">
                <i class="fas fa-boxes"></i>
                <span>Inventory</span>
            </a>
        </li>
        <li class="<?= $current_page == 'availability.php' ? 'active' : '' ?>">
            <a href="/InformationManagement_Database/items/availability.php">
                <i class="fas fa-calendar"></i>
                <span>Availability Calendar</span>
            </a>
        </li>
        <li class="<?= $current_folder == 'transactions' ? 'active' : '' ?>">
            <a href="/InformationManagement_Database/transactions/list.php">
                <i class="fas fa-shopping-cart"></i>
                <span>Transactions</span>
            </a>
        </li>
        <li class="<?= $current_folder == 'repairs' ? 'active' : '' ?>">
            <a href="/InformationManagement_Database/repairs/list.php">
                <i class="fas fa-tools"></i>
                <span>Repair Fees</span>
            </a>
        </li>
        <li class="<?= $current_folder == 'reports' ? 'active' : '' ?>">
            <a href="/InformationManagement_Database/reports/index.php">
                <i class="fas fa-chart-line"></i>
                <span>Reports</span>
            </a>
        </li>
    </ul>
</div>