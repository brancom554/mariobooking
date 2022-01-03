<?php
    $enable_gdpr= get_option('enable_gdpr', 'off');
    if($enable_gdpr == 'on'){
        enqueue_script('gdpr-js');
        enqueue_style('gdpr-css');
    }
?>
<?php do_action('footer'); ?>
<?php do_action('init_footer'); ?>
<?php do_action('init_frontend_footer'); ?>
<script src="{{asset('js/frontend.js')}}"></script>
</body>
</html>
