<!DOCTYPE html>
<html lang="en">
<head>	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="PangAIaShop - Your Ultimate E-commerce Destination">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<!-- title -->
	<title>@yield('title', 'PangAIaShop')</title>	<!-- favicon -->
	<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
	<link rel="shortcut icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
	<link rel="apple-touch-icon" href="{{ asset('assets/img/favicon.png') }}">
	<!-- google font -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Poppins:400,700&display=swap" rel="stylesheet">
	<!-- fontawesome -->
	<link rel="stylesheet" href="{{ asset('assets/css/all.min.css') }}">
	<!-- bootstrap -->
	<link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">
	<!-- owl carousel -->
	<link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.css') }}">
	<!-- magnific popup -->
	<link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.css') }}">
	<!-- animate css -->
	<link rel="stylesheet" href="{{ asset('assets/css/animate.css') }}">
	<!-- mean menu css -->
	<link rel="stylesheet" href="{{ asset('assets/css/meanmenu.min.css') }}">
	<!-- main style -->
	<link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">	<!-- responsive -->	<link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">
	<!-- custom fixes -->
	<link rel="stylesheet" href="{{ asset('assets/css/custom-fixes.css') }}">
	<!-- three part header -->	<link rel="stylesheet" href="{{ asset('assets/css/three-part-header.css') }}">
	<!-- user dropdown menu -->
	<link rel="stylesheet" href="{{ asset('assets/css/user-dropdown.css') }}">	<!-- form enhancements -->
	<link rel="stylesheet" href="{{ asset('assets/css/form-enhancements.css') }}">	<!-- select fixes -->
	<link rel="stylesheet" href="{{ asset('assets/css/select-fix.css') }}">
	<!-- wishlist common styles -->
	<link rel="stylesheet" href="{{ asset('assets/css/wishlist-common.css') }}">
	<!-- cart indicators -->
	<link rel="stylesheet" href="{{ asset('assets/css/cart-indicators.css') }}">	
	@yield('styles')
		<style>
		.footer-box .widget-title {
			position: relative;
			padding-bottom: 10px;
			margin-bottom: 20px;
		}
		
		.footer-box .widget-title::after {
			content: '';
			position: absolute;
			bottom: 0;
			left: 50%;
			transform: translateX(-50%);
			width: 60px;
			height: 3px;
			background-color: #F28123;
			border-radius: 2px;
		}
	</style>
