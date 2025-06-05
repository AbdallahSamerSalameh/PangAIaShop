@extends('frontend.layouts.master')

@section('title', 'Terms of Service - PangAIaShop')

@section('styles')
<style>
    .legal-content {
        max-width: 800px;
        margin: 0 auto;
        padding: 40px 20px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        line-height: 1.6;
    }
    .legal-title {
        text-align: center;
        margin-bottom: 40px;
        color: #F28123;
        font-size: 2.5rem;
    }
    .legal-content h2 {
        color: #333;
        margin-top: 30px;
        margin-bottom: 15px;
        border-bottom: 2px solid #F28123;
        padding-bottom: 10px;
    }
    .legal-content h3 {
        color: #555;
        margin-top: 25px;
        margin-bottom: 10px;
    }
    .legal-content p {
        margin-bottom: 15px;
        color: #666;
    }
    .legal-content ul {
        margin-left: 20px;
        margin-bottom: 15px;
    }
    .legal-content li {
        margin-bottom: 8px;
        color: #666;
    }
    .last-updated {
        text-align: center;
        color: #999;
        font-style: italic;
        margin-top: 40px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
</style>
@endsection

@section('content')
<!-- breadcrumb-section -->
<div class="breadcrumb-section breadcrumb-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <div class="breadcrumb-text">
                    <p>Legal Information</p>
                    <h1>Terms of Service</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end breadcrumb section -->

<!-- terms content -->
<div class="contact-from-section mt-150 mb-150">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="legal-content">
                    <h1 class="legal-title">Terms of Service</h1>
                    
                    <h2>1. Acceptance of Terms</h2>
                    <p>By accessing and using PangAIaShop, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>
                    
                    <h2>2. Description of Service</h2>
                    <p>PangAIaShop is an e-commerce platform that provides online retail services. We reserve the right to modify or discontinue our service at any time without notice.</p>
                    
                    <h2>3. User Account</h2>
                    <p>To access certain features of our service, you may be required to create an account. You are responsible for:</p>
                    <ul>
                        <li>Maintaining the confidentiality of your account credentials</li>
                        <li>All activities that occur under your account</li>
                        <li>Providing accurate and current information</li>
                        <li>Notifying us immediately of any unauthorized use</li>
                    </ul>
                    
                    <h2>4. Product Information and Pricing</h2>
                    <p>We strive to provide accurate product descriptions and pricing. However, we do not warrant that product descriptions, prices, or other content is accurate, complete, reliable, current, or error-free.</p>
                    <p>We reserve the right to:</p>
                    <ul>
                        <li>Modify prices at any time without notice</li>
                        <li>Correct any errors in pricing or product information</li>
                        <li>Cancel orders due to pricing errors</li>
                    </ul>
                    
                    <h2>5. Orders and Payment</h2>
                    <p>All orders are subject to acceptance and availability. We reserve the right to refuse any order. Payment must be received in full before items are shipped.</p>
                    
                    <h2>6. Shipping and Returns</h2>
                    <p>Shipping times are estimates and may vary. Return policies are subject to our separate return policy document. Items must be returned in original condition within 30 days of purchase.</p>
                    
                    <h2>7. Prohibited Uses</h2>
                    <p>You may not use our service:</p>
                    <ul>
                        <li>For any unlawful purpose or to solicit others to perform unlawful acts</li>
                        <li>To violate any international, federal, provincial, or state regulations, rules, laws, or local ordinances</li>
                        <li>To infringe upon or violate our intellectual property rights or the intellectual property rights of others</li>
                        <li>To harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate</li>
                        <li>To submit false or misleading information</li>
                    </ul>
                    
                    <h2>8. Intellectual Property</h2>
                    <p>All content on this website, including but not limited to text, graphics, logos, images, and software, is the property of PangAIaShop and is protected by copyright and other intellectual property laws.</p>
                    
                    <h2>9. Limitation of Liability</h2>
                    <p>PangAIaShop shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of the service.</p>
                    
                    <h2>10. Governing Law</h2>
                    <p>These terms shall be governed and construed in accordance with applicable laws, without regard to its conflict of law provisions.</p>
                    
                    <h2>11. Changes to Terms</h2>
                    <p>We reserve the right to update or change our Terms of Service at any time. Any changes will be posted on this page with an updated revision date.</p>
                    
                    <h2>12. Contact Information</h2>
                    <p>If you have any questions about these Terms of Service, please contact us through our contact page or email us directly.</p>
                    
                    <div class="last-updated">
                        <p>Last updated: {{ date('F j, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end terms content -->
@endsection
