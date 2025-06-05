{{--
    Standardized Image Component with Fallback System
    
    Usage Examples:
    
    1. Product Image with Category Fallback:
    @include('admin.components.image-with-fallback', [
        'src' => $productImageUrl,
        'alt' => $product->name,
        'fallbacks' => [$categoryImageUrl],
        'class' => 'img-thumbnail',
        'style' => 'width: 50px; height: 50px; object-fit: cover;'
    ])
    
    2. User Avatar with Default Profile Fallback:
    @include('admin.components.image-with-fallback', [
        'src' => $user->avatar_url,
        'alt' => $user->username,
        'type' => 'profile',
        'class' => 'rounded-circle',
        'style' => 'width: 40px; height: 40px;'
    ])
    
    3. Category Image with Default Product Fallback:
    @include('admin.components.image-with-fallback', [
        'src' => $category->image_url,
        'alt' => $category->name,
        'type' => 'product',
        'class' => 'img-fluid'
    ])
--}}

@php
    // Determine image type and set appropriate fallbacks
    $type = $type ?? 'product';
    $defaultFallbacks = [
        'product' => asset('admin-assets/img/undraw_posting_photo.svg'),
        'profile' => asset('admin-assets/img/undraw_profile.svg'),
        'category' => asset('admin-assets/img/undraw_posting_photo.svg'),
        'vendor' => asset('admin-assets/img/undraw_profile.svg'),
        'admin' => asset('admin-assets/img/undraw_profile.svg')
    ];
    
    // Smart URL handling for the main image
    $mainImageUrl = '';
    if ($src) {
        $mainImageUrl = str_starts_with($src, 'http') ? $src : asset('storage/' . $src);
    }
    
    // Process custom fallbacks
    $customFallbacks = $fallbacks ?? [];
    $processedFallbacks = [];
    
    foreach ($customFallbacks as $fallback) {
        if ($fallback) {
            $processedFallbacks[] = str_starts_with($fallback, 'http') ? $fallback : asset('storage/' . $fallback);
        }
    }
    
    // Add default fallback for the type
    $finalFallback = $defaultFallbacks[$type] ?? $defaultFallbacks['product'];
    
    // Build the fallback chain
    $fallbackChain = array_filter(array_merge($processedFallbacks, [$finalFallback]));
    
    // Generate onerror handler
    $onerrorHandler = '';
    if (count($fallbackChain) > 0) {
        if (count($fallbackChain) === 1) {
            // Single fallback
            $onerrorHandler = "this.src='{$fallbackChain[0]}'; this.onerror=null;";
        } else {
            // Multiple fallbacks
            $fallbackScript = '';
            for ($i = 0; $i < count($fallbackChain); $i++) {
                if ($i === count($fallbackChain) - 1) {
                    // Last fallback
                    $fallbackScript .= "this.src='{$fallbackChain[$i]}'; this.onerror=null;";
                } else {
                    // Intermediate fallback
                    $fallbackScript .= "if(this.src !== '{$fallbackChain[$i]}') { this.src='{$fallbackChain[$i]}'; } else { ";
                }
            }
            // Close the nested if statements
            for ($i = 0; $i < count($fallbackChain) - 1; $i++) {
                $fallbackScript .= ' }';
            }
            $onerrorHandler = $fallbackScript;
        }
    }
    
    // Default attributes
    $class = $class ?? 'img-fluid';
    $style = $style ?? '';
    $alt = $alt ?? 'Image';
    $loading = $loading ?? 'lazy';
    $id = $id ?? '';
    $dataAttributes = $dataAttributes ?? [];
@endphp

@if($mainImageUrl)
    <img 
        @if($id) id="{{ $id }}" @endif
        src="{{ $mainImageUrl }}" 
        alt="{{ $alt }}" 
        class="{{ $class }}"
        @if($style) style="{{ $style }}" @endif
        @if($onerrorHandler) onerror="{{ $onerrorHandler }}" @endif
        @if($loading) loading="{{ $loading }}" @endif
        @foreach($dataAttributes as $key => $value)
            data-{{ $key }}="{{ $value }}"
        @endforeach
    >
@elseif(count($fallbackChain) > 0)
    {{-- No main image, show first fallback directly --}}
    <img 
        @if($id) id="{{ $id }}" @endif
        src="{{ $fallbackChain[0] }}" 
        alt="{{ $alt }}" 
        class="{{ $class }}"
        @if($style) style="{{ $style }}" @endif
        @if(count($fallbackChain) > 1) onerror="this.src='{{ $finalFallback }}'; this.onerror=null;" @endif
        @if($loading) loading="{{ $loading }}" @endif
        @foreach($dataAttributes as $key => $value)
            data-{{ $key }}="{{ $value }}"
        @endforeach
    >
@else
    {{-- No image and no fallbacks, show placeholder --}}
    <div class="bg-light d-flex align-items-center justify-content-center {{ $class }}" @if($style) style="{{ $style }}" @endif>
        <i class="fas fa-image text-muted"></i>
    </div>
@endif
