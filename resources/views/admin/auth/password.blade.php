@extends('__layout.layout')

@section('title', 'Reset Pwd')

@section('css')
    <link rel="stylesheet" href="{{asset('css/admin/auth/login.css')}}">
@endsection

@section('content')
    <div class="content">

        <div class="col-lg-6">
            <form id="js-login-from" class="form-horizontal" role="form" method="POST">
                <div class="form-group">
                    <label for="password">@lang('admin.password')</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password">
                </div>

                <div class="form-group">
                    <label for="password">@lang('admin.confirm')@lang('admin.password')</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Password Confirmation">
                </div>

                <div class="form-group">
                    <button type="button" class="btn btn-default" id="js-update-password">@lang('admin.submit')</button>
                </div>
            </form>
        </div>

    </div>
@endsection

@section('js')
    <script src="{{asset('js/admin/auth/password.js')}}"></script>
@endsection