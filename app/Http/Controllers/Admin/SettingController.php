<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display the settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $generalSettings = $this->getGeneralSettings();
        $paymentSettings = $this->getPaymentSettings();
        $shippingSettings = $this->getShippingSettings();
        $emailSettings = $this->getEmailSettings();
        
        return view('admin.settings.index', compact(
            'generalSettings', 
            'paymentSettings', 
            'shippingSettings',
            'emailSettings'
        ));
    }

    /**
     * Update the settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $settingType = $request->input('setting_type');
        
        switch ($settingType) {
            case 'general':
                return $this->updateGeneralSettings($request);
                
            case 'payment':
                return $this->updatePaymentSettings($request);
                
            case 'shipping':
                return $this->updateShippingSettings($request);
                
            case 'email':
                return $this->updateEmailSettings($request);
                
            default:
                return redirect()->route('admin.settings.index')
                    ->with('error', 'Invalid settings type.');
        }
    }
    
    /**
     * Get general settings.
     *
     * @return array
     */
    private function getGeneralSettings()
    {
        return [
            'site_name' => config('app.name'),
            'site_description' => setting('site_description', 'PangAIaShop - AI-Powered E-Commerce Platform'),
            'contact_email' => setting('contact_email', 'contact@pangaiashop.com'),
            'contact_phone' => setting('contact_phone', '+1-555-123-4567'),
            'address' => setting('address', '123 Market St, San Francisco, CA 94103'),
            'currency' => setting('currency', 'USD'),
            'currency_symbol' => setting('currency_symbol', '$'),
            'logo' => setting('logo'),
            'favicon' => setting('favicon'),
            'social_links' => [
                'facebook' => setting('social_facebook', 'https://facebook.com/pangaiashop'),
                'twitter' => setting('social_twitter', 'https://twitter.com/pangaiashop'),
                'instagram' => setting('social_instagram', 'https://instagram.com/pangaiashop'),
            ],
        ];
    }
    
    /**
     * Get payment settings.
     *
     * @return array
     */
    private function getPaymentSettings()
    {
        return [
            'payment_methods' => [
                'stripe' => [
                    'enabled' => setting('payment_stripe_enabled', true),
                    'test_mode' => setting('payment_stripe_test_mode', true),
                    'public_key' => setting('payment_stripe_public_key', ''),
                    'secret_key' => setting('payment_stripe_secret_key', ''),
                ],
                'paypal' => [
                    'enabled' => setting('payment_paypal_enabled', true),
                    'test_mode' => setting('payment_paypal_test_mode', true),
                    'client_id' => setting('payment_paypal_client_id', ''),
                    'client_secret' => setting('payment_paypal_client_secret', ''),
                ],
                'cash_on_delivery' => [
                    'enabled' => setting('payment_cod_enabled', true),
                    'fee' => setting('payment_cod_fee', 5.00),
                ],
            ],
            'tax_rate' => setting('tax_rate', 8.5),
            'allow_guest_checkout' => setting('allow_guest_checkout', true),
        ];
    }
    
    /**
     * Get shipping settings.
     *
     * @return array
     */
    private function getShippingSettings()
    {
        return [
            'shipping_methods' => [
                'standard' => [
                    'enabled' => setting('shipping_standard_enabled', true),
                    'name' => setting('shipping_standard_name', 'Standard Shipping'),
                    'cost' => setting('shipping_standard_cost', 5.99),
                    'min_days' => setting('shipping_standard_min_days', 3),
                    'max_days' => setting('shipping_standard_max_days', 7),
                ],
                'express' => [
                    'enabled' => setting('shipping_express_enabled', true),
                    'name' => setting('shipping_express_name', 'Express Shipping'),
                    'cost' => setting('shipping_express_cost', 14.99),
                    'min_days' => setting('shipping_express_min_days', 1),
                    'max_days' => setting('shipping_express_max_days', 3),
                ],
                'free' => [
                    'enabled' => setting('shipping_free_enabled', true),
                    'name' => setting('shipping_free_name', 'Free Shipping'),
                    'min_order_value' => setting('shipping_free_min_order', 75.00),
                    'min_days' => setting('shipping_free_min_days', 5),
                    'max_days' => setting('shipping_free_max_days', 10),
                ],
            ],
            'allowed_countries' => explode(',', setting('shipping_allowed_countries', 'US,CA,UK,AU')),
        ];
    }
    
    /**
     * Get email settings.
     *
     * @return array
     */
    private function getEmailSettings()
    {
        return [
            'from_name' => config('mail.from.name'),
            'from_email' => config('mail.from.address'),
            'notifications' => [
                'new_order' => setting('email_notification_new_order', true),
                'order_status_change' => setting('email_notification_order_status', true),
                'low_inventory' => setting('email_notification_low_inventory', true),
            ],
            'templates' => [
                'welcome' => setting('email_template_welcome', true),
                'order_confirmation' => setting('email_template_order_confirmation', true),
                'shipping_confirmation' => setting('email_template_shipping_confirmation', true),
                'delivery_confirmation' => setting('email_template_delivery_confirmation', true),
            ],
        ];
    }
    
    /**
     * Update general settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    private function updateGeneralSettings(Request $request)
    {
        $validatedData = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'required|string|max:500',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'currency' => 'required|string|size:3',
            'currency_symbol' => 'required|string|max:5',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|image|mimes:ico,png|max:1024',
            'social_facebook' => 'nullable|url|max:255',
            'social_twitter' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
        ]);
        
        // Update config for app name
        updateEnvFile(['APP_NAME' => '"' . $validatedData['site_name'] . '"']);
        
        // Update the rest of the settings
        foreach ($validatedData as $key => $value) {
            if ($key !== 'logo' && $key !== 'favicon') {
                setting([$key => $value]);
            }
        }
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('settings', 'public');
            setting(['logo' => $logoPath]);
        }
        
        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $faviconPath = $request->file('favicon')->store('settings', 'public');
            setting(['favicon' => $faviconPath]);
        }
        
        return redirect()->route('admin.settings.index')
            ->with('success', 'General settings updated successfully!');
    }
    
    /**
     * Update payment settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    private function updatePaymentSettings(Request $request)
    {
        $validatedData = $request->validate([
            'payment_stripe_enabled' => 'boolean',
            'payment_stripe_test_mode' => 'boolean',
            'payment_stripe_public_key' => 'nullable|string|max:255',
            'payment_stripe_secret_key' => 'nullable|string|max:255',
            
            'payment_paypal_enabled' => 'boolean',
            'payment_paypal_test_mode' => 'boolean',
            'payment_paypal_client_id' => 'nullable|string|max:255',
            'payment_paypal_client_secret' => 'nullable|string|max:255',
            
            'payment_cod_enabled' => 'boolean',
            'payment_cod_fee' => 'nullable|numeric|min:0',
            
            'tax_rate' => 'required|numeric|min:0|max:100',
            'allow_guest_checkout' => 'boolean',
        ]);
        
        foreach ($validatedData as $key => $value) {
            setting([$key => $value]);
        }
        
        return redirect()->route('admin.settings.index', ['tab' => 'payment'])
            ->with('success', 'Payment settings updated successfully!');
    }
    
    /**
     * Update shipping settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    private function updateShippingSettings(Request $request)
    {
        $validatedData = $request->validate([
            'shipping_standard_enabled' => 'boolean',
            'shipping_standard_name' => 'required|string|max:255',
            'shipping_standard_cost' => 'required|numeric|min:0',
            'shipping_standard_min_days' => 'required|integer|min:1',
            'shipping_standard_max_days' => 'required|integer|min:1',
            
            'shipping_express_enabled' => 'boolean',
            'shipping_express_name' => 'required|string|max:255',
            'shipping_express_cost' => 'required|numeric|min:0',
            'shipping_express_min_days' => 'required|integer|min:1',
            'shipping_express_max_days' => 'required|integer|min:1',
            
            'shipping_free_enabled' => 'boolean',
            'shipping_free_name' => 'required|string|max:255',
            'shipping_free_min_order' => 'required|numeric|min:0',
            'shipping_free_min_days' => 'required|integer|min:1',
            'shipping_free_max_days' => 'required|integer|min:1',
            
            'shipping_allowed_countries' => 'required|array',
            'shipping_allowed_countries.*' => 'string|size:2',
        ]);
        
        // Handle allowed countries as comma-separated string
        $validatedData['shipping_allowed_countries'] = implode(',', $validatedData['shipping_allowed_countries']);
        
        foreach ($validatedData as $key => $value) {
            setting([$key => $value]);
        }
        
        return redirect()->route('admin.settings.index', ['tab' => 'shipping'])
            ->with('success', 'Shipping settings updated successfully!');
    }
    
    /**
     * Update email settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    private function updateEmailSettings(Request $request)
    {
        $validatedData = $request->validate([
            'mail_from_name' => 'required|string|max:255',
            'mail_from_address' => 'required|email|max:255',
            
            'email_notification_new_order' => 'boolean',
            'email_notification_order_status' => 'boolean',
            'email_notification_low_inventory' => 'boolean',
            
            'email_template_welcome' => 'boolean',
            'email_template_order_confirmation' => 'boolean',
            'email_template_shipping_confirmation' => 'boolean',
            'email_template_delivery_confirmation' => 'boolean',
        ]);
        
        // Update mail configuration in .env
        updateEnvFile([
            'MAIL_FROM_NAME' => '"' . $validatedData['mail_from_name'] . '"',
            'MAIL_FROM_ADDRESS' => $validatedData['mail_from_address'],
        ]);
        
        // Remove mail specific settings as they're handled separately
        unset($validatedData['mail_from_name']);
        unset($validatedData['mail_from_address']);
        
        // Save the rest of the settings
        foreach ($validatedData as $key => $value) {
            setting([$key => $value]);
        }
        
        return redirect()->route('admin.settings.index', ['tab' => 'email'])
            ->with('success', 'Email settings updated successfully!');
    }
      // The updateEnvFile function has been moved to app/Helpers/functions.php
}
