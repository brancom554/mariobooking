<?php
global $hh_lazyload;

class EnqueueScripts
{
    private static $styles = [];
    private static $scripts = [];

    private static $enqueuedStyles = [];
    private static $enqueuedScripts = [];

    public function __construct()
    {
        add_action('init', [$this, '_registerScripts']);

        add_action('init_frontend_header', [$this, '_enqueueHeader']);
        add_action('init_dashboard_header', [$this, '_enqueueHeaderDashboard']);
        add_action('init_frontend_footer', [$this, '_enqueueFooter']);
        add_action('init_dashboard_footer', [$this, '_enqueueFooterDashboard']);

        add_action('init_frontend_header', [$this, '_customCSS'], 20);
        add_action('init_dashboard_header', [$this, '_customCSS'], 20);

        add_action('init_frontend_header', [$this, '_datePickerLanguage'], 20);
        add_action('init_dashboard_header', [$this, '_datePickerLanguage'], 20);

        add_action('init_frontend_header', [$this, '_customHeaderCode'], 20);

        add_action('init_frontend_footer', [$this, '_customFooterCode'], 20);

        add_action('hh_updated_option', [$this, '_applySSL']);
        add_action('hh_updated_option', [$this, '_setTimeZone']);
        add_action('hh_updated_option', [$this, '_setCronJobConfig']);
    }

    public function _setCronJobConfig($option_Value)
    {
        $option_Value = unserialize($option_Value);
        if (isset($option_Value['payout_date']) && isset($option_Value['payout_time'])) {
            setEnvironmentValue([
                'PAYOUT_DATE' => $option_Value['payout_date'],
                'PAYOUT_TIME' => $option_Value['payout_time']
            ]);
        }
        if (isset($option_Value['ical_time_type']) && isset($option_Value['ical_hour']) && isset($option_Value['ical_minute'])) {
            setEnvironmentValue([
                'ICAL_TYPE' => $option_Value['ical_time_type'],
                'ICAL_HOUR' => $option_Value['ical_hour'],
                'ICAL_MINUTE' => $option_Value['ical_minute'],
            ]);
        }
    }

    public function _datePickerLanguage()
    {
        $datepicker_language = [
            'direction' => is_rtl()? 'rtl': 'ltr',
            'applyLabel' => __('Apply'),
            'cancelLabel' => __('Cancel'),
            'fromLabel' => __('From'),
            'toLabel' => __('To'),
            'customRangeLabel' => __('Custom'),
            'daysOfWeek' => [__('Sun'), __('Mo'), __('Tu'), __('We'), __('Th'), __('Fr'), __('Sa')],
            'monthNames' => [__('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December')],
            'firstDay' => 1,
            'today' => __('Today'),
        ];
        echo '<script> var locale_daterangepicker = ' . json_encode($datepicker_language) . '</script>';
    }

    public function _customFooterCode()
    {
        $code = get_option('custom_footer_code');
        if (!empty($code)) {
            echo $code;
        }
    }

    public function _customHeaderCode()
    {
        $code = get_option('custom_header_code');
        if (!empty($code)) {
            echo $code;
        }
    }

    public function _customCSS()
    {
        $main_color = get_option('main_color', '#f8546d');
        if (!empty($main_color)) {
            echo "<style>
                :root {
                  --pink: {$main_color};
                  --black: #2a2a2a;
                  --blue: #1abc9c;
                  --white: #FFFFFF;
                }
            </style>";
        }
        $google_font = get_option('google_font');
        if (!empty($google_font)) {
            $google_font = explode(';', $google_font);
            if (count($google_font) == 3 && !empty($google_font[0])) {
                $font_name = ucwords(str_replace('-', ' ', $google_font[0]));
                $font_weight = $google_font[1];
                $font_lang = $google_font[2];

                $url = 'https://fonts.googleapis.com/css?family=' . $font_name;
                if (!empty($font_weight)) {
                    $url .= ':' . $font_weight;
                }
                if (!empty($font_lang)) {
                    $url .= ':' . $font_lang;
                }
                echo '<link href="' . e($url) . '" rel="stylesheet">';
                echo "<style>
                    body{
                        font-family: '{$font_name}', sans-serif;
                    }
                    .awe-booking h1, .awe-booking h2, .awe-booking h3, .awe-booking h4, .awe-booking h5, .awe-booking h6{
                        font-family: '{$font_name}', sans-serif;
                    }
                </style>";
            }
        }
        $css = get_option('custom_css');
        $css = base64_decode($css);
        if (!empty($css)) {
            echo '<style>' . balanceTags($css) . '</style>';
        }
    }

