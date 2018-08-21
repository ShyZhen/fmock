<p align="center"><img src="https://www.litblc.com/usr/themes/pinghsu/images/favicon.ico"></p>

## About Fmock
A forums build with laravel.

我也不知道要做成一个什么东西。


## Environment
 - PHP >= 7.0.0
 - Mysql
 - Redis
 - Nodejs
 - ElasticSearch = 6.2.4 && ElasticSearch-analysis-ik (one index,one type)

## Installation
 - `git clone https://github.com/ShyZhen/fmock.git`
 - `copy .env.example .env` and edit .env
 - `composer install`
 - `php artisan key:generate`
 - `php artisan storage:link`
 - `php artisan migrate`
 - `php artisan passport:install`


## Security Vulnerabilities

If you discover a security vulnerability within Fmock, please send an e-mail to huaixiu.zhen via [huaixiu.zhen@gmail.com](mailto:huaixiu.zhen@gmail.com). All security vulnerabilities will be promptly addressed.

## License

The Fmock is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