</head>
<body>
	
	<!--PreLoader-->
    <div class="loader">
        <div class="loader-inner">
            <div class="circle"></div>
        </div>
    </div>
    <!--PreLoader Ends-->
	
	<!-- header -->
	<div class="top-header-area" id="sticker">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 col-sm-12 text-center">					
					<div class="main-menu-wrap">
						<!-- logo (left part) -->
						<div class="site-logo">
							<a href="{{ route('home') }}">
								<img src="{{ asset('assets/img/logo.png') }}" alt="PangAIaShop">
							</a>
						</div>
						<!-- logo end -->

						<!-- menu start (middle part) -->
						<nav class="main-menu">
							<ul>
								<li class="{{ request()->routeIs('home') ? 'current-list-item' : '' }}">
									<a href="{{ route('home') }}">Home</a>
								</li>
								<li class="{{ request()->routeIs('about') ? 'current-list-item' : '' }}">
									<a href="{{ route('about') }}">About</a>
								</li>
								<li class="{{ request()->routeIs('shop') ? 'current-list-item' : '' }}">
									<a href="{{ route('shop') }}">Shop</a>
								</li>
								<li class="{{ request()->routeIs('contact') ? 'current-list-item' : '' }}">
									<a href="{{ route('contact') }}">Contact</a>
								</li>								<!-- Keep the original menu item with icons for mobile only -->								<li>
									<div class="header-icons">
										<a class="shopping-cart" href="{{ route('cart') }}">
											<i class="fas fa-shopping-cart"></i>
											@if(Auth::check())
												@php
												$cartCount = 0;
												if (Auth::user()->cart && Auth::user()->cart->first()) {
													$cartCount = Auth::user()->cart->first()->items->sum('quantity');
												}
												@endphp
												@if($cartCount > 0)
													<span class="cart-count-indicator cart-count">{{ $cartCount }}</span>
												@endif
											@elseif(Session::has('guest_cart_count') && Session::get('guest_cart_count') > 0)
												<span class="cart-count-indicator cart-count">{{ Session::get('guest_cart_count') }}</span>
											@endif
										</a>
										<a class="mobile-hide login-icon" href="{{ route('login') }}"><i class="fas fa-user"></i></a>
									</div>
								</li>
							</ul>
						</nav>
						<!-- menu end -->						<!-- icons (right part) -->
						<div class="header-icons-container">							<div class="header-icons">
								<a class="shopping-cart" href="{{ route('cart') }}">
									<i class="fas fa-shopping-cart"></i>
									@if(Auth::check())
										@php
										$cartCount = 0;
										if (Auth::user()->cart && Auth::user()->cart->first()) {
											$cartCount = Auth::user()->cart->first()->items->sum('quantity');
										}
										@endphp
										@if($cartCount > 0)
											<span class="cart-count-indicator cart-count">{{ $cartCount }}</span>
										@endif
									@elseif(Session::has('guest_cart_count') && Session::get('guest_cart_count') > 0)
										<span class="cart-count-indicator cart-count">{{ Session::get('guest_cart_count') }}</span>
									@endif
								</a>
								@if(Auth::check())
									<div class="user-dropdown text-center">
										<a class="mobile-hide user-icon" href="{{ route('profile') }}">
											<i class="fas fa-user text-white"></i>
											<span class="ml-1 text-white">{{ Auth::user()->username }}</span>
										</a>
										<div class="dropdown-content">
											<a href="{{ route('profile') }}">My Profile</a>
											<a href="{{ route('orders') }}">My Orders</a>
											<a href="{{ route('wishlist') }}">Wishlist</a>
											<form action="{{ route('logout') }}" method="POST">
												@csrf
												<button type="submit" class="dropdown-logout text-center">Logout</button>
											</form>
										</div>
									</div>
								@else
									<div class="guest-user">
										<a class="mobile-hide login-icon" href="{{ route('login') }}">
											<i class="fas fa-user text-white"></i>
											<span class="ml-1 text-white">Guest</span>
										</a>
										<div class="dropdown-content">
											<a href="{{ route('login') }}">Sign In</a>
											<a href="{{ route('register') }}">Register</a>
										</div>
									</div>
								@endif
							</div>
						</div>
						<!-- icons end -->
						
						<div class="mobile-menu"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end header -->
	
	<!-- search area -->
	<div class="search-area">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<span class="close-btn"><i class="fas fa-window-close"></i></span>
					<div class="search-bar">
						<div class="search-bar-tablecell">
							<h3>Search For:</h3>
							<input type="text" placeholder="Keywords">
							<button type="submit">Search <i class="fas fa-search"></i></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end search area -->

	@yield('content')

	<!-- footer -->
	<div class="footer-area">
		<div class="container">
			<div class="row">
				<div class="col-lg-4 col-md-6 text-center">
					<div class="footer-box about-widget">
						<h2 class="widget-title">About us</h2>
						<p>PangAIaShop offers the best products with unbeatable prices and exceptional quality. Your go-to destination for all your shopping needs.</p>
					</div>
				</div>
				<div class="col-lg-4 col-md-6 text-center">
					<div class="footer-box get-in-touch">
						<h2 class="widget-title">Get in Touch</h2>
						<ul>
							<li>123 Main Street, Amman, Jordan</li>
							<li>support@pangaiashop.com</li>
							<li>+123 456 7890</li>
						</ul>
					</div>
				</div>				<div class="col-lg-4 col-md-6 text-center justify-content-center">
					<div class="footer-box pages">
						<h2 class="widget-title">Pages</h2>
						<ul>
							<li><a href="{{ route('home') }}">Home</a></li>
							<li><a href="{{ route('about') }}">About</a></li>
							<li><a href="{{ route('shop') }}">Shop</a></li>
							<li><a href="{{ route('contact') }}">Contact</a></li>
							<li><a href="{{ route('terms-of-service') }}">Terms of Service</a></li>
							<li><a href="{{ route('privacy-policy') }}">Privacy Policy</a></li>
						</ul>
					</div>
				</div>
				{{-- <div class="col-lg-3 col-md-6">
					<div class="footer-box subscribe">
						<h2 class="widget-title">Subscribe</h2>
						<p>Subscribe to our mailing list to get the latest updates.</p>
						<form action="{{ route('subscribe') }}">
							@csrf
							<input type="email" placeholder="Email">
							<button type="submit"><i class="fas fa-paper-plane"></i></button>
						</form>
					</div>
				</div> --}}
			</div>
		</div>
	</div>
	<!-- end footer -->
		<!-- copyright -->
	<div class="copyright">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 col-md-12">
					<p>Copyrights &copy; {{ date('Y') }} - <a href="{{ route('home') }}">PangAIaShop</a>, All Rights Reserved.<br>
					<small><a href="{{ route('terms-of-service') }}">Terms of Service</a> | <a href="{{ route('privacy-policy') }}">Privacy Policy</a></small></p>
				</div>
				<div class="col-lg-6 text-right col-md-12">
					<div class="social-icons">
						<ul>
							<li><a href="#" target="_blank"><i class="fab fa-facebook-f"></i></a></li>
							<li><a href="#" target="_blank"><i class="fab fa-twitter"></i></a></li>
							<li><a href="#" target="_blank"><i class="fab fa-instagram"></i></a></li>
							<li><a href="#" target="_blank"><i class="fab fa-linkedin"></i></a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end copyright -->
		<!-- Define route for login page -->
	<script>
		var loginRoute = "{{ route('login') }}";
	</script>
	<!-- jquery -->
	<script src="{{ asset('assets/js/jquery-1.11.3.min.js') }}"></script>
	<!-- bootstrap -->
	<script src="{{ asset('assets/bootstrap/js/bootstrap.min.js') }}"></script>
	<!-- count down -->
	<script src="{{ asset('assets/js/jquery.countdown.js') }}"></script>
	<!-- isotope -->
	<script src="{{ asset('assets/js/jquery.isotope-3.0.6.min.js') }}"></script>
	<!-- waypoints -->
	<script src="{{ asset('assets/js/waypoints.js') }}"></script>
	<!-- owl carousel -->
	<script src="{{ asset('assets/js/owl.carousel.min.js') }}"></script>
	<!-- magnific popup -->
	<script src="{{ asset('assets/js/jquery.magnific-popup.min.js') }}"></script>
	<!-- mean menu -->	<script src="{{ asset('assets/js/jquery.meanmenu.min.js') }}"></script>
	<!-- sticker js -->
	<script src="{{ asset('assets/js/sticker.js') }}"></script>	<!-- main js -->
	<script src="{{ asset('assets/js/main.js') }}"></script>	<!-- custom menu js -->
	<script src="{{ asset('assets/js/custom-menu.js') }}"></script>
	<!-- alerts js -->
	<script src="{{ asset('assets/js/alerts.js') }}"></script>	<!-- categories expand js -->
	<script src="{{ asset('assets/js/categories-expand.js') }}"></script>
	<!-- ajax cart js -->
	<script src="{{ asset('assets/js/ajax-cart.js') }}"></script>
	<!-- custom dropdown js -->
	<script src="{{ asset('assets/js/custom-dropdown.js') }}"></script>
	<!-- wishlist common js -->
	<script src="{{ asset('assets/js/wishlist-common.js') }}"></script>
	
	@if(config('app.debug'))
	<!-- debug menu js (only in development) -->
	<script src="{{ asset('assets/js/menu-debug.js') }}"></script>
	@endif
	
	@yield('scripts')
	<!-- Add this floating chat widget to your master.blade.php before the closing </body> tag -->
