<?php

Route::get('/', function () {
    return 'admin /';
});

Route::namespace('Admin')->group(function() {
    Route::get('index', 'IndexController@index');
});