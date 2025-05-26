@extends('frontend.layouts.master')

@section('title', 'PangAIaShop - Contact Us')

@section('content')
<!-- breadcrumb-section -->
<div class="breadcrumb-section breadcrumb-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <div class="breadcrumb-text">
                    <p>Get 24/7 Support</p>
                    <h1>Contact us</h1>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end breadcrumb section -->

<!-- contact form -->
<div class="contact-from-section mt-150 mb-150">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mb-5 mb-lg-0">
                <div class="form-title">
                    <h2>Have you got any question?</h2>
                    <p>Feel free to reach out to us with any questions, concerns, or feedback. Our team is here to assist you and provide the support you need.</p>
                </div>
                <div id="form_status"></div>
                <div class="contact-form">
                    <form action="{{ route('contact.submit') }}" method="POST" id="fruitkha-contact">
                        @csrf
                        <p>
                            <input type="text" placeholder="Name" name="name" id="name" required>
                            <input type="email" placeholder="Email" name="email" id="email" required>
                        </p>
                        <p>
                            <input type="tel" placeholder="Phone" name="phone" id="phone" required>
                            <input type="text" placeholder="Subject" name="subject" id="subject" required>
                        </p>
                        <p><textarea name="message" id="message" cols="30" rows="10" placeholder="Message" required></textarea></p>
                        <input type="hidden" name="token" value="FsWga4&@f6aw" />
                        <p><input type="submit" value="Submit"></p>
                    </form>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="contact-form-wrap">
                    <div class="contact-form-box">
                        <h4><i class="fas fa-map"></i> Shop Address</h4>
                        <p>123 Main Street <br> City, State 12345 <br> United States</p>
                    </div>
                    <div class="contact-form-box">
                        <h4><i class="far fa-clock"></i> Shop Hours</h4>
                        <p>MON - FRIDAY: 8 AM to 9 PM <br> SAT - SUN: 10 AM to 8 PM </p>
                    </div>
                    <div class="contact-form-box">
                        <h4><i class="fas fa-address-book"></i> Contact</h4>
                        <p>Phone: +123 456 7890 <br> Email: support@pangaiashop.com</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end contact form -->

<!-- find our location -->
<div class="find-location blue-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <p> <i class="fas fa-map-marker-alt"></i> Find Our Location</p>
            </div>
        </div>
    </div>
</div>
<!-- end find our location -->

<!-- google map section -->
<div class="embed-responsive embed-responsive-21by9">
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d26432.42324808999!2d-118.34398767954286!3d34.09378509738966!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80c2bf20e4c82873%3A0x14015754d926dadb!2sLos%20Angeles%2C%20CA%2C%20USA!5e0!3m2!1sen!2sbd!4v1576846473265!5m2!1sen!2sbd" width="600" height="450" frameborder="0" style="border:0;" allowfullscreen="" class="embed-responsive-item"></iframe>
</div>
<!-- end google map section -->
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Form validation and submission handling
        $('#fruitkha-contact').on('submit', function(e) {
            e.preventDefault();
            
            // You can implement AJAX form submission here
            // For now, let's just show a success message
            $('#form_status').html('<div class="alert alert-success">Thank you for your message. We will contact you soon!</div>');
            $(this).find('input:not([type="submit"]), textarea').val('');
        });
    });
</script>
@endsection