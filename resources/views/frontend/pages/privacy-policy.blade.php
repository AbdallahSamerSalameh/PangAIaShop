@extends('frontend.layouts.master')

@section('title', 'Privacy Policy - PangAIaShop')

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
    .highlight-box {
        background-color: #f8f9fa;
        border-left: 4px solid #F28123;
        padding: 15px;
        margin: 20px 0;
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
                    <h1>Privacy Policy</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end breadcrumb section -->

<!-- privacy content -->
<div class="contact-from-section mt-150 mb-150">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="legal-content">
                    <h1 class="legal-title">Privacy Policy</h1>
                    
                    <div class="highlight-box">
                        <p><strong>Your Privacy Matters:</strong> This Privacy Policy explains how PangAIaShop collects, uses, and protects your personal information when you use our website and services.</p>
                    </div>
                    
                    <h2>1. Information We Collect</h2>
                    <h3>Personal Information</h3>
                    <p>We collect information you provide directly to us, such as:</p>
                    <ul>
                        <li>Name, email address, and phone number</li>
                        <li>Billing and shipping addresses</li>
                        <li>Payment information (processed securely)</li>
                        <li>Account credentials</li>
                        <li>Communications with our customer support</li>
                    </ul>
                    
                    <h3>Automatically Collected Information</h3>
                    <p>When you visit our website, we automatically collect:</p>
                    <ul>
                        <li>IP address and browser information</li>
                        <li>Device and operating system details</li>
                        <li>Pages visited and time spent on our site</li>
                        <li>Referring website information</li>
                        <li>Cookies and similar tracking technologies</li>
                    </ul>
                    
                    <h2>2. How We Use Your Information</h2>
                    <p>We use the collected information to:</p>
                    <ul>
                        <li>Process and fulfill your orders</li>
                        <li>Provide customer support and respond to inquiries</li>
                        <li>Send order confirmations and shipping updates</li>
                        <li>Improve our website and user experience</li>
                        <li>Send promotional emails (with your consent)</li>
                        <li>Prevent fraud and ensure security</li>
                        <li>Comply with legal obligations</li>
                    </ul>
                    
                    <h2>3. Information Sharing</h2>
                    <p>We do not sell or rent your personal information to third parties. We may share your information with:</p>
                    <ul>
                        <li><strong>Service Providers:</strong> Payment processors, shipping companies, and other trusted partners</li>
                        <li><strong>Legal Requirements:</strong> When required by law or to protect our rights</li>
                        <li><strong>Business Transfers:</strong> In case of merger, acquisition, or sale of our business</li>
                    </ul>
                    
                    <h2>4. Data Security</h2>
                    <p>We implement appropriate security measures to protect your personal information:</p>
                    <ul>
                        <li>SSL encryption for data transmission</li>
                        <li>Secure payment processing</li>
                        <li>Regular security assessments</li>
                        <li>Limited access to personal information</li>
                        <li>Regular software updates and security patches</li>
                    </ul>
                    
                    <h2>5. Cookies and Tracking</h2>
                    <p>We use cookies and similar technologies to:</p>
                    <ul>
                        <li>Remember your preferences and settings</li>
                        <li>Analyze website traffic and usage patterns</li>
                        <li>Provide personalized content and advertisements</li>
                        <li>Improve website functionality</li>
                    </ul>
                    <p>You can control cookies through your browser settings, but some features may not work properly if disabled.</p>
                    
                    <h2>6. Your Rights and Choices</h2>
                    <p>You have the right to:</p>
                    <ul>
                        <li>Access and update your personal information</li>
                        <li>Delete your account and associated data</li>
                        <li>Opt-out of promotional communications</li>
                        <li>Request a copy of your data</li>
                        <li>Correct inaccurate information</li>
                    </ul>
                    
                    <h2>7. Data Retention</h2>
                    <p>We retain your personal information for as long as necessary to provide our services and comply with legal obligations. Account information is kept for the duration of your account, plus a reasonable period thereafter for business and legal purposes.</p>
                    
                    <h2>8. Children's Privacy</h2>
                    <p>Our services are not intended for children under 13 years of age. We do not knowingly collect personal information from children under 13. If we become aware that a child has provided us with personal information, we will delete it promptly.</p>
                    
                    <h2>9. Third-Party Links</h2>
                    <p>Our website may contain links to third-party websites. We are not responsible for the privacy practices of these external sites. We encourage you to read their privacy policies.</p>
                    
                    <h2>10. International Users</h2>
                    <p>If you are accessing our services from outside our primary jurisdiction, please note that your information may be transferred to and processed in countries with different privacy laws.</p>
                    
                    <h2>11. Changes to Privacy Policy</h2>
                    <p>We may update this Privacy Policy from time to time. Significant changes will be communicated via email or prominently displayed on our website. Your continued use of our services constitutes acceptance of the updated policy.</p>
                    
                    <h2>12. Contact Us</h2>
                    <p>If you have questions about this Privacy Policy or our data practices, please contact us:</p>
                    <ul>
                        <li>Through our contact form</li>
                        <li>By email at privacy@pangaiashop.com</li>
                        <li>By mail at our business address</li>
                    </ul>
                    
                    <div class="last-updated">
                        <p>Last updated: {{ date('F j, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end privacy content -->
@endsection