<!-- This creates a floating chat button that opens a modal -->

<style>
.chat-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
}

.chat-toggle-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

.chat-toggle-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(0,0,0,0.3);
}

.chat-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 2000;
    justify-content: center;
    align-items: center;
}

.chat-modal-content {
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    overflow: hidden;
    position: relative;
}

.chat-modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
    padding: 5px;
}

.mini-chat-messages {
    height: 300px;
    overflow-y: auto;
    padding: 15px;
    background: #f8f9fa;
}

.mini-chat-input-area {
    padding: 15px;
    background: white;
    border-top: 1px solid #e9ecef;
}

@media (max-width: 768px) {
    .chat-modal-content {
        width: 95%;
        height: 80vh;
    }
    
    .mini-chat-messages {
        height: calc(80vh - 120px);
    }
}
</style>

{{-- <!-- Chat Widget -->
<div class="chat-widget">
    <button class="chat-toggle-btn" id="chatToggleBtn" title="Chat with AI">
        <i class="fas fa-comments"></i>
    </button>
</div> --}}

<!-- Chat Modal -->
<div class="chat-modal" id="chatModal">
    <div class="chat-modal-content">
        <div class="chat-modal-header">
            <h4><i class="fas fa-robot"></i> AI Assistant</h4>
            <button class="chat-modal-close" id="chatModalClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="mini-chat-messages" id="miniChatMessages">
            <div style="text-align: center; padding: 20px; color: #6c757d;">
                <h5>👋 Hi there!</h5>
                <p>How can I help you today?</p>
            </div>
        </div>
        
        <div class="mini-chat-input-area">
            <form id="miniChatForm" style="display: flex; gap: 10px;">
                @csrf
                <input type="text" id="miniMessageInput" 
                       style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 20px; outline: none;"
                       placeholder="Type your message..." required>
                <button type="submit" id="miniSendBtn"
                        style="padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 20px; cursor: pointer;">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// Mini Chat Widget functionality