    public function _applySSL($option_Value)
    {
        $option_Value = unserialize($option_Value);
        if (isset($option_Value['use_ssl'])) {
            if ($option_Value['use_ssl'] == 'on') {
                updateEnv('APP_ENV', 'production_ssl');
            } else {
                updateEnv('APP_ENV', 'local');
            }
        }
    }

    public function _setTimeZone($option_Value)
    {
        $option_Value = maybe_unserialize($option_Value);
        if (isset($option_Value['timezone'])) {
            if (!empty($option_Value['timezone'])) {
                updateConfig('timezone', $option_Value['timezone']);
            } else {
                updateConfig('timezone', 'UTC');
            }
        }
    }

    public function _registerScripts()
    {
        global $hh_rtl;
        $hh_rtl = false;
        if (is_multi_language()) {
            $current_lang = get_current_language_data();
            $hh_rtl = !!(isset($current_lang['rtl']) && $current_lang['rtl'] == 'on');
        } else {
            $hh_rtl = !!(get_option('right_to_left', 'off') == 'on');
        }
        $this->addScript('vendor-js', asset('js/vendor.min.js'), false, true);
        $this->addStyle('vendor-css', asset('css/vendor.min.css'), true, true);

        $lazy_load = get_option('enable_lazyload', 'off');

        if ($lazy_load == 'on') {
            $this->addScript('lazy-js', asset('vendors/lazy/jquery.lazyscrollloading.js'), false, true, 'frontend');
        }
        if(is_rtl()){
            $this->addStyle('app-css', asset('vendors/app-rtl.min.css'), true, true);
        }else{
            $this->addStyle('app-css', asset('vendors/app.min.css'), true, true);
        }
        $this->addStyle('main-css', asset('css/main.min.css'), true, true);
        $this->addStyle('frontend-css', asset('css/frontend.min.css'), true, true, 'frontend');
        if(is_rtl()){
            $this->addStyle('frontend-rtl', asset('css/frontend-rtl.min.css'), true, true, 'frontend');
        }
        $this->addStyle('option-css', asset('css/option.min.css'), true, true, 'dashboard');
        if(is_rtl()){
            $this->addStyle('option-rtl', asset('css/option-rtl.min.css'), true, true, 'dashboard');
        }
        $this->addStyle('dashboard-css', asset('css/dashboard.min.css'), true, true, 'dashboard');
        if(is_rtl()){
            $this->addStyle('dashboard-rtl', asset('css/dashboard-rtl.min.css'), true, true, 'dashboard');
        }

        $this->addScript('image-loaded-js', asset('vendors/imagesloaded.pkgd.js'), false, true);
        $this->addScript('jquery-ui-js', asset('vendors/jquery-ui/jquery-ui.js'), false, true);
        $this->addScript('bootstrap-validate-js', asset('vendors/bootstrap-validate.js'), false, true);
        $this->addScript('toast-js', asset('vendors/jquery-toast/jquery.toast.js'), false, true);
        $this->addScript('bootstrap-maxlength-js', asset('vendors/bootstrap-maxlength/bootstrap-maxlength.js'), false, true);

        if (get_option('use_google_captcha') == 'on') {
            $this->addScript('google-captcha', 'https://www.google.com/recaptcha/api.js?render=' . get_option('google_captcha_site_key'), false, true, '', true);
        }

        $this->addScript('nested-sort-js', asset('vendors/jquery.mjs.nestedSortable.js'), false, true);

        $this->addScript('nice-select-js', asset('vendors/jquery-nice-select/jquery.nice-select.js'));
        $this->addStyle('nice-select-css', asset('vendors/jquery-nice-select/nice-select.css'));

        $this->addScript('select2-js', asset('vendors/select2/select2.js'));
        $this->addStyle('select2-css', asset('vendors/select2/select2.css'));

        $this->addScript('magnific-popup-js', asset('vendors/magnific-popup/magnific-popup.js'));
        $this->addStyle('magnific-popup-css', asset('vendors/magnific-popup/magnific-popup.css'));

        $this->addScript('switchery-js', asset('vendors/switchery/switchery.js'));
        $this->addStyle('switchery-css', asset('vendors/switchery/switchery.css'));

        $this->addScript('flatpickr-js', asset('vendors/flatpickr/flatpickr.js'));
        $this->addStyle('flatpickr-css', asset('vendors/flatpickr/flatpickr.css'));

        $this->addScript('bootstrap-colorpicker-js', asset('vendors/bootstrap-colorpicker/bootstrap-colorpicker.js'));
        $this->addStyle('bootstrap-colorpicker-css', asset('vendors/bootstrap-colorpicker/bootstrap-colorpicker.css'));

        $this->addScript('mapbox-gl-js', asset('vendors/mapbox/mapbox-gl.js'));
        $this->addScript('mapbox-gl-geocoder-js', asset('vendors/mapbox/mapbox-gl-geocoder.js'));
        $this->addStyle('mapbox-gl-css', asset('vendors/mapbox/mapbox-gl.css'));
        $this->addStyle('mapbox-gl-geocoder-css', asset('vendors/mapbox/mapbox-gl-geocoder.css'));

        $this->addScript('dropzone-js', asset('vendors/dropzone/dropzone.min.js'));
        $this->addStyle('dropzone-css', asset('vendors/dropzone/dropzone.min.css'));

        $this->addScript('countdown-js', asset('countdown.min.js'));

        $this->addScript('gdpr-js', asset('vendors/gdpr/gdpr.js'));
        $this->addStyle('gdpr-css', asset('vendors/gdpr/gdpr.css'));

        $this->addScript('datatables-js', asset('vendors/datatables/datatable.js'));
        $this->addScript('pdfmake-js', asset('vendors/pdfmake/pdfmake.js'));
        $this->addScript('vfs-fonts-js', asset('vendors/pdfmake/vfs_fonts.js'));
        $this->addStyle('datatables-css', asset('vendors/datatables/datatable.css'));

        $this->addScript('tinymce-js', asset('vendors/tinymce/tinymce.min.js'));

        $this->addScript('confirm-js', asset('vendors/confirm/jquery-confirm.js'));
        $this->addStyle('confirm-css', asset('vendors/confirm/jquery-confirm.css'));

        $this->addScript('tagify-js', asset('vendors/tagify/tagify.min.js'));
        $this->addStyle('tagify-css', asset('vendors/tagify/tagify.css'));

        $this->addScript('light-gallery-js', asset('vendors/lightGallery/js/lightgallery.js'));
        $this->addStyle('light-gallery-css', asset('vendors/lightGallery/css/lightgallery.css'));

        $this->addScript('daterangepicker-js', asset('vendors/daterangepicker/daterangepicker.js'));
        $this->addStyle('daterangepicker-css', asset('vendors/daterangepicker/daterangepicker.css'));

        $this->addStyle('home-slider', asset('vendors/slider/css/style.css'));
        $this->addScript('home-slider', asset('vendors/slider/js/slider.js'));

        $this->addStyle('iconrange-slider', asset('vendors/ion-rangeslider/ion.rangeSlider.css'));
        $this->addScript('iconrange-slider', asset('vendors/ion-rangeslider/ion.rangeSlider.js'));

        $this->addScript('sticky-js', asset('vendors/sticky-menu/jquery.sticky.js'));

        $this->addStyle('range-slider', asset('vendors/rangeslider/rangeslider.css'));
        $this->addScript('range-slider', asset('vendors/rangeslider/rangeslider.js'));

        $this->addScript('flot', asset('vendors/flot-charts/jquery.flot.js'));
        $this->addScript('flot-time', asset('vendors/flot-charts/jquery.flot.time.js'));
        $this->addScript('flot-tooltip', asset('vendors/flot-charts/jquery.flot.tooltip.min.js'));
        $this->addScript('flot-crosshair', asset('vendors/flot-charts/jquery.flot.crosshair.js'));
        $this->addScript('flot-selection', asset('vendors/flot-charts/jquery.flot.selection.js'));

        $this->addScript('nicescroll-js', asset('vendors/jquery.nicescroll.js'));
        $this->addScript('scroll-magic-js', asset('vendors/scroll-magic.js'));

        $this->addScript('ace-js', asset('vendors/ace/ace.js'));


        $this->addScript('owl-carousel', asset('vendors/owl-carousel/owl.carousel.min.js'));
        $this->addStyle('owl-carousel', asset('vendors/owl-carousel/assets/owl.carousel.min.css'));
        $this->addStyle('owl-carousel-theme', asset('vendors/owl-carousel/assets/owl.theme.default.min.css'));

        $this->addScript('context-menu-pos', asset('vendors/jquery-contextmenu/jquery.ui.position.min.js'));
        $this->addScript('context-menu', asset('vendors/jquery-contextmenu/jquery.contextMenu.min.js'));
        $this->addStyle('context-menu', asset('vendors/jquery-contextmenu/jquery.contextMenu.min.css'));

        $this->addScript('search-home-js', asset('js/search/home.js'));
        $this->addScript('search-car-js', asset('js/search/car.js'));
        $this->addScript('search-experience-js', asset('js/search/experience.js'));

        do_action('hh_register_scripts', $this);
    }

