## About FMock Admin
A forums build with laravel.

FMock墨客社区后台管理。

## Install
 - 当执行完`fmock:install`后，会自动创建管理员账号: 默认账号 `env('APP_NAME', 'fmock')`, 默认密码 `fmock`
 - 登录地址`env(ADMIN_URL) . '/login'`
 
## Develop
 > 后台开发一切从简，安全、性能至上
 - 不使用webpack、gulp、vue等编译sass、js等，所有静态资源放到public中
 - layout已经引入bootstrap,jquery,以及公共的js,css; 需要单独的静态文件在单页面自行引入
 

## Security Vulnerabilities

If you discover a security vulnerability within FMock, please send an e-mail to huaixiu.zhen via [huaixiu.zhen@gmail.com](mailto:huaixiu.zhen@gmail.com). All security vulnerabilities will be promptly addressed.

## License

The FMock is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

