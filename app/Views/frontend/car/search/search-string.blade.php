<?php
    if($count != 1){
        $search_text = sprintf(__('%s Cars'), $count);
    }else{
        $search_text = sprintf(__('%s Car'), $count);
    }
    $address_text = '';
    if(!empty($address)){
        $address_text = sprintf(__('in %s'), '<strong>'. urldecode(esc_html($address)) .'</strong>');
    }

    $booking_type = get_car_booking_type();

    $date_string = '';
    if(!empty($check_in) && !empty($check_out)){
    	if($booking_type == 'hour'){
		    $check_in_time_str = strtolower(urldecode($check_in_time));
		    $check_out_time_str = strtolower(urldecode($check_out_time));

		    $date_string = sprintf(__('from %s to %s'), '<strong>'. esc_html(date(hh_date_format(), strtotime($check_in))) . ' ' . esc_html($check_in_time_str) . '</strong>', '<strong>'. esc_html(date(hh_date_format(), strtotime($check_out))) . ' ' . esc_html($check_out_time_str) . '</strong>');
        }else{
		    $date_string = sprintf(__('from %s to %s'), '<strong>'. esc_html(date(hh_date_format(), strtotime($check_in))) . '</strong>', '<strong>'. esc_html(date(hh_date_format(), strtotime($check_out))) . '</strong>');
        }
    }

?>
{{__('Found')}} <span class="item-found"><?php echo balanceTags($search_text); ?></span> <?php echo balanceTags($address_text . ' ' . $date_string) ?>
