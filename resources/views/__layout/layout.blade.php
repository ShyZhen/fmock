<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" id="csrfToken" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <meta charset="utf-8">

    {{--  公共css  --}}
    <link rel="stylesheet" href="{{asset('css/bootstrap.css')}}">
    <link rel="stylesheet" href="{{asset('static/iconfont/iconfont.css')}}">
    <link rel="stylesheet" href="{{asset('css/common.css')}}">

    {{--占位符,单页面js--}}
    @yield('css')
</head>
<body>
<div id="main" class="main" v-cloak>

    @include('__layout.header')

    <div id="sidebar" class="sidebar">
        @section('sidebar')
        @show
    </div>

    <div id="container" class="container">
        {{--@yield('content')--}}    {{--  占位符，@section完全覆盖  --}}
        @section('content')          {{--  @section完全覆盖，如果有@parent则是追加  --}}
        @show
    </div>

    <div id="footer" class="footer">
        @section('footer')
        @show
    </div>
</div>

{{--  公共js  --}}
<script src="{{asset('js/jquery.js')}}"></script>
<script src="{{asset('js/common.js')}}"></script>

{{--  执行初始化js  --}}
<script>
    $FMock.init()
</script>

{{-- 占位符,单页面js --}}
@yield('js')

</body>
</html>