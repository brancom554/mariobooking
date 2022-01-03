@php
    if(!is_user_logged_in() || !is_admin()){
        return;
    }
@endphp
<!-- Right Sidebar -->
<div class="right-bar">
    <div class="rightbar-title">
        <a href="javascript:void(0);" class="right-bar-toggle float-right">
            <i class="dripicons-cross noti-icon"></i>
        </a>
        <h5 class="m-0 text-white">{{__('Settings')}}</h5>
    </div>
    <div class="slimscroll-menu">
        <!-- User box -->
        @php
            $userdata = get_current_user_data();
        @endphp
        <div class="user-box">
            <div class="user-img">
                <img src="{{ get_user_avatar($userdata->getUserId(), [150, 150]) }}" alt="user-img"
                     title="{{ get_username($userdata->getUserId()) }}"
                     class="rounded-circle img-fluid">
            </div>
            <h5><a target="_blank" href="{{ dashboard_url('profile') }}">{{ get_username($userdata->getUserId()) }}</a>
            </h5>
            <p class="text-muted mb-0">
                <small>{{ $userdata->email }}</small>
            </p>
        </div>
        <!-- Settings -->
        <hr class="mt-0"/>
        <h5 class="pl-3">{{__('Quick Settings')}}</h5>
        <hr class="mb-0"/>
        <div class="p-3">
            <form action="{{ dashboard_url('save-quick-settings') }}"
                  class="form form-xs clearfix relative form-quick-settings" method="post">
                @include('common.loading')
                @php
                    $allFields = [
                        [
                            'id' => 'main_color',
                            'field_type' => 'meta',
                            'type' => 'colorpicker'
                        ],
                        [
                            'id' => 'enable_review',
                            'field_type' => 'meta',
                            'type' => 'on_off'
                        ],
                        [
                            'id' => 'partner_commission',
                            'field_type' => 'meta',
                            'type' => 'text'
                        ],
                        [
                            'id' => 'mapbox_key',
                            'field_type' => 'meta',
                            'type' => 'text'
                        ],
                        [
                            'id' => 'google_font_key',
                            'field_type' => 'meta',
                            'type' => 'text'
                        ],
                    ];
                    $main_color = get_option('main_color', '#f8546d');
                    $enable_review = get_option('enable_review', 'off');
                    $partner_commission = get_option('partner_commission', '');
                    $mapbox_key = get_option('mapbox_key', '');
                    $google_font_key= get_option('google_font_key', '');

                    enqueue_script('switchery-js');
                    enqueue_style('switchery-css');
                    enqueue_script('bootstrap-colorpicker-js');
                    enqueue_style('bootstrap-colorpicker-css');
                @endphp
                <input type="hidden" name="all_fields" value="{{ base64_encode(json_encode($allFields)) }}">
                <div class="mb-2">
                    <label for="q_main_color">{{__('Main Color')}}</label><br/>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text color"></div>
                        </div>
                        <input type="text" id="q_main_color" data-plugin="colorpicker"
                               class="form-control"
                               name="main_color" value="{{ $main_color }}">
                    </div>
                </div>
                <div class="mb-2">
                    <label for="q_enable_review">{{__('Enable Review')}}</label><br/>
                    <input id="q_enable_review" type="checkbox" name="enable_review" value="on" data-plugin="switchery"
                           data-color="#1abc9c"
                           @if($enable_review == 'on') checked @endif>
                </div>
                <div class="mb-2">
                    <label for="q_partner_commission">{{__('Partner Commission')}}</label><br/>
                    <input type="number" class="form-control" min="0" id="q_partner_commission"
                           name="partner_commission"
                           value="{{ $partner_commission }}">
                </div>
                <div class="mb-2">
                    <label for="q_mapbox_key">{{__('Mapbox Key')}}</label><br/>
                    <input type="text" class="form-control" id="q_mapbox_key" name="mapbox_key"
                           value="{{ $mapbox_key }}">
                </div>
                <div class="mb-2">
                    <label for="q_google_font_key">{{__('Google Font Key')}}</label><br/>
                    <input type="text" class="form-control" id="q_google_font_key" name="google_font_key"
                           value="{{ $google_font_key }}">
                </div>
            </form>
        </div>
        <!-- Timeline -->
        <hr class="mt-0"/>
        <h5 class="px-3">{{__('Latest Notifications')}}</h5>
        <hr class="mb-0"/>
        <div class="pl-3 pr-3">
            <div class="inbox-widget notification-list ">
                @php
                    $allNotifications = \App\Controllers\NotificationController::get_inst()->allNotifications(['user_id' => get_current_user_id()]);
                @endphp
                @if(count($allNotifications['results']))
                    @foreach($allNotifications['results'] as $item)
                        <div class="notify-item pl-0 pr-0">
                            <div class="notify-icon notify-{{ $item->type }}">
                                @if($item->type == 'booking')
                                    <i class="fe-calendar"></i>
                                @elseif($item->type == 'global')
                                    <i class="fe-shield"></i>
                                @endif
                            </div>
                            <p class="notify-details">{!! balanceTags($item->title) !!}</p>
                            <p class="text-muted mb-0 user-msg">
                                <small>{!! balanceTags(get_time_since($item->created_at)) !!}</small>
                            </p>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted"><i>{{__('No notification yet')}}</i></p>
                @endif
            </div> <!-- end inbox-widget -->
        </div> <!-- end .p-3-->
    </div> <!-- end slimscroll-menu-->
</div>
<!-- /Right-bar -->

<!-- Right bar overlay-->
<div class="rightbar-overlay"></div>