document.addEventListener('DOMContentLoaded', function() {
    const chatToggleBtn = document.getElementById('chatToggleBtn');
    const chatModal = document.getElementById('chatModal');
    const chatModalClose = document.getElementById('chatModalClose');
    const miniChatForm = document.getElementById('miniChatForm');
    const miniMessageInput = document.getElementById('miniMessageInput');
    const miniChatMessages = document.getElementById('miniChatMessages');
    const miniSendBtn = document.getElementById('miniSendBtn');
    
    let miniConversation = [];
    
    // Toggle chat modal
    chatToggleBtn.addEventListener('click', () => {
        chatModal.style.display = 'flex';
        miniMessageInput.focus();
    });
    
    // Close modal
    chatModalClose.addEventListener('click', () => {
        chatModal.style.display = 'none';
    });
    
    // Close modal when clicking outside
    chatModal.addEventListener('click', (e) => {
        if (e.target === chatModal) {
            chatModal.style.display = 'none';
        }
    });
    
    // Handle form submission
    miniChatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const message = miniMessageInput.value.trim();
        if (!message) return;
        
        // Add user message
        addMiniMessage(message, 'user');
        miniMessageInput.value = '';
        toggleMiniLoading(true);
        
        try {
            const response = await fetch('{{ route("chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    message: message,
                    conversation: miniConversation
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                addMiniMessage(data.message, 'bot');
                miniConversation = data.conversation;
            } else {
                addMiniMessage(data.error || 'Sorry, something went wrong.', 'bot');
            }
        } catch (error) {
            addMiniMessage('Sorry, I\'m having trouble connecting.', 'bot');
        } finally {
            toggleMiniLoading(false);
        }
    });
    
    function addMiniMessage(content, type) {
        // Remove welcome message
        const welcome = miniChatMessages.querySelector('div[style*="text-align: center"]');
        if (welcome) welcome.remove();
        
        const messageDiv = document.createElement('div');
        messageDiv.style.cssText = `
            margin-bottom: 10px;
            display: flex;
            ${type === 'user' ? 'justify-content: flex-end;' : ''}
        `;
        
        const contentDiv = document.createElement('div');
        contentDiv.style.cssText = `
            max-width: 80%;
            padding: 8px 12px;
            border-radius: 15px;
            word-wrap: break-word;
            font-size: 14px;
            ${type === 'user' ? 
                'background: #007bff; color: white; border-bottom-right-radius: 5px;' : 
                'background: white; border: 1px solid #ddd; border-bottom-left-radius: 5px;'
            }
        `;
        contentDiv.textContent = content;
        
        messageDiv.appendChild(contentDiv);
        miniChatMessages.appendChild(messageDiv);
        miniChatMessages.scrollTop = miniChatMessages.scrollHeight;
    }
    
    function toggleMiniLoading(isLoading) {
        miniSendBtn.disabled = isLoading;
        miniMessageInput.disabled = isLoading;
        
        if (isLoading) {
            miniSendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        } else {
            miniSendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            miniMessageInput.focus();
        }
    }
});
</script>
</body>
</html>