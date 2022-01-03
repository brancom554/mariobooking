<?php do_action('init'); ?>
<?php do_action('frontend_init'); ?>
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $favicon = get_option('favicon');
        $favicon_url = get_attachment_url($favicon);
    @endphp
    <link rel="shortcut icon" type="image/png" href="{{ $favicon_url }}"/>

    <title>{{ page_title() }}</title>

    <?php
    $hh_params = [
        'timezone' => e(config('app.timezone')),
        'mapbox_token' => e(get_option('mapbox_key')),
        'currency' => current_currency(),
        'add_media_url' => e(dashboard_url('add-media')),
        'facebook_login' => e(get_option('facebook_login', 'off')),
        'facebook_api' => e(get_option('facebook_api')),
        'use_google_captcha' => e(get_option('use_google_captcha', 'off')),
        'google_captcha_key' => e(get_option('google_captcha_site_key')),
        'media_upload_permission' => get_media_upload_permission(),
        'media_upload_size' => get_media_upload_size(),
        'media_upload_message' => get_media_upload_message(),
        'enable_gdpr' => e(get_option('enable_gdpr', 'off')),
        'gdpr_page' => e(get_the_permalink(get_option('gdpr_page', ''), '', 'page')),
        'gdpr_lang' => [
            'description' => __('We use cookies to offer you a better browsing experience, personalise content and ads, to provide social media features and to analyse our traffic. Read about how we use cookies and how you can control them by clicking Cookie Settings. You consent to our cookies if you continue to use this website.'),
            'settings' => __('Cookie settings'),
            'accept' => __('Accept cookies'),
            'statement' => __('Our cookie statement'),
            'save' => __('Save settings'),
            'always_on' => __('Always on'),
            'cookie_essential_title' => __('Essential website cookies'),
            'cookie_essential_desc' => __('Necessary cookies help make a website usable by enabling basic functions like page navigation and access to secure areas of the website. The website cannot function properly without these cookies.'),
            'cookie_performance_title' => __('Performance cookies'),
            'cookie_performance_desc' => __('These cookies are used to enhance the performance and functionality of our websites but are non-essential to their use. For example it stores your preferred language or the region that you are in.'),
            'cookie_analytics_title' => __('Analytics cookies'),
            'cookie_analytics_desc' => __('We use analytics cookies to help us measure how users interact with website content, which helps us customize our websites and application for you in order to enhance your experience.'),
            'cookie_marketing_title' => __('Marketing cookies'),
            'cookie_marketing_desc' => __('These cookies are used to make advertising messages more relevant to you and your interests. The intention is to display ads that are relevant and engaging for the individual user and thereby more valuable for publishers and third party advertisers.')
        ],
        'lazy_load' => get_option('enable_lazyload', 'off')
    ];
    ?>
    <script>
        var hh_params = <?php echo json_encode($hh_params); ?>
    </script>
    <?php do_action('header'); ?>
    <?php do_action('init_header'); ?>
    <?php do_action('init_frontend_header'); ?>
    @php
        $body_class = isset($bodyClass)? $bodyClass: '';
        if(is_user_logged_in() && (is_admin() || is_partner())){
            $body_class .= ' has-admin-bar';
        }
    @endphp
</head>
<body class="awe-booking {{ $body_class }}">
<?php do_action('after_body_frontend'); ?>

@include('common.loading', ['class' => 'page-loading'])
