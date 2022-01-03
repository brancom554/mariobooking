<?php
if (!isset($cart)) {
    return;
}
$homeID = $cart['serviceID'];
$homeObject = unserialize($cart['serviceObject']);
?>
<h3 class="heading">{{__('Your Item')}}</h3>
<div class="card-box mt-3 cart-information cart-home-item">
    <div class="media service-detail d-flex align-items-center">
        @php
            $thumbnail = get_attachment_url($homeObject->thumbnail_id, [400, 400])
        @endphp
        <img src="{{ $thumbnail }}" class="mr-3"
             alt="{{ get_attachment_alt($homeObject->thumbnail_id) }}">
        <div class="media-body">
            <a target="_blank"
               href="{{ get_the_permalink($homeID) }}">{{ get_translate($homeObject->post_title) }}</a>
            @if ($address = get_translate($homeObject->location_address))
                <div class="desc mt-2">
                    <i class="fe-map-pin mr-1"></i> {{ $address }}
                </div>
            @endif
        </div>
    </div>
    <h5 class="title">{{__('Detail')}}</h5>
    <ul class="menu cart-list">
        @php
            $checkIn = $cart['cartData']['startDate'];
            $checkOut = $cart['cartData']['endDate'];
            $startTime = $cart['cartData']['startTime'];
            $endTime = $cart['cartData']['endTime'];
            $adults = $cart['cartData']['numberAdult'];
            $children = $cart['cartData']['numberChild'];
            $infant = $cart['cartData']['numberInfant'];
        @endphp
        <li>
            @if($homeObject->booking_type == 'per_night')
                <span>{{__('Check In/Out')}}</span>
                <span>
                {{ date(hh_date_format(), $checkIn) }} <i class="fe-arrow-right ml-2 mr-2"></i> {{ date(hh_date_format(), $checkOut) }}
                </span>
            @elseif($homeObject->booking_type == 'per_hour')
                <span>{{__('Date')}}</span>
                <span>
                {{ date(hh_date_format(), $checkIn) }}
                </span>
            @endif
        </li>
        @if($homeObject->booking_type == 'per_hour')
            <li>
                <span>{{__('Time')}}</span>
                <span>{{ date(hh_time_format(), $startTime) }} <i class="fe-arrow-right ml-2 mr-2"></i> {{ date(hh_time_format(), $endTime) }}</span>
            </li>
        @endif
        @if ($adults > 0)
            <li>
                <span>{{ _n(__('Adult'), __('Adults'), $adults) }}</span>
                <span> {{ $adults }}  </span>
            </li>
        @endif
        @if ($children > 0)
            <li>
                <span>{{ _n(__('Child'), __('Children'), $children) }}</span>
                <span>{{ $children }}</span>
            </li>
        @endif
        @if ($infant > 0)
            <li>
                <span>{{ _n(__('Infant'), __('Infants'), $infant) }}</span>
                <span>{{ $infant }}</span>
            </li>
        @endif
    </ul>
    @php
        $coupon = isset($cart['cartData']['coupon']) ? $cart['cartData']['coupon'] : [];
        $couponCode = isset($coupon->coupon_code) ? $coupon->coupon_code : '';
    @endphp
    <form action="{{ url('add-coupon') }}" class="form-sm form-action form-add-coupon"
          method="post"
          data-reload-time="1000">
        @include('common.loading')
        <div class="form-group">
            <label for="coupon">{{__('Coupon Code')}}</label>
            <input id="coupon" type="text" class="form-control" name="coupon"
                   value="{{ $couponCode }}"
                   placeholder="{{__('Have a coupon?')}}">
            <input type="hidden" name="service_id"
                   value="{{ $homeID }}">
            <input type="hidden" name="service_type"
                   value="home">
            <button class="btn" type="submit" name="sm"><i class="fe-arrow-right "></i>
            </button>
        </div>
        <div class="form-message"></div>
    </form>
    <h5 class="title">{{__('Summary')}}</h5>
    <ul class="menu cart-list">
        @php
            $numberNight = $cart['cartData']['numberNight'];
            $basePrice = $cart['basePrice'];
            $extraPrice = $cart['extraPrice'];
            $tax = $cart['tax'];
        @endphp
        <li>
            @if($homeObject->booking_type == 'per_night')
                <span>{{ _n(__('Price for %s night'), __('Price for %s nights'), $numberNight) }}</span>
            @elseif($homeObject->booking_type == 'per_hour')
                <span>{{ _n(__('Price for %s hour'), __('Price for %s hours'), $numberNight) }}</span>
            @endif
            <span>{{ convert_price($basePrice) }}</span>
        </li>
        @if ($extraPrice > 0)
            <li>
                <span>{{__('Extra Service')}}</span>
                <span>{{ convert_price($extraPrice) }}</span>
            </li>
        @endif
        @if (!empty($coupon))
            <li>
                <form action="{{ url('remove-coupon') }}" class="form-action" method="post"
                      data-reload-time="1500">
                    @include('common.loading')
                    <input type="hidden" name="homeID"
                           value="{{ $homeID }}">
                    <div class="d-flex align-items-center">
                        <span>
                            {{__('Coupon')}}
                            <button class="btn ml-2" type="submit" name="sm">(remove)</button>
                        </span>
                        <span>- {{ $coupon->couponPriceHtml }}</span>
                    </div>
                    <div class="form-message"></div>
                </form>
            </li>
        @endif
        <li class="divider">
            <span>{{__('Tax')}}
                <span class="text-muted">
                    @if ($cart['tax']['included'] == 'on')
                        {{__('(included)')}}
                    @endif
                </span>
            </span>
            <span>{{ $cart['tax']['tax'] }}%</span>
        </li>
        <li class="amount">
            <span>{{__('Amount')}}</span>
            <span>{{ convert_price($cart['amount']) }}</span>
        </li>
    </ul>
</div>
