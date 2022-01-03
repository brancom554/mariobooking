@include('dashboard.components.header')
@php
    enqueue_script('confirm-js');
    enqueue_style('confirm-css');
@endphp
<div id="wrapper">
    @include('dashboard.components.top-bar')
    @include('dashboard.components.nav')
    <div class="content-page">
        <div class="content">
            @include('dashboard.components.breadcrumb', ['heading' => __('Partner Registration')])
            {{--Start Content--}}
            <div class="card-box">
                <div class="header-area d-flex align-items-center">
                    <h4 class="header-title mb-0">{{__('All Users')}}</h4>
                    <form class="form-inline right d-none d-sm-block" method="get">
                        <div class="form-group">
                            @php
                                $search = request()->get('_s');
                                $order = request()->get('order', 'desc');
                            @endphp
                            <input type="text" class="form-control" name="_s"
                                   value="{{ $search }}"
                                   placeholder="{{__('Search by id, email')}}">
                        </div>
                        <button type="submit" class="btn btn-default"><i class="ti-search"></i></button>
                    </form>
                </div>
                @php
                    enqueue_style('datatables-css');
                    enqueue_script('datatables-js');
                    enqueue_script('pdfmake-js');
                    enqueue_script('vfs-fonts-js');
                @endphp
                @php
                    $tableColumns = [0, 1, 2, 3, 4];
                @endphp
                <table class="table table-large mb-0 dt-responsive nowrap w-100" data-plugin="datatable"
                       data-paging="false"
                       data-export="on"
                       data-pdf-name="{{__('Export to PDF')}}"
                       data-csv-name="{{__('Export to CSV')}}"
                       data-cols="{{ base64_encode(json_encode($tableColumns)) }}"
                       data-ordering="false">
                    <thead>
                    <tr>
                        @php
                            $_order = ($order == 'asc') ? 'desc' : 'asc';
                            $url = add_query_arg([
                                'orderby' => 'id',
                                'order' => $_order
                            ]);
                        @endphp
                        <th data-priority="1">
                            <a href="{{ $url }}" class="order">
                                {{__('ID')}}
                                @if($order == 'asc')
                                    <i class="icon-arrow-down"></i>
                                @else
                                    <i class="icon-arrow-up"></i>
                                @endif
                                <span class="exp d-none">{{__('ID')}}</span>
                            </a>
                        </th>
                        <th data-priority="3">
                            {{__('Name')}}
                        </th>
                        <th data-priority="4">{{__('Email')}}</th>
                        <th data-priority="4">{{__('Mobile')}}</th>
                        <th data-priority="5" class="text-center">{{__('Registered At')}}</th>
                        <th data-priority="-1" class="text-center">{{__('Actions')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if ($allUsers['total'])
                        @foreach ($allUsers['results'] as $item)
                            @php
                                $userID = $item->id;
                            @endphp
                            <tr>
                                <td class="align-middle">
                                    #{{ $userID }}
                                </td>
                                <td class="align-middle">
                                    @if($item->first_name || $item->last_name)
                                        {{ $item->first_name }} {{ $item->last_name }}
                                    @else
                                        {{ $item->email }}
                                    @endif
                                </td>
                                <td class="align-middle">
                                    {{ $item->email }}
                                </td>
                                <td class="align-middle">
                                    {{ $item->mobile }}
                                </td>
                                <td class="align-middle text-center">
                                    {{ date(hh_date_format(), strtotime($item->created_at)) }}
                                </td>
                                <td class="align-middle text-center">
                                    <div class="dropdown dropleft">
                                        <a href="javascript: void(0)" class="dropdown-toggle table-action-link"
                                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i
                                                class="ti-settings"></i></a>
                                        <div class="dropdown-menu">
                                            @php
                                                $params = [
                                                    'userID' => $userID,
                                                    'userEncrypt' => hh_encrypt($userID)
                                                ];
                                            @endphp
                                            <a href="javascript:void(0)" class="dropdown-item" data-toggle="modal"
                                               data-params="{{ base64_encode(json_encode($params)) }}"
                                               data-target="#hh-view-partner-modal">{{__('Detail')}}</a>
                                            <a class="dropdown-item hh-link-action hh-link-change-status-home"
                                               data-action="{{ dashboard_url('approve-partner') }}"
                                               data-parent="tr"
                                               data-params="{{ base64_encode(json_encode($params)) }}"
                                               href="javascript: void(0)">{{__('Approved')}}</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7">
                                <h4 class="mt-3 text-center">{{__('No users yet.')}}</h4>
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
                <div class="clearfix mt-2">
                    {{ dashboard_pagination(['total' => $allUsers['total']]) }}
                </div>
            </div>
            <div id="hh-view-partner-modal" class="modal fade hh-get-modal-content" tabindex="-1" role="dialog"
                 aria-hidden="true"
                 data-url="{{ dashboard_url('get-partner-info') }}">
                <div class="modal-dialog">
                    <div class="modal-content">
                        @include('common.loading')
                        <div class="modal-header">
                            <h4 class="modal-title">{{__('Partner Information')}}</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×
                            </button>
                        </div>
                        <div class="modal-body">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light waves-effect"
                                    data-dismiss="modal">{{__('Close')}}</button>
                        </div>
                    </div>
                </div>
            </div><!-- /.modal -->
            {{--End content--}}
            @include('dashboard.components.footer-content')
        </div>
    </div>
</div>
@include('dashboard.components.footer')
