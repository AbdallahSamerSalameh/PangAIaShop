<?php

namespace App\Traits;

trait ImageFallbackHelper
{
    /**
     * Get a properly formatted image URL with fallback support
     *
     * @param string|null $imageUrl The primary image URL or path
     * @param string $type The type of image (product, profile, category, etc.)
     * @param array $fallbacks Additional fallback URLs/paths
     * @return array Contains 'url' and 'fallbacks' for frontend use
     */
    public function getImageWithFallbacks($imageUrl = null, $type = 'product', $fallbacks = [])
    {
        $defaultFallbacks = [
            'product' => 'admin-assets/img/undraw_posting_photo.svg',
            'profile' => 'admin-assets/img/undraw_profile.svg',
            'category' => 'admin-assets/img/undraw_posting_photo.svg',
            'vendor' => 'admin-assets/img/undraw_profile.svg',
            'admin' => 'admin-assets/img/undraw_profile.svg'
        ];

        $result = [
            'url' => null,
            'fallbacks' => []
        ];

        // Process main image URL
        if ($imageUrl) {
            $result['url'] = str_starts_with($imageUrl, 'http') ? $imageUrl : asset('storage/' . $imageUrl);
        }

        // Process custom fallbacks
        foreach ($fallbacks as $fallback) {
            if ($fallback) {
                $result['fallbacks'][] = str_starts_with($fallback, 'http') ? $fallback : asset('storage/' . $fallback);
            }
        }

        // Add default fallback
        $defaultFallback = $defaultFallbacks[$type] ?? $defaultFallbacks['product'];
        $result['fallbacks'][] = asset($defaultFallback);

        return $result;
    }

    /**
     * Get product image with category fallback
     *
     * @param object $product Product model with images and categories
     * @param bool $primaryOnly Whether to use only primary image or first available
     * @return array
     */
    public function getProductImageWithFallback($product, $primaryOnly = true)
    {
        $imageUrl = null;
        $categoryFallback = null;

        // Get product image
        if ($product->images && $product->images->count() > 0) {
            if ($primaryOnly) {
                $primaryImage = $product->images->where('is_primary', true)->first();
                $imageUrl = $primaryImage ? $primaryImage->image_url : $product->images->first()->image_url;
            } else {
                $imageUrl = $product->images->first()->image_url;
            }
        }

        // Get category fallback
        if ($product->directCategories && $product->directCategories->count() > 0 && $product->directCategories->first()->image_url) {
            $categoryFallback = $product->directCategories->first()->image_url;
        } elseif ($product->categories && $product->categories->count() > 0 && $product->categories->first()->image_url) {
            $categoryFallback = $product->categories->first()->image_url;
        }

        return $this->getImageWithFallbacks($imageUrl, 'product', array_filter([$categoryFallback]));
    }

    /**
     * Get user avatar with profile fallback
     *
     * @param object $user User model with avatar
     * @return array
     */
    public function getUserAvatarWithFallback($user)
    {
        $avatarUrl = $user->avatar_url ?? $user->profile_image ?? null;
        return $this->getImageWithFallbacks($avatarUrl, 'profile');
    }

    /**
     * Get category image with fallback
     *
     * @param object $category Category model with image
     * @return array
     */
    public function getCategoryImageWithFallback($category)
    {
        return $this->getImageWithFallbacks($category->image_url ?? null, 'category');
    }

    /**
     * Generate onerror attribute for image tags
     *
     * @param array $fallbacks Array of fallback URLs
     * @return string
     */
    public function generateOnerrorAttribute($fallbacks)
    {
        if (empty($fallbacks)) {
            return '';
        }

        if (count($fallbacks) === 1) {
            return "this.src='{$fallbacks[0]}'; this.onerror=null;";
        }

        $script = '';
        for ($i = 0; $i < count($fallbacks); $i++) {
            if ($i === count($fallbacks) - 1) {
                $script .= "this.src='{$fallbacks[$i]}'; this.onerror=null;";
            } else {
                $script .= "if(this.src !== '{$fallbacks[$i]}') { this.src='{$fallbacks[$i]}'; } else { ";
            }
        }

        // Close nested if statements
        for ($i = 0; $i < count($fallbacks) - 1; $i++) {
            $script .= ' }';
        }

        return $script;
    }
}