    public function _enqueueHeader()
    {
        $enable_lazyload = get_option('enable_lazyload', 'off');
        global $hh_lazyload;
        $hh_lazyload = $enable_lazyload;

        $enable_optimize = get_option('optimize_site', 'off');
        $current_route = \Illuminate\Support\Facades\Route::current();

        if ($enable_optimize == 'on' && is_object($current_route)) {
            HHMinify::get_inst()->renderCSS(true, 'frontend');
            HHMinify::get_inst()->renderJS(true, 'frontend');
        } else {
            $this->styleRender(true, 'frontend');
            $this->scriptRender(true, 'frontend');
        }
    }

    public function _enqueueHeaderDashboard()
    {
        $this->styleRender(true, 'dashboard');
        $this->scriptRender(true, 'dashboard');
    }

    public function _enqueueFooter()
    {
        $current_route = \Illuminate\Support\Facades\Route::current();
        $enable_optimize = get_option('optimize_site', 'off');
        if ($enable_optimize == 'on' && is_object($current_route)) {
            HHMinify::get_inst()->renderCSS(false, 'frontend');
            HHMinify::get_inst()->renderJS(false, 'frontend');
        } else {
            $this->styleRender(false, 'frontend');
            $this->scriptRender(false, 'frontend');
        }
    }

