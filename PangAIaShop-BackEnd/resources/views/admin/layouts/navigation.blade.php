<!-- Admin sidebar navigation -->
<nav class="mt-2 px-2 space-y-1">
    <!-- Dashboard -->
    <a href="{{ route('admin.dashboard') }}" 
        class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
        <i class="fas fa-tachometer-alt mr-3 text-lg {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
        Dashboard
    </a>

    <!-- Orders -->
    <div x-data="{ open: {{ request()->routeIs('admin.orders*') ? 'true' : 'false' }} }">
        <button type="button" 
            @click="open = !open" 
            class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.orders*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-shopping-cart mr-3 text-lg {{ request()->routeIs('admin.orders*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            <span class="flex-1">Orders</span>
            <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
        </button>
        <div x-show="open" class="mt-1 space-y-1 pl-6">
            <a href="{{ route('admin.orders.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.orders.index') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                All Orders
            </a>
            <a href="{{ route('admin.orders.create') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.orders.create') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Create Order
            </a>
            <a href="{{ route('admin.orders.pending') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.orders.pending') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Pending Orders
            </a>
        </div>
    </div>

    <!-- Products -->
    <div x-data="{ open: {{ request()->routeIs('admin.products*') || request()->routeIs('admin.categories*') ? 'true' : 'false' }} }">
        <button type="button" 
            @click="open = !open" 
            class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.products*') || request()->routeIs('admin.categories*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-box mr-3 text-lg {{ request()->routeIs('admin.products*') || request()->routeIs('admin.categories*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            <span class="flex-1">Products</span>
            <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
        </button>
        <div x-show="open" class="mt-1 space-y-1 pl-6">
            <a href="{{ route('admin.products.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.products.index') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                All Products
            </a>
            <a href="{{ route('admin.products.create') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.products.create') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Add Product
            </a>
            <a href="{{ route('admin.categories.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.categories.index') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Categories
            </a>
            <a href="{{ route('admin.products.inventory') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.products.inventory') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Inventory
            </a>
        </div>
    </div>

    <!-- Customers -->
    <div x-data="{ open: {{ request()->routeIs('admin.customers*') ? 'true' : 'false' }} }">
        <button type="button" 
            @click="open = !open" 
            class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.customers*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-users mr-3 text-lg {{ request()->routeIs('admin.customers*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            <span class="flex-1">Customers</span>
            <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
        </button>
        <div x-show="open" class="mt-1 space-y-1 pl-6">
            <a href="{{ route('admin.customers.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.customers.index') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                All Customers
            </a>
            <a href="{{ route('admin.customers.create') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.customers.create') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Add Customer
            </a>
        </div>
    </div>

    <!-- Vendors -->
    <div x-data="{ open: {{ request()->routeIs('admin.vendors*') ? 'true' : 'false' }} }">
        <button type="button" 
            @click="open = !open" 
            class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.vendors*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-store mr-3 text-lg {{ request()->routeIs('admin.vendors*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            <span class="flex-1">Vendors</span>
            <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
        </button>
        <div x-show="open" class="mt-1 space-y-1 pl-6">
            <a href="{{ route('admin.vendors.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.vendors.index') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                All Vendors
            </a>
            <a href="{{ route('admin.vendors.create') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.vendors.create') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Add Vendor
            </a>
        </div>
    </div>

    <!-- Promotions -->
    <div x-data="{ open: {{ request()->routeIs('admin.promotions*') ? 'true' : 'false' }} }">
        <button type="button" 
            @click="open = !open" 
            class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.promotions*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-percentage mr-3 text-lg {{ request()->routeIs('admin.promotions*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            <span class="flex-1">Promotions</span>
            <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
        </button>
        <div x-show="open" class="mt-1 space-y-1 pl-6">
            <a href="{{ route('admin.promotions.discounts') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.promotions.discounts') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Discounts
            </a>
            <a href="{{ route('admin.promotions.promo-codes') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.promotions.promo-codes') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Promo Codes
            </a>
        </div>
    </div>

    <!-- Reviews & Ratings -->
    <a href="{{ route('admin.reviews.index') }}" 
        class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.reviews*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
        <i class="fas fa-star mr-3 text-lg {{ request()->routeIs('admin.reviews*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
        Reviews & Ratings
    </a>

    <!-- Support -->
    <a href="{{ route('admin.support-tickets.index') }}" 
        class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.support-tickets*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
        <i class="fas fa-headset mr-3 text-lg {{ request()->routeIs('admin.support-tickets*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
        Support Tickets
    </a>

    <!-- Reports -->
    <div x-data="{ open: {{ request()->routeIs('admin.reports*') ? 'true' : 'false' }} }">
        <button type="button" 
            @click="open = !open" 
            class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.reports*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-chart-bar mr-3 text-lg {{ request()->routeIs('admin.reports*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            <span class="flex-1">Reports</span>
            <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
        </button>
        <div x-show="open" class="mt-1 space-y-1 pl-6">
            <a href="{{ route('admin.reports.sales') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.reports.sales') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Sales Report
            </a>
            <a href="{{ route('admin.reports.inventory') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.reports.inventory') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Inventory Report
            </a>
            <a href="{{ route('admin.reports.customers') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.reports.customers') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Customer Report
            </a>
        </div>
    </div>

    <!-- Settings -->
    <div x-data="{ open: {{ request()->routeIs('admin.settings*') ? 'true' : 'false' }} }">
        <button type="button" 
            @click="open = !open" 
            class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.settings*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-cog mr-3 text-lg {{ request()->routeIs('admin.settings*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            <span class="flex-1">Settings</span>
            <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
        </button>
        <div x-show="open" class="mt-1 space-y-1 pl-6">
            <a href="{{ route('admin.settings.general') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.settings.general') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                General
            </a>
            <a href="{{ route('admin.settings.shipping') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.settings.shipping') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Shipping
            </a>
            <a href="{{ route('admin.settings.payment') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.settings.payment') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Payment
            </a>
            <a href="{{ route('admin.settings.tax') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.settings.tax') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Tax
            </a>
            <a href="{{ route('admin.settings.email') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.settings.email') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Email
            </a>
        </div>
    </div>

    <!-- Administrators -->
    <div x-data="{ open: {{ request()->routeIs('admin.administrators*') || request()->routeIs('admin.audit-logs*') ? 'true' : 'false' }} }">
        <button type="button" 
            @click="open = !open" 
            class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.administrators*') || request()->routeIs('admin.audit-logs*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-user-shield mr-3 text-lg {{ request()->routeIs('admin.administrators*') || request()->routeIs('admin.audit-logs*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            <span class="flex-1">Administration</span>
            <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
        </button>
        <div x-show="open" class="mt-1 space-y-1 pl-6">
            <a href="{{ route('admin.administrators.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.administrators.index') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Administrators
            </a>
            <a href="{{ route('admin.administrators.roles') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.administrators.roles') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Roles & Permissions
            </a>
            <a href="{{ route('admin.audit-logs.index') }}" 
                class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.audit-logs.index') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                Audit Logs
            </a>
        </div>
    </div>
</nav>