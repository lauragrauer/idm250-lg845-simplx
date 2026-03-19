<?php $current = basename($_SERVER['PHP_SELF']); ?>
<nav class="sidebar">
    <a href="dashboard.php" class="sidebar-brand">
        <img src="simplx-logo.png" alt="Simplx" class="sidebar-logo">
    </a>
    <ul>
        <li class="<?php if ($current == 'dashboard.php') echo 'active'; ?>">
            <a href="dashboard.php">Dashboard</a>
        </li>
        <li class="<?php if ($current == 'sku_management.php' || $current == 'sku_edit.php') echo 'active'; ?>">
            <a href="sku_management.php">SKU Management</a>
        </li>
        <li class="<?php if ($current == 'inventory_internal.php') echo 'active'; ?>">
            <a href="inventory_internal.php">Internal Inventory</a>
        </li>
        <li class="<?php if ($current == 'inventory_warehouse.php') echo 'active'; ?>">
            <a href="inventory_warehouse.php">Warehouse Inventory</a>
        </li>
        <li class="<?php if ($current == 'mpl_records.php' || $current == 'mpl_form.php') echo 'active'; ?>">
            <a href="mpl_records.php">MPL Records</a>
        </li>
        <li class="<?php if ($current == 'order_records.php' || $current == 'order_form.php') echo 'active'; ?>">
            <a href="order_records.php">Orders</a>
        </li>
        <li class="<?php if ($current == 'api-docs.php') echo 'active'; ?>">
            <a href="api-docs.php">API Docs</a>
        </li>
        <li class="sidebar-logout">
            <a href="logout.php">Logout</a>
        </li>
    </ul>
</nav>
