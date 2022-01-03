@include('frontend.components.header')
@php
    enqueue_script('scroll-magic-js');
    global $post;
@endphp
<div class="single-page single-car pb-5">
    <!-- Gallery -->
    @php
        $gallery = $post->gallery;
        $thumbnail_id = get_car_thumbnail_id($post->post_id);
        $thumbnailUrl = get_attachment_url($thumbnail_id, 'full');
    @endphp
    <div class="hh-gallery hh-thumbnail has-background-image" data-src="{{ $thumbnailUrl }}" style="background-image: url({{ $thumbnailUrl }})">
        <div class="controls">
            <a href="javascript: void(0);" class="view-gallery item-link"><span>{{__('View Photos')}}</span> <i class="ti-gallery"></i> </a>
            @if(!empty($post->video))
                @php
                    enqueue_script('magnific-popup-js');
                    enqueue_style('magnific-popup-css');
                @endphp
                <a href="{{$post->video}}" class="view-video item-link ml-1 hh-iframe-popup" data-effect="mfp-zoom-in"><span>{{__('View Video')}}</span> <i class="ti-video-clapper"></i> </a>
            @endif
        </div>
        @php
            if ( !empty( $gallery ) ) {
                enqueue_script('light-gallery-js');
                enqueue_style('light-gallery-css');

                $gallery = explode(',', $gallery);
                $data    = [];
                foreach ( $gallery as $key => $val ) {
                    $url       = get_attachment_url( $val );
                    if ( !empty( $url ) ) {
                        $data[] = [
                            'src'     =>$url
                        ];
                    }
                }
                if ( !empty( $data ) ) {
                    $data = base64_encode(json_encode( $data ));
                    echo '<div class="data-gallery" data-gallery="' .esc_attr($data) . '"></div>';
                }
            }
        @endphp
    </div>
    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-8 col-md-8 col-lg-9 col-content">
                @include('frontend.components.breadcrumb', ['currentPage' => get_translate($post->post_title)])
                <h1 class="title mt-3">
                    {{ get_translate($post->post_title) }}
                    @if($post->is_featured == 'on')
                        <span class="is-featured">{{ get_option('featured_text', __('Featured')) }}</span>
                    @endif
                </h1>
                @if ($post->location_address)
                    <p class="location">
                        <i class="ti-location-pin"></i>
                        {{ get_translate($post->location_address) }}
                    </p>
                @endif
                @php
                    $rate = $post->review_count;
                @endphp
                @if ($rate)
                    <div class="count-reviews">
                        <span class="count">{{ _n(__('%s review'), __('%s reviews'), $rate) }}</span>
                        {!! star_rating_render($post->rating) !!}
                    </div>
                @endif
                <div class="featured-amenities mt-2 mb-2">
                    <div class="item">
                        <h4>{{__('Passenger:')}}</h4>

                        <span> {{ $post->passenger }} </span>
                    </div>
                    <div class="item">
                        <h4>{{__('Gear Shift:')}}</h4>
                        <span>{{ get_translate($post->gear_shift) }}</span>
                    </div>
                    <div class="item">
                        <h4>{{__('Baggage:')}}</h4>
                        <span>{{ $post->baggage }}</span>
                    </div>
                    <div class="item">
                        <h4>{{__('Door:')}}</h4>
                        <span>{{ $post->door }}</span>
                    </div>
                    @php
                        $car_type_arr = [];
                        $tax_car_types = $post->tax_car_types;
                        if (!empty($tax_car_types) && is_object($tax_car_types)){
                            foreach ($tax_car_types as $type_object){
                                    array_push($car_type_arr, get_translate($type_object->term_title));
                            }
                        }
                    @endphp
                    @if(!empty($car_type_arr))
                        <div class="item">
                            <h4>{{__('Type:')}}</h4>
                            <span>{{ implode(', ', $car_type_arr) }}</span>
                        </div>
                    @endif
                </div>
                <h2 class="heading mt-4 mb-2">{{__('Detail')}}</h2>
                {!! balanceTags(get_translate($post->post_content)) !!}
                @php
                    $features = $post->tax_car_features;
                @endphp
                @if (!empty($features) && is_object($features))
                    <h2 class="heading mt-4 mb-2">{{__('Features')}}</h2>
                    <div class="amenities row">
                        @foreach ($features as $feature)
                            <div class="col-6 col-sm-4 col-lg-3">
                                <div class="amenity-item" data-toggle="ots-tooltip"
                                     data-title="{{ get_translate($feature->term_description) }}">
                                    @if (!$feature->term_icon)
                                        <i class="fe-check"></i>
                                    @else
                                        {!! get_icon($feature->term_icon, '#2a2a2a', '30px', '')  !!}
                                    @endif
                                    <span class="title">{{ get_translate($feature->term_title) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                <h2 class="heading mt-3 mb-2">{{__('Policies')}}</h2>
                @php
                    $enableCancellation = $post->enable_cancellation;
                    $cancelBeforeDay = (int)$post->cancel_before;
                    $cancellationDetail = $post->cancellation_detail;
                @endphp
                <div class="w-100"></div>
                @if ($enableCancellation == 'on')
                    <div class="item d-inline-block mr-4 mb-3">
                        <span class="font-weight-bold">{{__('Cancellation:')}}</span>
                        <span class="ml-2 small-info bg-success">{{__('enable')}}</span>
                        <span class="d-inline-block ml-1">{{ sprintf(__('before %s day(s)'), $cancelBeforeDay) }}</span>
                    </div>
                    @if (get_translate($cancellationDetail))
                        <div class="w-100">{!! balanceTags(get_translate($cancellationDetail)) !!}</div>
                    @endif
                @endif
                <h2 class="heading mt-4 mb-2">{{__('On Map')}}</h2>
                @php
                    $lat = $post->location_lat;
                    $lng = $post->location_lng;
                    $zoom = $post->location_zoom;

                    enqueue_style('mapbox-gl-css');
                    enqueue_style('mapbox-gl-geocoder-css');
                    enqueue_script('mapbox-gl-js');
                    enqueue_script('mapbox-gl-geocoder-js');
                @endphp
                <div class="hh-mapbox-single" data-lat="{{ $lat }}"
                     data-lng="{{ $lng }}" data-zoom="{{ $zoom }}"></div>
                @php
                    $author = get_user_by_id($post->author);
                    $address = $author->address;
                    $location = $author->location;
                    $country = get_country_by_code($location);
                    $description = $author->description;
                @endphp
            </div>
            <div class="col-12 col-sm-4 col-md-4 col-lg-3 col-sidebar">
                @php
                    enqueue_style('daterangepicker-css');
                    enqueue_script('daterangepicker-js');
                    enqueue_script('daterangepicker-lang-js');
                    $checkInOut = request()->get('checkInOut', '');
                    $checkIn = request()->get('checkIn', '');
                    $checkOut = request()->get('checkOut', '');
                    $checkInTime = request()->get('checkInTime', '');
                    $checkOutTime = request()->get('checkOutTime', '');
                    if(strlen($checkInTime) == 7){
                        $checkInTime = '0' . $checkInTime;
                    }
                    if(strlen($checkOutTime) == 7){
                        $checkOutTime = '0' . $checkOutTime;
                    }
                @endphp
                @php
                    $booking_form  = $post->booking_form;
                    $booking_type = get_car_booking_type();
                @endphp
                <div id="form-book-car"
                     class="form-book form-book-car"
                     data-real-price="{{ url('get-car-price-realtime') }}"
                     data-booking-type="{{$booking_type}}">
                    <div class="popup-booking-form-close">{!! get_icon('001_close', '#FFFFFF', '28px', '28px') !!}</div>
                    <div class="form-head">
                        <div class="price-wrapper">
                            <span class="price">{{ convert_price($post->base_price) }}</span>
                            <span class="unit">/{{get_car_unit()}}</span>
                        </div>
                    </div>

                    <div class="form-body relative">
                        @include('common.loading', ['class' => 'booking-loading'])
                        @if($booking_form == 'instant_enquiry')
                            <ul class="nav nav-tabs nav-bordered row">
                                <li class="nav-item nav-item-booking-form-instant col">
                                    <a href="#booking-form-instant"
                                       data-toggle="tab"
                                       aria-expanded="false"
                                       class="nav-link @if($booking_form == 'instant_enquiry' ||$booking_form == 'instant') active @endif">
                                        {{ __('Instant') }}
                                    </a>
                                </li>
                                <li class="nav-item nav-item-booking-form-instant col">
                                    <a href="#booking-form-enquiry"
                                       data-toggle="tab"
                                       aria-expanded="false"
                                       class="nav-link @if($booking_form == 'enquiry') active @endif">
                                        {{ __('Enquiry') }}
                                    </a>
                                </li>
                            </ul>
                        @endif
                        @if($booking_form == 'instant_enquiry')
                            <div class="tab-content">
                                @endif
                                @if($booking_form == 'instant_enquiry' || $booking_form == 'instant')
                                    <div
                                        class="tab-pane @if($booking_form == 'instant_enquiry' ||$booking_form == 'instant') active @endif"
                                        id="booking-form-instant">
                                        <form class="form-action" action="{{ url('add-to-cart-car') }}" method="post"
                                              data-loading-from=".form-body">

                                            <div class="form-group">
                                                <label for="checkinout">{{ __('Date') }}</label>
                                                <div class="date-wrapper date-double"
                                                     data-date-format="{{ hh_date_format_moment() }}"
                                                     data-action="{{ url('get-car-availability-single') }}"
                                                     data-action-time="{{ url('get-car-availability-time-single') }}"
                                                     data-single-click="false"
                                                     data-same-date="true">
                                                    <input type="text" class="input-hidden check-in-out-field"
                                                           name="checkInOut" data-car-id="{{ $post->post_id }}"
                                                           data-car-encrypt="{{ hh_encrypt($post->post_id) }}" value="{{$checkInOut}}">
                                                    <input type="text" class="input-hidden check-in-field"
                                                           name="checkIn" value="{{$checkIn}}">
                                                    <input type="text" class="input-hidden check-out-field"
                                                           name="checkOut" value="{{$checkOut}}">
                                                    <span class="check-in-render"
                                                          data-date-format="{{ hh_date_format_moment() }}"></span>
                                                    <span class="divider"></span>
                                                    <span class="check-out-render"
                                                          data-date-format="{{ hh_date_format_moment() }}"></span>
                                                </div>
                                            </div>

                                            <div class="form-group form-group-date-time @if($booking_type == 'hour' && (empty($checkInTime) || empty($checkOutTime))) d-none @endif">
                                                <label>{{ __('Time') }}</label>
                                                @php
                                                    if($booking_type == 'day' || (!empty($checkInTime) && !empty($checkOutTime))){
                                                        $listTime = list_hours(15);
                                                    }
                                                @endphp
                                                <div class="date-wrapper date-time">
                                                    <div class="date-render check-in-render"
                                                         data-time-format="{{ hh_time_format() }}"
                                                         data-same-time="true">
                                                        <div class="render">
                                                            @if(!empty($checkInTime))
                                                                {{$checkInTime}}
                                                            @else
                                                                {{__('Start Time')}}
                                                            @endif
                                                        </div>
                                                        <div class="dropdown-time">
                                                            @if($booking_type == 'day' || (!empty($checkInTime) && !empty($checkOutTime)))
                                                                @foreach($listTime as $key => $value)
                                                                    <div class="item @if($checkInTime == $key) active @endif" data-value="{{ $key }}">{{ $value }}</div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                        <input type="hidden" name="startTime" value="{{$checkInTime}}"
                                                               class="input-checkin"/>
                                                    </div>
                                                    <span class="divider"></span>
                                                    <div class="date-render check-out-render"
                                                         data-time-format="{{ hh_time_format() }}">
                                                        <div class="render">
                                                            @if(!empty($checkOutTime))
                                                                {{$checkOutTime}}
                                                            @else
                                                                {{__('End Time')}}
                                                            @endif
                                                        </div>
                                                        <div class="dropdown-time">
                                                            @if($booking_type == 'day' || (!empty($checkInTime) && !empty($checkOutTime)))
                                                                @foreach($listTime as $key => $value)
                                                                    <div class="item @if($checkOutTime == $key) active @endif" data-value="{{ $key }}">{{ $value }}</div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                        <input type="hidden" name="endTime" value="{{$checkOutTime}}"
                                                               class="input-checkin"/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                @php
                                                    if(!empty($post->quantity)){
                                                        $quantity = $post->quantity;
                                                    }else{
                                                        $quantity  = 20;
                                                    }
                                                @endphp
                                                <div class="guest-group">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="pull-left">{{__('Number')}}</span>
                                                        <div class="d-flex align-items-center">
                                                            <i class="decrease ti-minus"></i>
                                                            <input type="text" min="1" step="1" max="{{$quantity}}" name="number" value="1" readonly="" class="car-number">
                                                            <i class="increase ti-plus"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @php
                                                $equipments = get_equipments($post->equipments, $post->tax_car_equipments);
                                            @endphp
                                            @if(count($equipments) > 0)
                                            <div class="fomr-group">
                                                <div class="extra-services">
                                                    <label class="d-block mb-2" for="extra-services">
                                                        <span>{{__('Equipments')}}</span>
                                                        <a href="#extra-not-required-collapse" class="right"
                                                           data-toggle="collapse">{!! get_icon('002_download_1', '#2a2a2a','15px') !!}</a>
                                                    </label>

                                                    <div id="extra-not-required-collapse" class="collapse">
                                                        @foreach ($equipments as $key => $val)
                                                            <div class="extra-item d-flex">
                                                                <div class="checkbox checkbox-success">
                                                                    <input
                                                                            id="extra-service-{{ $key }}"
                                                                            class="input-extra"
                                                                            type="checkbox" name="equipment[]"
                                                                            value="{{ $key }}">
                                                                    <label
                                                                            for="extra-service-{{ $key }}">
                                                                            <span
                                                                                    class="name">{{ get_translate($val['title']) }}</span>
                                                                    </label>
                                                                </div>
                                                                <span
                                                                        class="price ml-auto">{{ convert_price($val['price']) }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                            @php
                                                $insurance_plan = $post->insurance_plan;
                                                $insurance_plan = maybe_unserialize($insurance_plan);
                                            @endphp
                                            @if(!empty($insurance_plan))
                                                <div class="extra-services">
                                                    <label class="d-block mb-2" for="extra-services">
                                                        <span>{{__('Insurance Plan')}}</span>
                                                        <a href="#extra-not-required-collapse1" class="right"
                                                           data-toggle="collapse">{!! get_icon('002_download_1', '#2a2a2a','15px') !!}</a>
                                                    </label>
                                                    <div id="extra-not-required-collapse1" class="collapse">
                                                        @foreach ($insurance_plan as $ip)
                                                            @php
                                                                if($ip['fixed'] == 'on'){
                                                                    $price_type = __('Fixed Price');
                                                                }else{
                                                                    $price_type = sprintf(__('Price per %s'), $booking_type);
                                                                }
                                                            @endphp
                                                            <div class="extra-item d-flex">
                                                                <div class="checkbox checkbox-success">
                                                                    <input
                                                                            id="extra-service-{{ $ip['name_unique'] }}"
                                                                            class="input-extra"
                                                                            type="checkbox" name="insurancePlan[]"
                                                                            value="{{ $ip['name_unique'] }}">
                                                                    <label
                                                                            for="extra-service-{{ $ip['name_unique'] }}">
                                                                            <span
                                                                                    class="name">{{ get_translate($ip['name']) }}</span> <i class="position-relative t-2 c-666 dripicons-information field-desc" data-toggle="tooltip" data-placement="top" data-html="true" title="{{$ip['description']}}<h4>{{$price_type}}</h4>"></i>
                                                                    </label>
                                                                </div>
                                                                <span
                                                                        class="price ml-auto">{{ convert_price($ip['price']) }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="form-group form-render">
                                            </div>
                                            <div class="form-group mt-2 mb-0">
                                                <input type="hidden" name="carID" value="{{ $post->post_id }}">
                                                <input type="hidden" name="carEncrypt"
                                                       value="{{ hh_encrypt($post->post_id) }}">
                                                <input type="submit" class="btn btn-primary btn-block text-uppercase"
                                                       name="sm"
                                                       value="{{__('Book Now')}}">
                                            </div>
                                            <div class="form-message"></div>
                                        </form>
                                    </div>
                                @endif
                                @if($booking_form == 'instant_enquiry' || $booking_form == 'enquiry')
                                    <div class="tab-pane @if($booking_form == 'enquiry') active @endif"
                                         id="booking-form-enquiry">
                                        <form action="{{ url('car-send-enquiry-form') }}" data-google-captcha="yes"
                                              class="form-action form-sm has-reset" data-loading-from=".form-body">
                                            <div class="form-group">
                                                <label for="full-name-enquiry-form">{{ __('Full Name') }} <span
                                                        class="text-danger">*</span></label>
                                                <input id="full-name-enquiry-form" type="text" name="name"
                                                       class="form-control has-validation" data-validation="required">
                                            </div>
                                            <div class="form-group">
                                                <label for="email-enquiry-form">{{ __('Email') }} <span
                                                        class="text-danger">*</span></label>
                                                <input id="email-enquiry-form" type="email" name="email"
                                                       class="form-control has-validation"
                                                       data-validation="required|email">
                                            </div>
                                            <div class="form-group">
                                                <label for="message-enquiry-form">{{ __('Message') }} <span
                                                        class="text-danger">*</span></label>
                                                <textarea id="message-enquiry-form" class="form-control has-validation"
                                                          name="message" data-validation="required"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <input type="submit" class="btn btn-primary btn-block text-uppercase"
                                                       name="sm"
                                                       value="{{ __('Send a Request') }}">
                                            </div>
                                            <input type="hidden" name="post_id" value="{{ $post->post_id }}">
                                            <input type="hidden" name="post_encrypt"
                                                   value="{{ hh_encrypt($post->post_id) }}">
                                            <div class="form-message"></div>
                                        </form>
                                    </div>
                                @endif
                                @if($booking_form == 'instant_enquiry')
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @php
            $lat = $post->location_lat;
            $lng = $post->location_lng;
            $list_services = \App\Controllers\Services\CarController::get_inst()->listOfCars([
                'number' => 4,
                'location' => [
                    'lat' => $lat,
                    'lng' => $lng,
                    'radius' => 25
                ],
                'orderby' => 'distance',
                'order' => 'asc',
                'not_in' => [$post->post_id]
            ]);
        @endphp
        @if(count($list_services['results']))
            <h2 class="heading mt-4 mb-2">{{__('Cars Near By')}}</h2>
            <div class="hh-list-of-services">
                <div class="row">
                    @foreach($list_services['results'] as $item)
                        <div class="col-12 col-md-6 col-lg-3">
                            @include('frontend.car.loop.grid', ['item' => $item, 'show_distance' => true])
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        @if(enable_review())
            <div class="row">
                <div class="col-12 col-sm-8 col-md-8 col-lg-9 col-content">
                    @include('frontend.car.review')
                </div>
            </div>
        @endif
    </div>
    <div class="mobile-book-action">
        <div class="container">
            <div class="action-inner">
                <div class="action-price-wrapper">
                    <span class="price">{{ convert_price($post->base_price) }}</span>
                    <span class="unit">/{{get_car_unit()}}</span>
                </div>
                <a class="btn btn-primary action-button" id="mobile-check-availability">{{__('Check Availability')}}</a>
            </div>
        </div>
    </div>
</div>
@include('frontend.components.footer')
