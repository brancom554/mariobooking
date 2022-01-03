@php
    global $post;
    $post_content = $post->post_content;
    $post_content = maybe_unserialize($post_content);
@endphp
@include('frontend.components.header')
<div class="page-archive pb-4">
    @if(!empty($post_content))
        @foreach($post_content as $key => $val)
            <div id="{{$key}}">
                {!! $val['class']::inst()->getHTML($val) !!}
            </div>
        @endforeach
    @endif
</div>
@include('frontend.components.footer')
