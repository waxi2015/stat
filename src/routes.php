<?php

Route::group(['middleware' => 'web'], function(){
	Route::post('/wax/stat/chart', 'Waxis\Stat\StatController@chart');
});