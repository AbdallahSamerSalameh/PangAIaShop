<!DOCTYPE html>
<html lang="en">

<head>    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="PangAIa Shop Admin Dashboard - Manage your ecommerce store">
    <meta name="author" content="PangAIa Shop">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
      <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name', 'PangAIa Shop') }}</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('admin-assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">    <!-- Custom styles for this template-->
    <link href="{{ asset('admin-assets/css/sb-admin-2.min.css') }}" rel="stylesheet">
    
    <!-- Custom admin styling to match public site branding -->
    <link href="{{ asset('admin-assets/css/admin-custom.css') }}" rel="stylesheet">

    @stack('styles')
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        @include('admin.layouts.sidebar')
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                @include('admin.layouts.topbar')
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    @if(isset($pageTitle))
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">{{ $pageTitle }}</h1>
                        @isset($pageAction)
                            {!! $pageAction !!}
                        @endisset
                    </div>
                    @endif

                    <!-- Content Row -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @yield('content')

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            @include('admin.layouts.footer')
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>    <!-- Universal Delete Confirmation Modal -->
    <div class="modal fade" id="universalDeleteModal" tabindex="-1" role="dialog" aria-labelledby="universalDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title" id="universalDeleteModalLabel">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Confirm Deletion
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-trash-alt text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="mb-3" id="deleteMessage">Are you sure you want to delete this item?</h5>
                    <p class="text-muted mb-1"><span id="itemTypeLabel">Item</span>: <strong id="itemName" class="text-dark"></strong></p>
                    <p class="text-muted small">This action cannot be undone. All data associated with this <span id="itemTypeLower">item</span> will be permanently removed.</p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-secondary px-4 mr-3" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-danger px-4" id="universalConfirmDeleteBtn">
                        <i class="fas fa-trash mr-2"></i>Delete <span id="buttonItemType">Item</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Delete Modal Styles -->
    <style>
        /* Custom Delete Modal Styling */
        #universalDeleteModal .modal-content {
            border-radius: 15px;
            overflow: hidden;
        }

        #universalDeleteModal .modal-header.bg-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        }

        #universalDeleteModal .modal-body {
            background: linear-gradient(180deg, #ffffff 0%, #f8f9fc 100%);
        }

        #universalDeleteModal .modal-footer {
            background: #f8f9fc;
        }

        #universalDeleteModal .btn {
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        #universalDeleteModal .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }

        #universalDeleteModal .btn-danger:hover {
            background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
            transform: translateY(-2px);
        }

        #universalDeleteModal .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        #universalDeleteModal .btn-secondary:hover {
            background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
            transform: translateY(-2px);
        }

        /* Modal animation */
        #universalDeleteModal.fade .modal-dialog {
            transform: translate(0, -50px);
            transition: transform 0.3s ease-out;
        }

        #universalDeleteModal.show .modal-dialog {
            transform: translate(0, 0);
        }

        /* Backdrop blur effect */
        #universalDeleteModal.modal-backdrop,
        #universalDeleteModal + .modal-backdrop {
            backdrop-filter: blur(5px);
            background-color: rgba(0, 0, 0, 0.6);
        }

        /* Notification and sidebar badge styling */
        .sidebar-review-badge {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        
        .sidebar-badge-dismiss {
            transition: all 0.2s ease;
            opacity: 0.8;
        }
        
        .sidebar-badge-dismiss:hover {
            opacity: 1;
            color: #fff !important;
            transform: scale(1.1);
        }
        
        .sidebar .nav-link-wrapper {
            position: relative;
        }
        
        /* Make sidebar badge responsive */
        @media (max-width: 768px) {
            .sidebar-badge-dismiss {
                font-size: 8px !important;
            }
        }
        
        /* Animation for badge dismissal */
        .sidebar-review-badge.fade-out {
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.3s ease;
        }
        
        /* Enhanced notification dropdown styling */
        .notification-item {
            transition: all 0.2s ease;
        }
        
        .notification-item:hover {
            background-color: #f8f9fc;
        }
        
        .dismiss-notification {
            opacity: 0.6;
            transition: opacity 0.2s ease;
        }
        
        .dismiss-notification:hover {
            opacity: 1;
        }
    </style>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('admin-assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('admin-assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('admin-assets/vendor/jquery-easing/jquery.easing.min.js') }}"></script>    <!-- Custom scripts for all pages-->
    <script src="{{ asset('admin-assets/js/sb-admin-2.min.js') }}"></script>
      <!-- Custom admin JavaScript for enhanced functionality -->
    <script src="{{ asset('admin-assets/js/admin-custom.js') }}"></script>

    <!-- Universal Delete Modal JavaScript -->
    <script>
        // Global variables for universal delete modal
        let universalDeleteModal = {
            currentItemId: null,
            itemType: 'item',
            
            // Show the universal delete modal
            show: function(itemId, itemName, itemType = 'item', customMessage = null) {
                this.currentItemId = itemId;
                this.itemType = itemType;
                
                // Update modal text
                $('#itemName').text(itemName);
                $('#itemTypeLabel').text(itemType.charAt(0).toUpperCase() + itemType.slice(1));
                $('#itemTypeLower').text(itemType.toLowerCase());
                $('#buttonItemType').text(itemType.charAt(0).toUpperCase() + itemType.slice(1));
                
                // Update delete message if custom message provided
                if (customMessage) {
                    $('#deleteMessage').text(customMessage);
                } else {
                    $('#deleteMessage').text(`Are you sure you want to delete this ${itemType.toLowerCase()}?`);
                }
                
                // Show the modal
                $('#universalDeleteModal').modal('show');
            },
            
            // Reset modal state
            reset: function() {
                this.currentItemId = null;
                this.itemType = 'item';
                $('#universalConfirmDeleteBtn').html('<i class="fas fa-trash mr-2"></i>Delete Item');
                $('#universalConfirmDeleteBtn').prop('disabled', false);
            }
        };

        // Handle delete confirmation
        $('#universalConfirmDeleteBtn').on('click', function() {
            if (universalDeleteModal.currentItemId) {
                // Add loading state
                $(this).html('<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...');
                $(this).prop('disabled', true);
                
                // Submit the form
                $('#delete-form-' + universalDeleteModal.currentItemId).submit();
            }
        });

        // Reset modal when closed
        $('#universalDeleteModal').on('hidden.bs.modal', function () {
            universalDeleteModal.reset();
        });

        // Global function for easy access
        window.showDeleteModal = function(itemId, itemName, itemType = 'item', customMessage = null) {
            universalDeleteModal.show(itemId, itemName, itemType, customMessage);
        };
    </script>      <!-- Notification System JavaScript (moved from topbar) -->
    <script>    // Function to hide dismissed notifications immediately to prevent flicker
    function hideDismissedNotifications() {
        const dismissedNotifications = JSON.parse(localStorage.getItem('dismissedNotifications') || '[]');
        console.log('Checking dismissed notifications:', dismissedNotifications);
        
        if (dismissedNotifications.length > 0) {
            dismissedNotifications.forEach(function(type) {
                // Hide notification items of this type
                $(`.notification-item[data-notification-type="${type}"]`).hide();
                
                // Hide sidebar badges of this type
                if (type === 'reviews') {
                    $('#sidebarReviewBadge').hide();
                }
            });
        }
        
        // Update notification display after hiding dismissed notifications
        updateNotificationDisplay();
    }

    // Wait for jQuery to be fully loaded and DOM to be ready      $(document).ready(function() {
        console.log('Notification script loaded - jQuery version:', $.fn.jquery);
        console.log('Notification items found:', $('.notification-item').length);
        console.log('Notification badge found:', $('#notificationBadge').length);
        console.log('Notification dropdown found:', $('#notificationDropdown').length);
        console.log('Sidebar review badge found:', $('#sidebarReviewBadge').length);
        
        // Hide dismissed notifications immediately on page load to prevent flicker
        hideDismissedNotifications();
        // Note: hideDismissedNotifications() already calls updateNotificationDisplay()
        
        // Handle clicking on notification item (not the dismiss button)
        $(document).on('click', '.notification-item', function(e) {
            console.log('Notification item clicked!');
            
            // Don't trigger if clicking on dismiss button
            if ($(e.target).closest('.dismiss-notification').length > 0) {
                console.log('Dismiss button clicked, preventing notification click');
                return;
            }
            
            e.preventDefault();
            e.stopPropagation();
            
            const notificationItem = $(this);
            const notificationId = notificationItem.data('notification-id');
            const notificationType = notificationItem.data('notification-type');
            
            console.log('Processing notification click:', {
                id: notificationId,
                type: notificationType,
                item: notificationItem
            });
            
            // Navigate to the href (if it exists)
            const href = notificationItem.attr('href');
            if (href && href !== '#') {
                console.log('Will navigate to:', href);
                // Dismiss the notification first, then navigate
                dismissNotification(notificationType, notificationItem, function() {
                    console.log('Navigating to:', href);
                    window.location.href = href;
                });
            } else {
                console.log('No href found, just dismissing');
                // Just dismiss if no href
                dismissNotification(notificationType, notificationItem);
            }
        });
        
        // Handle individual notification dismissal via X button
        $(document).on('click', '.dismiss-notification', function(e) {
            console.log('Dismiss button clicked!');
            
            e.preventDefault();
            e.stopPropagation();
            
            const notificationItem = $(this).closest('.notification-item');
            const notificationId = $(this).data('notification-id');
            const notificationType = $(this).data('notification-type') || 'reviews';
            
            console.log('Processing dismiss click:', {
                id: notificationId,
                type: notificationType,
                item: notificationItem
            });
            
            dismissNotification(notificationType, notificationItem);
        });
        
        // Handle sidebar badge dismissal via X button
        $(document).on('click', '.sidebar-badge-dismiss', function(e) {
            console.log('Sidebar badge dismiss button clicked!');
            
            e.preventDefault();
            e.stopPropagation();
            
            const badgeElement = $(this).closest('.sidebar-review-badge');
            const notificationId = $(this).data('notification-id');
            const notificationType = $(this).data('notification-type') || 'reviews';
            
            console.log('Processing sidebar badge dismiss click:', {
                id: notificationId,
                type: notificationType,
                badge: badgeElement
            });
            
            dismissSidebarBadge(notificationType, badgeElement);
        });
          // Handle clear all notifications
        $(document).on('click', '#clear-all-notifications', function(e) {
            console.log('Clear all clicked!');
            
            e.preventDefault();
            e.stopPropagation();
            
            const allNotifications = $('.notification-item');
            console.log('Found notifications to clear:', allNotifications.length);
            
            // Collect all notification types
            const notificationTypes = [];
            allNotifications.each(function() {
                const type = $(this).data('notification-type') || 'reviews';
                if (notificationTypes.indexOf(type) === -1) {
                    notificationTypes.push(type);
                }
            });
            
            // If no specific types found, default to reviews
            if (notificationTypes.length === 0) {
                notificationTypes.push('reviews');
            }
              console.log('Dismissing notification types:', notificationTypes);
            
            // Store all types as dismissed in localStorage immediately
            let dismissedNotifications = JSON.parse(localStorage.getItem('dismissedNotifications') || '[]');
            notificationTypes.forEach(function(type) {
                if (dismissedNotifications.indexOf(type) === -1) {
                    dismissedNotifications.push(type);
                }
            });
            localStorage.setItem('dismissedNotifications', JSON.stringify(dismissedNotifications));
            console.log('Stored all dismissed notifications in localStorage:', dismissedNotifications);
            
            // Now fade out all notifications immediately
            allNotifications.fadeOut(300, function() {
                $(this).remove();
                updateNotificationDisplay();
                console.log('All notifications cleared from DOM');
            });
            
            // Also dismiss sidebar badges for the same types
            notificationTypes.forEach(function(type) {
                if (type === 'reviews') {
                    $('#sidebarReviewBadge').fadeOut(300, function() {
                        $(this).remove();
                    });
                }
            });
            
            // Optional: Send AJAX request to dismiss all (server-side tracking)
            $.ajax({
                url: '{{ route("admin.notifications.dismiss-all") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    types: notificationTypes
                },
                success: function(response) {
                    console.log('Server notified of bulk dismissal:', response);
                },
                error: function(xhr, status, error) {
                    console.warn('Server notification failed for clear all (continuing anyway):', error);
                }
            });
        });// Function to dismiss individual notification (LOCALSTORAGE with optional server sync)
        function dismissNotification(notificationType, notificationItem, callback) {
            console.log('Dismissing notification type (localStorage):', notificationType);
            
            // Store dismissed state in localStorage immediately
            let dismissedNotifications = JSON.parse(localStorage.getItem('dismissedNotifications') || '[]');
            if (dismissedNotifications.indexOf(notificationType) === -1) {
                dismissedNotifications.push(notificationType);
                localStorage.setItem('dismissedNotifications', JSON.stringify(dismissedNotifications));
                console.log('Stored dismissed notification in localStorage:', notificationType);
            }
            
            // Now hide the notification with animation
            notificationItem.fadeOut(300, function() {
                $(this).remove();
                updateNotificationDisplay();
                
                // Execute callback if provided (for navigation)
                if (typeof callback === 'function') {
                    console.log('Executing callback');
                    callback();
                }
                
                console.log('Notification dismissed and removed from DOM');
            });
            
            // Also dismiss corresponding sidebar badge if it's a review notification
            if (notificationType === 'reviews') {
                $('#sidebarReviewBadge').fadeOut(300, function() {
                    $(this).remove();
                });
            }
            
            // Optional: Send AJAX request to server for tracking (non-blocking)
            $.ajax({
                url: '{{ route("admin.notifications.dismiss") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    type: notificationType
                },
                success: function(response) {
                    console.log('Server notified of dismissal:', response);
                },
                error: function(xhr, status, error) {
                    console.warn('Server notification failed (continuing anyway):', error);
                }
            });
        }
                },
                error: function(xhr, status, error) {
                    console.warn('Server dismissal failed:', error);
                    // Still hide the notification locally even if server call fails
                    notificationItem.fadeOut(300, function() {
                        $(this).remove();
                        updateNotificationDisplay();
                        
                        if (typeof callback === 'function') {
                            callback();
                        }
                    });
                }
            });
        }
        
        // Function to dismiss sidebar badge (CLIENT-SIDE ONLY)
        function dismissSidebarBadge(notificationType, badgeElement) {
            console.log('Dismissing sidebar badge type (client-side):', notificationType);
            
            // Store dismissed state in localStorage for persistence
            let dismissedNotifications = JSON.parse(localStorage.getItem('dismissedNotifications') || '[]');
            if (dismissedNotifications.indexOf(notificationType) === -1) {
                dismissedNotifications.push(notificationType);
                localStorage.setItem('dismissedNotifications', JSON.stringify(dismissedNotifications));
                console.log('Stored dismissed sidebar badge in localStorage:', notificationType);
            }
            
            // Immediately hide the badge with animation
            badgeElement.fadeOut(300, function() {
                $(this).remove();
                console.log('Sidebar badge dismissed and removed from DOM');
            });
            
            // Also dismiss corresponding notifications in dropdown if they exist
            $(`.notification-item[data-notification-type="${notificationType}"]`).fadeOut(300, function() {
                $(this).remove();
                updateNotificationDisplay();
            });
            
            // Optional: Still send AJAX request in background for server tracking (no reload)
            $.ajax({
                url: '{{ route("admin.notifications.dismiss") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    type: notificationType
                },
                success: function(response) {
                    console.log('Server notified of sidebar badge dismissal:', response);
                },
                error: function(xhr, status, error) {
                    console.warn('Server notification failed for sidebar badge (continuing anyway):', error);
                }
            });
        }
          // Function to update notification display
        function updateNotificationDisplay() {
            const remainingNotifications = $('.notification-item:visible').length;
            const badge = $('#notificationBadge');
            const dropdown = $('#notificationDropdown');
            
            console.log('Updating notification display:', {
                remaining: remainingNotifications,
                totalItems: $('.notification-item').length,
                visibleItems: $('.notification-item:visible').length,
                badge: badge.length,
                dropdown: dropdown.length
            });
            
            // Update badge
            if (remainingNotifications <= 0) {
                console.log('No visible notifications remaining, hiding badge');
                badge.hide();
                
                // Remove divider and clear all button
                $('#notificationDivider').remove();
                $('#clear-all-notifications').parent().remove();
                
                // Add "no notifications" message if not already present
                if (dropdown.find('#no-notifications').length === 0) {
                    console.log('Adding no notifications message');
                    dropdown.append('<div class="dropdown-item text-center small text-gray-500" id="no-notifications"><i class="fas fa-check-circle text-success"></i> No pending notifications</div>');
                }
            } else {
                console.log('Visible notifications remaining, updating badge:', remainingNotifications);
                badge.text(remainingNotifications > 99 ? '99+' : remainingNotifications).show();
                // Remove "no notifications" message if it exists
                $('#no-notifications').remove();
            }
        }
        
        // Test notification system on page load
        console.log('Testing notification system elements:');
        console.log('- Notification items:', $('.notification-item'));
        console.log('- Dismiss buttons:', $('.dismiss-notification'));
        console.log('- Sidebar badge dismiss buttons:', $('.sidebar-badge-dismiss'));
        console.log('- Clear all button:', $('#clear-all-notifications'));
        console.log('- Badge:', $('#notificationBadge'));
        console.log('- Dropdown:', $('#notificationDropdown'));
        console.log('- Sidebar review badge:', $('#sidebarReviewBadge'));          // Global function to reset dismissed notifications (for testing/debugging)
        window.resetNotifications = function() {
            // Clear localStorage dismissals
            localStorage.removeItem('dismissedNotifications');
            console.log('Cleared localStorage dismissed notifications');
            
            // Clear server-side session dismissals
            $.ajax({
                url: '{{ route("admin.notifications.dismiss") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    type: 'reset_all'
                },
                success: function(response) {
                    console.log('Server notifications reset successfully');
                    window.location.reload();
                },
                error: function() {
                    console.log('Reloading page to reset notifications');
                    window.location.reload();
                }
            });
        };
        
        console.log('Use resetNotifications() in console to reset all dismissed notifications');
    });
    </script>

    @stack('scripts')
    @yield('scripts')

</body>

</html>
