<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - PangAIaShop Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.10.2/dist/cdn.min.js" defer></script>
    
    <!-- Additional styles -->
    @yield('styles')
</head>
<body class="bg-gray-100 min-h-screen">
    <div x-data="{ sidebarOpen: false }">
        <!-- Mobile sidebar backdrop -->
        <div 
            x-show="sidebarOpen" 
            x-transition:enter="transition-opacity ease-linear duration-300" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            x-transition:leave="transition-opacity ease-linear duration-300" 
            x-transition:leave-start="opacity-100" 
            x-transition:leave-end="opacity-0" 
            class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 md:hidden"
            @click="sidebarOpen = false"
        ></div>

        <!-- Mobile sidebar panel -->
        <div 
            x-show="sidebarOpen" 
            x-transition:enter="transition ease-in-out duration-300 transform" 
            x-transition:enter-start="-translate-x-full" 
            x-transition:enter-end="translate-x-0" 
            x-transition:leave="transition ease-in-out duration-300 transform" 
            x-transition:leave-start="translate-x-0" 
            x-transition:leave-end="-translate-x-full" 
            class="fixed inset-y-0 left-0 z-40 flex w-full max-w-xs flex-col bg-gray-800 md:hidden"
        >
            <div class="h-16 flex items-center justify-between px-4 bg-gray-900">
                <div class="flex items-center">
                    <img src="{{ asset('images/logo-light.png') }}" alt="PangAIaShop" class="h-8 w-auto mr-2">
                    <span class="text-white text-lg font-semibold">PangAIaShop</span>
                </div>
                <button @click="sidebarOpen = false" class="text-gray-300 hover:text-white">
                    <span class="sr-only">Close sidebar</span>
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="flex-1 flex flex-col overflow-y-auto pt-5 pb-4">
                @include('admin.layouts.navigation')
            </div>
        </div>

        <!-- Static sidebar for desktop -->
        <div class="hidden md:fixed md:inset-y-0 md:flex md:w-64 md:flex-col">
            <div class="flex min-h-0 flex-1 flex-col bg-gray-800">
                <div class="h-16 flex items-center justify-center px-4 bg-gray-900">
                    <div class="flex items-center">
                        <img src="{{ asset('images/logo-light.png') }}" alt="PangAIaShop" class="h-8 w-auto mr-2">
                        <span class="text-white text-lg font-semibold">PangAIaShop</span>
                    </div>
                </div>
                <div class="flex flex-1 flex-col overflow-y-auto pt-5 pb-4">
                    @include('admin.layouts.navigation')
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="md:pl-64 flex flex-col flex-1">
            <!-- Top header -->
            <div class="sticky top-0 z-10 bg-white shadow-sm flex h-16">
                <button @click="sidebarOpen = true" class="md:hidden px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                    <span class="sr-only">Open sidebar</span>
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                <div class="flex-1 px-4 flex justify-between">
                    <div class="flex-1 flex items-center">
                        <h1 class="text-2xl font-semibold text-gray-800">@yield('header', 'Dashboard')</h1>
                    </div>
                    
                    <div class="ml-4 flex items-center md:ml-6">
                        <!-- Notifications -->
                        <div x-data="{ open: false }" class="relative mr-3">
                            <button @click="open = !open" class="relative p-1 rounded-full text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <span class="sr-only">View notifications</span>
                                <i class="fas fa-bell text-xl"></i>
                                @if(auth()->guard('admin')->user() && auth()->guard('admin')->user()->unreadNotifications && auth()->guard('admin')->user()->unreadNotifications->count() > 0)
                                    <span class="absolute top-0 right-0 block h-4 w-4 rounded-full bg-red-500 text-xs text-white text-center">
                                        {{ auth()->guard('admin')->user()->unreadNotifications->count() }}
                                    </span>
                                @endif
                            </button>
                            
                            <div x-show="open" 
                                @click.away="open = false" 
                                x-transition:enter="transition ease-out duration-100" 
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none py-1">
                                <div class="p-3 border-b border-gray-200">
                                    <h3 class="text-sm font-medium text-gray-900">Notifications</h3>
                                </div>
                                <div class="max-h-80 overflow-y-auto">
                                    @if(auth()->guard('admin')->user() && method_exists(auth()->guard('admin')->user(), 'notifications'))
                                        @forelse(auth()->guard('admin')->user()->notifications()->take(5)->get() as $notification)
                                            <div class="block px-4 py-3 hover:bg-gray-50 {{ $notification->read_at ? 'opacity-75' : '' }}">
                                                <div class="flex items-start">
                                                    <div class="flex-shrink-0 pt-0.5">
                                                        <i class="fas fa-{{ $notification->data['icon'] ?? 'bell' }} text-{{ $notification->data['color'] ?? 'blue' }}-500"></i>
                                                    </div>
                                                    <div class="ml-3 w-0 flex-1">
                                                        <p class="text-sm font-medium text-gray-900">{{ $notification->data['title'] }}</p>
                                                        <p class="text-sm text-gray-500">{{ Str::limit($notification->data['message'], 100) }}</p>
                                                        <p class="mt-1 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="px-4 py-6 text-center text-gray-500">
                                                <i class="fas fa-bell-slash text-2xl mb-2"></i>
                                                <p>No notifications yet</p>
                                            </div>
                                        @endforelse
                                    @else
                                        <div class="px-4 py-6 text-center text-gray-500">
                                            <i class="fas fa-bell-slash text-2xl mb-2"></i>
                                            <p>Notifications unavailable</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="border-t border-gray-200 p-2 text-center">
                                    <a href="{{ route('admin.notifications.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View all notifications</a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Profile dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex max-w-xs items-center rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <span class="sr-only">Open user menu</span>
                                @if(auth()->guard('admin')->user())
                                    <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode(auth()->guard('admin')->user()->name) }}&color=7F9CF5&background=EBF4FF" alt="{{ auth()->guard('admin')->user()->name }}">
                                @else
                                    <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name=User&color=7F9CF5&background=EBF4FF" alt="User">
                                @endif
                            </button>
                            
                            <div x-show="open" 
                                @click.away="open = false" 
                                x-transition:enter="transition ease-out duration-100" 
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" 
                                role="menu" 
                                aria-orientation="vertical" 
                                aria-labelledby="user-menu-button" 
                                tabindex="-1">
                                @if(auth()->guard('admin')->user())
                                    <div class="px-4 py-2 border-b border-gray-200">
                                        <p class="text-sm font-medium text-gray-900">{{ auth()->guard('admin')->user()->name }}</p>
                                        <p class="text-xs text-gray-500">{{ auth()->guard('admin')->user()->email }}</p>
                                    </div>
                                    
                                    <a href="{{ route('admin.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        <i class="fas fa-user mr-2 text-gray-400"></i> Your Profile
                                    </a>
                                    
                                    <a href="{{ route('admin.settings.general') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        <i class="fas fa-cog mr-2 text-gray-400"></i> Settings
                                    </a>
                                    
                                    <a href="{{ route('admin.help') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        <i class="fas fa-question-circle mr-2 text-gray-400"></i> Help & Support
                                    </a>
                                    
                                    <div class="border-t border-gray-200 pt-1">
                                        <form method="POST" action="{{ route('admin.logout') }}">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                                <i class="fas fa-sign-out-alt mr-2 text-red-400"></i> Sign out
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <a href="{{ route('admin.login') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                        <i class="fas fa-sign-in-alt mr-2 text-gray-400"></i> Sign in
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Page content -->
            <main class="flex-1">
                <div class="py-6">
                    <div class="mx-auto px-4 sm:px-6 md:px-8">
                        @if(session('success'))
                            <div x-data="{ show: true }"
                                x-init="setTimeout(() => show = false, 4000)"
                                x-show="show"
                                x-transition:leave="transition ease-in duration-300"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="mb-4 rounded-md bg-green-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-check-circle text-green-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                                    </div>
                                    <div class="ml-auto pl-3">
                                        <div class="-mx-1.5 -my-1.5">
                                            <button @click="show = false" class="inline-flex rounded-md bg-green-50 p-1.5 text-green-500 hover:bg-green-100">
                                                <span class="sr-only">Dismiss</span>
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if(session('error'))
                            <div x-data="{ show: true }"
                                x-init="setTimeout(() => show = false, 4000)"
                                x-show="show"
                                x-transition:leave="transition ease-in duration-300"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="mb-4 rounded-md bg-red-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle text-red-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                                    </div>
                                    <div class="ml-auto pl-3">
                                        <div class="-mx-1.5 -my-1.5">
                                            <button @click="show = false" class="inline-flex rounded-md bg-red-50 p-1.5 text-red-500 hover:bg-red-100">
                                                <span class="sr-only">Dismiss</span>
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if ($errors->any())
                            <div x-data="{ show: true }"
                                x-show="show"
                                x-transition:leave="transition ease-in duration-300"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="mb-4 rounded-md bg-red-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle text-red-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">There were {{ $errors->count() }} errors with your submission</h3>
                                        <div class="mt-2 text-sm text-red-700">
                                            <ul class="list-disc pl-5 space-y-1">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="ml-auto pl-3">
                                        <div class="-mx-1.5 -my-1.5">
                                            <button @click="show = false" class="inline-flex rounded-md bg-red-50 p-1.5 text-red-500 hover:bg-red-100">
                                                <span class="sr-only">Dismiss</span>
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Page content -->
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Global scripts -->
    @yield('scripts')
</body>
</html>