    public function _enqueueFooterDashboard()
    {
        $this->styleRender(false, 'dashboard');
        $this->scriptRender(false, 'dashboard');
    }

    public function addStyle($name, $url, $in_header = false, $queue = false, $type = '', $external = false)
    {
        if (!isset(self::$styles[$name])) {
            self::$styles[$name] = [
                'name' => $name,
                'url' => $url,
                'queue' => $queue,
                'header' => $in_header,
                'type' => $type,
                'external' => $external
            ];
        }
    }

    public function addScript($name, $url, $in_header = false, $queue = false, $type = '', $external = false)
    {
        if (!isset(self::$scripts[$name])) {
            self::$scripts[$name] = [
                'name' => $name,
                'url' => $url,
                'queue' => $queue,
                'header' => $in_header,
                'type' => $type,
                'external' => $external
            ];
        }
    }

    public function enqueueStyles()
    {
        foreach (self::$styles as $name => $style) {
            $this->_enqueueStyle($name);
        }
    }

    public function enqueueScripts()
    {
        foreach (self::$scripts as $name => $script) {
            $this->_enqueueScript($name);
        }
    }

    public function _enqueueScript($name)
    {
        if (isset(self::$scripts[$name])) {
            self::$scripts[$name]['queue'] = true;
        }
    }

    public function _enqueueStyle($name)
    {
        if (isset(self::$styles[$name])) {
            self::$styles[$name]['queue'] = true;
        }

    }

    public function styleRender($in_header = false, $type = '')
    {
        foreach (self::$styles as $name => $style) {
            if ($style['queue'] && $style['header'] == $in_header && !in_array($name, self::$enqueuedStyles) && in_array($style['type'], ['', $type])) {
                self::$enqueuedStyles[] = $name;
                echo '<link href="' . $style['url'] . '" rel="stylesheet">' . "\r\n";
            }
        }
    }

    public function scriptRender($in_header = false, $type = '')
    {
        foreach (self::$scripts as $name => $script) {
            if ($script['queue'] && $script['header'] == $in_header && !in_array($name, self::$enqueuedScripts) && in_array($script['type'], ['', $type])) {
                self::$enqueuedScripts[] = $name;
                echo '<script src="' . $script['url'] . '"></script>' . "\r\n";
            }
        }
    }

    public function get_styles()
    {
        return self::$styles;
    }

    public function get_enqueued_styles()
    {
        return self::$enqueuedStyles;
    }

    public function set_enqueued_styles($name)
    {
        self::$enqueuedStyles[] = $name;
    }

    public function get_scripts()
    {
        return self::$scripts;
    }

    public function get_enqueued_scripts()
    {
        return self::$enqueuedScripts;
    }

    public function set_enqueued_scripts($name)
    {
        self::$enqueuedScripts[] = $name;
    }

    public static function get_inst()
    {
        static $instance;
        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}
