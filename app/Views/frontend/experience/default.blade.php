@include('frontend.components.header')
<?php
enqueue_script('scroll-magic-js');
global $post;
$booking_form = $post->booking_form;
?>
<div class="single-page single-experience pb-5">
    <div class="container">
    @include('frontend.components.service_breadcrumb', ['post_type' => 'experience'])
    <!-- Gallery -->
        <?php
        $gallery = $post->gallery;
        $thumbnail_id = get_experience_thumbnail_id($post->post_id);
        $thumbnailUrl = get_attachment_url($thumbnail_id, 'full');
        ?>
        <div class="hh-gallery hh-grid-gallery">
            <div class="controls">
                <a href="javascript: void(0);" class="view-gallery item-link"><span>{{__('View Photos')}}</span> <i
                        class="ti-gallery"></i> </a>
            </div>
            <?php
            if ( !empty($gallery) ) {
            enqueue_script('light-gallery-js');
            enqueue_style('light-gallery-css');

            $gallery = explode(',', $gallery);
            $data = [];
            $i = 0;
            foreach ( $gallery as $key => $val ) {
            $thumbnail = get_attachment_url($val, [500, 750]);
            if(in_array($i, [0, 1, 4])){
            ?>
            <div class="item">
                <div class="item-inner">
                    <img src="{{$thumbnail}}" alt="{{ get_attachment_alt($val) }}">
                </div>
            </div>
            <?php
            }elseif($i == 2 || $i == 3){
            if($i == 2){
            ?>
            <div class="item item-small">
                <div class="item-outer">
                    <div class="item-inner">
                        <img src="{{$thumbnail}}" alt="{{ get_attachment_alt($val) }}">
                    </div>
                </div>
                <div class="space"></div>
                <?php
                }elseif($i == 3){
                ?>
                <div class="item-outer">
                    <div class="item-inner">
                        <img src="{{$thumbnail}}" alt="{{ get_attachment_alt($val) }}">
                    </div>
                </div>
            </div>
            <?php
            }
            }
            $url = get_attachment_url($val);
            if (!empty($url)) {
                $data[] = [
                    'src' => $url
                ];
            }

            $i++;
            }
            if (!empty($data)) {
                $data = base64_encode(json_encode($data));
                echo '<div class="data-gallery" data-gallery="' . esc_attr($data) . '"></div>';
            }
            }
            ?>
        </div>
        <div class="row">
            <div class="col-12 col-sm-8 col-md-8 col-lg-9 col-content">
                <div class="row">
                    <div class="col-12 col-xl-4">
                        <h1 class="title">
                            @if($post->is_featured == 'on')
                                <span class="is-featured featured-icon"
                                      title="{{__('Featured')}}">{!! balanceTags(get_icon('001_diamond')) !!}</span>
                            @endif
                            {{ get_translate($post->post_title) }}
                        </h1>
                        @if ($post->location_address)
                            <p class="location mb-1">
                                <i class="ti-location-pin"></i>
                                {{ get_translate($post->location_address) }}
                            </p>
                        @endif
                        <div class="review-summary">
                            <?php
                            $rate = $post->review_count;
                            ?>
                            <div class="count-reviews">
                                {{ number_format(round((float)$post->rating, 1), 1) }} <i class="fas fa-star"></i> <span
                                    class="count">{{ _n(__('(%s review)'), __('(%s reviews)'), $rate) }}</span>
                            </div>

                        </div>
                    </div>
                    <div class="col-12 col-xl-8">
                        <div class="tour-featured">
                            <div class="row">
                                <div class="col-6 col-md-6 col-lg-6 col-xl-3">
                                    <div class="item mb-2">
                                        {!! get_icon('009_sunbed', '#4a4a4a') !!}
                                        <div class="title">{{__('Duration')}}</div>
                                        <div class="desc">{{ get_translate($post->durations) }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-6 col-lg-6 col-xl-3">
                                    <div class="item mb-2">
                                        {!! get_icon('ico_child', '#4a4a4a') !!}
                                        <div class="title">{{__('Group size')}}</div>
                                        <?php
                                        $max_people = (float)$post->number_of_guest;
                                        ?>
                                        @if($max_people == -1)
                                            <div class="desc">{{ __('Unlimited')}}</div>
                                        @else
                                            <div
                                                class="desc">{{ _n(__('%s person'), __('Up to %s people'), $max_people)}}</div>
                                        @endif
                                    </div>
                                </div>
                                @if($post->experience_type)
                                    @php
                                        $tour_type = get_term_by('id', $post->experience_type);
                                    @endphp
                                    @if(!is_null($tour_type))
                                        <div class="col-6 col-md-6 col-lg-6 col-xl-3">
                                            <div class="item mb-2">
                                                {!! get_icon('001_tour', '#4a4a4a') !!}
                                                <div class="title">{{__('Type')}}</div>
                                                <div class="desc">{{ get_translate($tour_type->term_title) }}</div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                                <div class="col-6 col-md-6 col-lg-6 col-xl-3">
                                    <div class="item mb-2">
                                        {!! get_icon('001_language', '#4a4a4a') !!}
                                        <div class="title">{{__('Language')}}</div>
                                        <?php

                                        $language = $post->languages;
                                        $language_return = '';
                                        ?>
                                        @if(empty($language))
                                            <div class="desc">{{ __('Not set') }}</div>
                                        @else
                                            <?php
                                            $language = explode(',', $language);
                                            foreach ($language as $lang) {
                                                $term = get_term_by('id', $lang);
                                                if (!is_null($term)) {
                                                    $language_return .= get_translate($term->term_title) . ', ';
                                                }
                                            }
                                            if (!empty($language_return)) {
                                                $language_return = substr($language_return, 0, -2);
                                            }
                                            ?>
                                        @endif
                                        <div class="desc">
                                            @if(!empty($language_return))
                                                {{$language_return}}
                                            @else
                                                {{ __('Not set') }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-12 col-md-4">
                        <h2 class="heading mt-0 mb-2">{{__('What you will do')}}</h2>
                    </div>
                    <div class="col-12 col-md-8">
                        {!! balanceTags(get_translate($post->post_content)) !!}
                    </div>
                </div>

                @php
                    $author = get_user_by_id($post->author);
                    $description = $author->description;
                @endphp
                <div class="row mt-4">
                    <div class="col-12 col-md-4">
                        <h2 class="heading mt-0 mb-2">{{__('Your host')}}</h2>
                    </div>
                    <div class="col-12 col-md-8">
                        <img src="{{ get_user_avatar($post->author, [64, 64]) }}" alt="{{ __('User Avatar') }}"
                             class="avatar rounded-circle">
                        <h2 class="h4"> {{get_username($author->getUserId())}}</h2>
                        @if(!empty($description))
                            <div class="hr mt-0"></div>
                            <div class="clearfix">
                                {!! balanceTags(nl2br($description)) !!}
                            </div>
                        @endif
                    </div>
                </div>
                @if($post->itinerary)
                    <div class="row mt-4">
                        <div class="col-12 col-md-4">
                            <h2 class="heading mt-0 mb-2">{{__('Your Itinerary')}}</h2>
                        </div>
                        <div class="col-12 col-md-8">
                            <div class="itinerary-tour">
                                @foreach($post->itinerary as $item)
                                    <div class="item">
                                        <div class="d-block d-sm-flex align-items-center">
                                            <div class="sub-title">{{ get_translate($item['sub_title']) }}</div>
                                            <h2 class=" title">{{ get_translate($item['title']) }}</h2>
                                        </div>
                                        <div class="desc">
                                            @if($item['image'])
                                                <?php
                                                $image_url = get_attachment_url($item['image']);
                                                $image_alt = get_attachment_alt($item['image']);
                                                ?>
                                                <img src="{{$image_url}}" class="img-fluid" alt="{{$image_alt}}">
                                            @endif
                                            {!! balanceTags(get_translate($item['description'])) !!}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <?php
                $inclusions = $post->inclusions;
                ?>
                <div class="row mt-4">
                    <div class="col-12 col-md-4">
                        <h2 class="heading heading-line  mt-0 mb-2">{{__('Inclusions')}}</h2>
                    </div>
                    <div class="col-12 col-md-8">
                        <?php
                        if ($inclusions) {
                        $inclusions = explode(',', $inclusions);
                        ?>
                        <div class="inclusions">
                            <div class="row">
                                <?php
                                foreach ($inclusions as $item) {
                                $term = get_term_by('id', $item);
                                ?>
                                @if(!is_null($term ))
                                    <div class="col-6">
                                        <div class="item">
                                            <div class="label">{{ get_translate($term->term_title) }}</div>
                                            @if($term->term_description)
                                                <div
                                                    class="desc">{!! balanceTags(get_translate($term->term_description)) !!}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                        } else {
                        ?>
                        <p>{{__('Not set')}}</p>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
                $exclusions = $post->exclusions;
                ?>
                <div class="row mt-4">
                    <div class="col-12 col-md-4">
                        <h2 class="heading heading-line mt-0 mb-2">{{__('Exclusions')}}</h2>
                    </div>
                    <div class="col-12 col-md-8">
                        <?php
                        if ($exclusions) {
                        $exclusions = explode(',', $exclusions);
                        ?>
                        <div class="inclusions">
                            <div class="row">
                                <?php
                                foreach ($exclusions as $item) {
                                $term = get_term_by('id', $item);
                                ?>
                                @if(!is_null($term))
                                    <div class="col-6">
                                        <div class="item">
                                            <div class="label">{{ get_translate($term->term_title) }}</div>
                                            @if($term->term_description)
                                                <div
                                                    class="desc">{!! balanceTags(get_translate($term->term_description)) !!}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                        } else {
                        ?>
                        <p>{{__('Not set')}}</p>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                @php
                    $enableCancellation = $post->enable_cancellation;
                    $cancelBeforeDay = (int)$post->cancel_before;
                    $cancellationDetail = $post->cancellation_detail;
                @endphp
                @if ($enableCancellation == 'on')
                    <div class="row mt-4">
                        <div class="col-12 col-md-4">
                            <h2 class="heading mt-0 mb-2">{{__('Policies')}}</h2>
                        </div>
                        <div class="col-12 col-md-8">
                            <div class="item">
                                <span class="font-weight-bold">{{__('Cancellation:')}}</span>
                                <span class="ml-2 small-info bg-success">{{__('enable')}}</span>
                                <span
                                    class="d-inline-block ml-1">{{ sprintf(__('before %s day(s)'), $cancelBeforeDay) }}</span>
                            </div>
                            @if (get_translate($cancellationDetail))
                                <div class="w-100 mt-1">{!! balanceTags(get_translate($cancellationDetail)) !!}</div>
                            @endif
                        </div>
                    </div>
                @endif
                @if($post->video)
                    <div class="row mt-4">
                        <div class="col-12 col-md-4">
                            <h2 class="heading mt-0 mb-2">{{__('Video')}}</h2>
                        </div>
                        <div class="col-12 col-md-8">
                            <div class="video-wrapper">
                                {!! balanceTags(get_video_embed_url(get_translate($post->video))) !!}
                            </div>
                        </div>
                    </div>
                @endif
                <div class="row mt-4">
                    <div class="col-12 col-md-4">
                        <h2 class="heading mt-0 mb-2">{{__('On Map')}}</h2>
                    </div>
                    <div class="col-12 col-md-8">
                        @php
                            $lat = $post->location_lat;
                            $lng = $post->location_lng;
                            $zoom = $post->location_zoom;

                            enqueue_style('mapbox-gl-css');
                            enqueue_style('mapbox-gl-geocoder-css');
                            enqueue_script('mapbox-gl-js');
                            enqueue_script('mapbox-gl-geocoder-js');
                        @endphp
                        <div class="hh-mapbox-single" data-lat="{{ $lat }}" data-type="experience"
                             data-lng="{{ $lng }}" data-zoom="{{ $zoom }}"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-4 col-md-4 col-lg-3 col-sidebar">
                @php
                    enqueue_style('daterangepicker-css');
                    enqueue_script('daterangepicker-js');
                    enqueue_script('daterangepicker-lang-js');
                @endphp
                @php
                    $booking_form  = $post->booking_form;
                @endphp
                <div id="form-book-experience" class="form-book"
                     data-real-price="{{ url('get-experience-price-realtime') }}">
                    <div class="popup-booking-form-close">{!! get_icon('001_close', '#FFFFFF', '28px', '28px') !!}</div>
                    <div class="form-head">
                        <div class="price-wrapper">
                            <span class="prefix">{{__('From')}}</span>
                            <span class="price">{{ convert_price($post->base_price) }}</span>
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
                                        <form class="form-action" action="{{ url('add-to-cart-experience') }}"
                                              data-get-total="{{url('get-total-price-experience')}}"
                                              method="post"
                                              data-loading-from=".form-body">
                                            <?php if ($post->booking_type == 'package') {
                                            $packages = $post->tour_packages;
                                            if (!empty($packages)) {
                                            ?>

                                            <div class="form-group">
                                                <label>{{__('Packages')}}</label>
                                                <?php
                                                foreach($packages as $key => $package){
                                                $package_price = $sale_price = $package['price'];
                                                if (!empty($package['sale_price']) && (float)$package['sale_price'] >= 0) {
                                                    $sale_price = $package['sale_price'];
                                                }
                                                $price_html = '<span class="price-html">';
                                                if ($sale_price < $package_price) {
                                                    $price_html .= '<span class="base-price has-sale">' . convert_price($package_price) . '</span>';
                                                    $price_html .= '<span class="sale-price">' . convert_price($sale_price) . '</span>';
                                                } else {
                                                    $price_html .= '<span class="base-price">' . convert_price($package_price) . '</span>';
                                                }
                                                $price_html .= '</span>';
                                                ?>
                                                <div class="package-item mb-2">
                                                    <div class="radio radio-success">
                                                        <input type="radio" name="tour_package"
                                                               id="tour-package-{{$package['name']}}"
                                                               value="{{$package['name']}}">
                                                        <label
                                                            for="tour-package-{{$package['name']}}">{{ get_translate($package['title']) }}
                                                            - {!! balanceTags($price_html) !!}</label>
                                                    </div>
                                                    <div class="row align-items-center mt-2">
                                                        <div class="col item" data-toggle="tooltip" data-placement="top"
                                                             title=""
                                                             data-original-title="{{ _n(__('No. Adult: %s'), __('No. Adults: %s'), $package['num_adult']) }}">{{__('AD')}}
                                                            : <strong>{{(int) $package['num_adult']}}</strong></div>
                                                        <div class="col item" data-toggle="tooltip" data-placement="top"
                                                             title=""
                                                             data-original-title="{{ _n(__('No. Child: %s'), __('No. Children: %s'), $package['num_child']) }}">{{__('CH')}}
                                                            : <strong>{{(int) $package['num_child']}}</strong></div>
                                                        <div class="col item" data-toggle="tooltip" data-placement="top"
                                                             title=""
                                                             data-original-title="{{ _n(__('No. Infant: %s'), __('No. Infants: %s'), $package['num_infant']) }}">{{__('IN')}}
                                                            : <strong>{{(int) $package['num_infant']}}</strong></div>
                                                    </div>
                                                    @if(!empty($package['detail']))
                                                        <div class="package-description">
                                                            {!! nl2br(balanceTags(get_translate($package['detail']))) !!}
                                                        </div>
                                                    @endif
                                                </div>
                                                <?php
                                                }
                                                }
                                                ?>
                                            </div>
                                            <?php
                                            } ?>
                                            <div class="form-group">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <label class="mb-0">{{ __('Date') }} <span
                                                            class="label-date-render"
                                                            data-date-format="{{ hh_date_format_moment() }}"></span></label>
                                                    <a href="#date-collapse" class="right" aria-expanded="true"
                                                       data-toggle="collapse">{!! get_icon('002_download_1', '#2a2a2a','15px') !!}</a>
                                                </div>
                                                <div id="date-collapse" class="show">
                                                    <div class="date-wrapper {{ $post->booking_type }}"
                                                         data-date-format="{{ hh_date_format_moment() }}"
                                                         data-action="{{ url('get-experience-availability-single') }}"
                                                         data-action-time="{{ url('get-experience-date-time') }}"
                                                         data-action-guest="{{ url('get-experience-guest') }}">
                                                        <input type="text"
                                                               class="input-hidden check-in-out-field {{ $post->booking_type }}"
                                                               name="checkInOut"
                                                               data-experience-id="{{ $post->post_id }}"
                                                               data-experience-encrypt="{{ hh_encrypt($post->post_id) }}">
                                                        <input type="text" class="input-hidden check-in-field"
                                                               name="checkIn">
                                                        <input type="text" class="input-hidden check-out-field"
                                                               name="checkOut">
                                                    </div>
                                                </div>
                                            </div>
                                            @if($post->booking_type == 'date_time')
                                                <div class="date-time-render d-none"></div>
                                            @endif
                                            <?php
                                            if($post->booking_type != 'package'){
                                            $max_guest = (int)$post->number_of_guest;
                                            ?>
                                            <div class="form-group">
                                                <?php
                                                $price_categories = $post->price_categories;
                                                ?>
                                                <label>{{__('Guests')}}</label>
                                                <div class="guest-group">
                                                    <button type="button" class="btn btn-light dropdown-toggle"
                                                            data-toggle="dropdown"
                                                            data-text-guest="{{__('Guest')}}"
                                                            data-text-guests="{{__('Guests')}}"
                                                            data-text-infant="{{__('Infant')}}"
                                                            data-text-infants="{{__('Infants')}}"
                                                            aria-haspopup="true" aria-expanded="false">
                                                        &nbsp;
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        @if(in_array('enable_adults', $price_categories))
                                                            <div class="group">
                                                                <span class="pull-left">{{__('Adults')}}</span>
                                                                <div class="control-item">
                                                                    <i class="decrease ti-minus"></i>
                                                                    <input type="number" min="1" step="1"
                                                                           max="{{ $max_guest }}"
                                                                           name="num_adults"
                                                                           value="1">
                                                                    <i class="increase ti-plus"></i>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <input type="hidden" name="num_adults" value="0">
                                                        @endif
                                                        @if(in_array('enable_children', $price_categories))
                                                            <div class="group">
                                                                <span class="pull-left">{{__('Children')}}</span>
                                                                <div class="control-item">
                                                                    <i class="decrease ti-minus"></i>
                                                                    <input type="number" min="0" step="1"
                                                                           max="{{ $max_guest }}"
                                                                           name="num_children"
                                                                           value="0">
                                                                    <i class="increase ti-plus"></i>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <input type="hidden" name="num_children" value="0">
                                                        @endif
                                                        @if(in_array('enable_infants', $price_categories))
                                                            <div class="group">
                                                                <span class="pull-left">{{__('Infants')}}</span>
                                                                <div class="control-item">
                                                                    <i class="decrease ti-minus"></i>
                                                                    <input type="number" min="0" step="1"
                                                                           max="{{ $max_guest }}"
                                                                           name="num_infants"
                                                                           value="0">
                                                                    <i class="increase ti-plus"></i>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <input type="hidden" name="num_infants" value="0">
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } ?>
                                            <div class="form-group">
                                                @php
                                                    $requiredExtra = $post->required_extra;
                                                @endphp
                                                @if ($requiredExtra)
                                                    <div class="extra-services">
                                                        <label class="d-block mb-2">
                                                            {{__('Extra')}}
                                                            <span
                                                                class="text-danger f12">{{__('(required)')}}</span>
                                                            <a href="#extra-collapse" class="right"
                                                               data-toggle="collapse">{!! get_icon('002_download_1', '#2a2a2a','15px') !!}</a>
                                                        </label>
                                                        <div id="extra-collapse" class="collapse show">
                                                            @foreach ($requiredExtra as $ex)
                                                                <div class="extra-item d-flex">
                                                                    <span
                                                                        class="name">{{ get_translate($ex['name']) }}</span>
                                                                    <span
                                                                        class="price ml-auto">{{ convert_price($ex['price']) }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                                @php
                                                    $extra = $post->not_required_extra;
                                                @endphp
                                                @if ($extra)
                                                    <div class="extra-services">
                                                        <label class="d-block mb-2">
                                                            <span>{{__('Extra (optional)')}}</span>
                                                            <a href="#extra-not-required-collapse" class="right"
                                                               data-toggle="collapse">{!! get_icon('002_download_1', '#2a2a2a','15px') !!}</a>
                                                        </label>
                                                        <div id="extra-not-required-collapse" class="collapse">
                                                            @foreach ($extra as $ex)
                                                                <div class="extra-item d-flex">
                                                                    <div class="checkbox checkbox-success">
                                                                        <input
                                                                            id="extra-service-{{ $ex['name_unique'] }}"
                                                                            class="input-extra"
                                                                            type="checkbox" name="extraServices[]"
                                                                            value="{{ $ex['name_unique'] }}">
                                                                        <label
                                                                            for="extra-service-{{ $ex['name_unique'] }}">
                                                                            <span
                                                                                class="name">{{ get_translate($ex['name']) }}</span>
                                                                        </label>
                                                                    </div>
                                                                    <span
                                                                        class="price ml-auto">{{ convert_price($ex['price']) }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="form-group form-render">
                                            </div>
                                            <div class="form-group mt-2">
                                                <input type="hidden" name="experienceID" value="{{ $post->post_id }}">
                                                <input type="hidden" name="experienceEncrypt"
                                                       value="{{ hh_encrypt($post->post_id) }}">
                                                <input type="hidden" name="bookingType"
                                                       value="{{ $post->booking_type }}">
                                                <input type="submit"
                                                       class="btn btn-primary btn-block text-uppercase"
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
                                        <form action="{{ url('experience-send-enquiry-form') }}"
                                              data-google-captcha="yes"
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
            $list_services = \App\Controllers\Services\ExperienceController::get_inst()->listOfExperiences([
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
            <h2 class="heading mt-4 mb-3">{{__('Experiences Near By')}}</h2>
            <div class="hh-list-of-services list-experience">
                <div class="row">
                    @foreach($list_services['results'] as $item)
                        <?php $item = setup_post_data($item, 'experience'); ?>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            @include('frontend.experience.loop.grid', ['item' => $item, 'show_distance' => true])
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        @if(enable_review())
            <div class="row mt-3">
                <div class="col-12 col-sm-8 col-md-8 col-lg-9 col-content">
                    @include('frontend.experience.review')
                </div>
            </div>
        @endif
    </div>
    <div class="mobile-book-action">
        <div class="container">
            <div class="action-inner">
                <div class="action-price-wrapper">
                    <span class="price">{{ convert_price($post->base_price) }}</span>
                    <span class="unit">/{{$post->unit}}</span>
                </div>
                <a class="btn btn-primary action-button" id="mobile-check-availability">{{__('Check Availability')}}</a>
            </div>
        </div>
    </div>
</div>
@include('frontend.components.footer')
