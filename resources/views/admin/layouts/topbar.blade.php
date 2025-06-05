<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Digital Live Clock -->
    <div class="digital-clock mr-3" id="digitalClock">
        <span id="clockTime" style="color: #ff8c00; font-weight: bold; font-size: 1.1rem;"></span>
    </div>

    {{-- <!-- Topbar Search -->
    <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
        <div class="input-group">
            <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
            <div class="input-group-append">
                <button class="btn btn-primary" type="button">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div>
        </div>
    </form> --}}

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">

        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
        <li class="nav-item dropdown no-arrow d-sm-none">
            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-search fa-fw"></i>
            </a>
            <!-- Dropdown - Messages -->
            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                <form class="form-inline mr-auto w-100 navbar-search">
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li>

        <!-- Nav Item - Alerts -->
        <li class="nav-item dropdown no-arrow mx-1">            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <!-- Counter - Alerts (managed by JavaScript but initialized by server) -->
                @if(isset($pendingReviewsCount) && $pendingReviewsCount > 0)
                <span class="badge badge-danger badge-counter" id="notificationBadge">{{ $pendingReviewsCount > 99 ? '99+' : $pendingReviewsCount }}</span>
                @else
                <span class="badge badge-danger badge-counter" id="notificationBadge" style="display: none;"></span>
                @endif
            </a><!-- Dropdown - Alerts -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown" id="notificationDropdown">
                <h6 class="dropdown-header">
                    Notifications Center
                </h6>                @if(isset($pendingReviewsCount) && $pendingReviewsCount > 0)
                    <a class="dropdown-item d-flex align-items-center notification-item" 
                       href="{{ route('admin.reviews.index', ['status' => 'pending']) }}"
                       data-notification-id="reviews-{{ $pendingReviewsCount }}"
                       data-notification-type="reviews">
                        <div class="mr-3">
                            <div class="icon-circle bg-warning">
                                <i class="fas fa-star text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small text-gray-500">{{ now()->format('F d, Y') }}</div>
                            <span class="font-weight-bold">{{ $pendingReviewsCount }} {{ $pendingReviewsCount === 1 ? 'review' : 'reviews' }} waiting for approval</span>
                        </div>
                        <div class="ml-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary dismiss-notification" 
                                    data-notification-id="reviews-{{ $pendingReviewsCount }}"
                                    data-notification-type="reviews" title="Dismiss notification">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </a>@endif
                  
                @php
                    $hasNotifications = (isset($pendingReviewsCount) && $pendingReviewsCount > 0);
                @endphp
                  @if($hasNotifications)
                    <div class="dropdown-divider" id="notificationDivider"></div>
                    <a class="dropdown-item text-center small text-gray-500" href="#" id="clear-all-notifications">
                        <i class="fas fa-check-circle"></i> Clear All Notifications
                    </a>                @else
                    <div class="dropdown-item text-center small text-gray-500" id="no-notifications">
                        <i class="fas fa-check-circle text-success"></i> No pending notifications
                    </div>
                @endif
            </div>
        </li>

        <!-- Nav Item - Messages -->
        <li class="nav-item dropdown no-arrow mx-1">
            {{-- <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
                <!-- Counter - Messages -->
                @if(isset($unreadMessages) && $unreadMessages > 0)
                <span class="badge badge-danger badge-counter">{{ $unreadMessages > 99 ? '99+' : $unreadMessages }}</span>
                @endif
            </a> --}}
            <!-- Dropdown - Messages -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header">
                    Message Center
                </h6>
                <!-- Sample messages - replace with dynamic content -->
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="{{ asset('admin-assets/img/undraw_profile_1.svg') }}" alt="...">
                        <div class="status-indicator bg-success"></div>
                    </div>
                    <div class="font-weight-bold">
                        <div class="text-truncate">Support ticket from customer regarding order issue</div>
                        <div class="small text-gray-500">Customer Service · 58m</div>
                    </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="#">Read More Messages</a>
            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ auth('admin')->user()->username ?? 'Admin User' }}</span>
                @php
                    $currentAdminImage = auth('admin')->user()->profile_image ? asset('storage/' . auth('admin')->user()->profile_image) : (auth('admin')->user()->avatar_url ?? null);
                @endphp
                @include('admin.components.image-with-fallback', [
                    'src' => $currentAdminImage,
                    'alt' => auth('admin')->user()->username ?? 'Admin User',
                    'type' => 'profile',
                    'class' => 'img-profile rounded-circle',
                    'style' => 'width: 2rem; height: 2rem; object-fit: cover;'
                ])
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{ route('admin.profile') }}">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                {{-- <a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                    Settings
                </a>
                <a class="dropdown-item" href="{{ route('admin.audit-logs.index') }}">
                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                    Activity Log
                </a> --}}
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>

    </ul>
</nav>

<!-- Digital Clock Script -->
<script>
function updateClock() {
    const now = new Date();
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    const seconds = now.getSeconds().toString().padStart(2, '0');
    const timeString = `${hours}:${minutes}:${seconds}`;
    
    const clockElement = document.getElementById('clockTime');
    if (clockElement) {
        clockElement.textContent = timeString;
    }
}

// Update clock immediately and then every second
document.addEventListener('DOMContentLoaded', function() {
    updateClock();
    setInterval(updateClock, 1000);
});
</script>

<!-- Notification Script moved to bottom after jQuery loads -->