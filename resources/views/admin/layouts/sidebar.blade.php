<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-shopping-bag"></i>
        </div>
        <div class="sidebar-brand-text mx-2">PangAIa Admin</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Ecommerce Management
    </div>    <!-- Nav Item - Products -->
    <li class="nav-item {{ request()->routeIs('admin.products.*') && !request()->routeIs('admin.products.index') && !request()->routeIs('admin.products.create') && !request()->routeIs('admin.products.edit') && !request()->routeIs('admin.products.show') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseProducts"
            aria-expanded="true" aria-controls="collapseProducts">
            <i class="fas fa-fw fa-box"></i>
            <span>Products</span>
        </a>
        <div id="collapseProducts" class="collapse {{ request()->routeIs('admin.products.*') || request()->routeIs('admin.inventory.*') ? 'show' : '' }}" aria-labelledby="headingProducts" data-parent="#accordionSidebar">            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Product Management:</h6>
                <a class="collapse-item {{ request()->routeIs('admin.products.index') && !request()->has('action') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">All Products</a>
                <a class="collapse-item {{ request()->routeIs('admin.products.create') ? 'active' : '' }}" href="{{ route('admin.products.create') }}">Add Product</a>
                <a class="collapse-item {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}" href="{{ route('admin.inventory.index') }}">Inventory</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Categories -->
    <li class="nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.categories.index') }}">
            <i class="fas fa-fw fa-tags"></i>
            <span>Categories</span>
        </a>
    </li>

    <!-- Nav Item - Orders -->
    <li class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.orders.index') }}">
            <i class="fas fa-fw fa-shopping-cart"></i>
            <span>Orders</span>
        </a>
    </li>

    <!-- Nav Item - Customers -->
    <li class="nav-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.customers.index') }}">
            <i class="fas fa-fw fa-users"></i>
            <span>Customers</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Marketing & Reports
    </div>    <!-- Nav Item - Promotions -->
    <li class="nav-item {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePromotions"
            aria-expanded="true" aria-controls="collapsePromotions">
            <i class="fas fa-fw fa-percentage"></i>
            <span>Promotions</span>
        </a>
        <div id="collapsePromotions" class="collapse {{ request()->routeIs('admin.promotions.*') ? 'show' : '' }}" aria-labelledby="headingPromotions" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Promotion Management:</h6>
                <a class="collapse-item" href="{{ route('admin.promotions.discounts') }}">Discounts</a>
                <a class="collapse-item" href="{{ route('admin.promotions.promo-codes') }}">Promo Codes</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Reviews -->
    <li class="nav-item {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.reviews.index') }}">
            <i class="fas fa-fw fa-star"></i>
            <span>Reviews</span>
        </a>
    </li>    <!-- Nav Item - Reports -->
    <li class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReports"
            aria-expanded="true" aria-controls="collapseReports">
            <i class="fas fa-fw fa-chart-area"></i>
            <span>Reports</span>
        </a>
        <div id="collapseReports" class="collapse {{ request()->routeIs('admin.reports.*') ? 'show' : '' }}" aria-labelledby="headingReports" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Analytics:</h6>
                <a class="collapse-item" href="{{ route('admin.reports.sales') }}">Sales Reports</a>
                <a class="collapse-item" href="{{ route('admin.reports.inventory') }}">Inventory Reports</a>
                <a class="collapse-item" href="{{ route('admin.reports.customers') }}">Customer Reports</a>
            </div>
        </div>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Support & Settings
    </div>

    <!-- Nav Item - Support Tickets -->
    <li class="nav-item {{ request()->routeIs('admin.support-tickets.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.support-tickets.index') }}">
            <i class="fas fa-fw fa-life-ring"></i>
            <span>Support Tickets</span>
        </a>
    </li>    <!-- Nav Item - Settings -->
    <li class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.settings.index') }}">
            <i class="fas fa-fw fa-cog"></i>
            <span>Settings</span>
        </a>
    </li>

    <!-- Nav Item - Admins -->
    <li class="nav-item {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.admins.index') }}">
            <i class="fas fa-fw fa-user-shield"></i>
            <span>Admin Users</span>
        </a>
    </li>

    <!-- Nav Item - Audit Logs -->
    <li class="nav-item {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.audit-logs.index') }}">
            <i class="fas fa-fw fa-history"></i>
            <span>Audit Logs</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
