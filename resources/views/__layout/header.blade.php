<div id="app">
    <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
            <div class="navbar-header">
                <!-- Branding Image -->
                <a href="{{ url('/dashboard') }}" style="float: left;padding-right: 15px;height: 50px;">
                    <img src="{{asset('static/image/logo.png')}}" alt="" style="height: inherit;"/>
                </a>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side Of Navbar -->
                <ul class="nav navbar-nav" id="app-left-nav">
                    <li>
                        <a href="/users">
                            @lang('admin.users')@lang('admin.manage')
                        </a>
                    </li>
                    <li>
                        <a href="/posts">
                            @lang('admin.posts')@lang('admin.manage')
                        </a>
                    </li>
                    <li>
                        <a href="/videos">
                            @lang('admin.videos')@lang('admin.manage')
                        </a>
                    </li>
                    <li>
                        <a href="/orders">
                            @lang('admin.orders')@lang('admin.manage')
                        </a>
                    </li>
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="nav navbar-nav navbar-right">
                    <!-- Authentication Links -->
                    @if (Auth::guard('admin')->guest())
                        <li><a href="{{ url('/login') }}">@lang('admin.login')</a></li>
                    @else
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                {{ Auth::guard('admin')->user()->name }} <span class="caret"></span>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <a href="/password">
                                        @lang('admin.reset') @lang('admin.password')
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ url('/logout') }}"
                                        onclick="event.preventDefault();
                                        document.getElementById('logout-form').submit();">
                                        @lang('admin.logout')
                                    </a>

                                    <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
</div>