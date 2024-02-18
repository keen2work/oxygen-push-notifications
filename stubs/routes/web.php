<?php

// Start OxygenPushNotifications Routes
Route::group(['prefix' => 'manage', 'middleware' => ['auth', 'auth.acl:roles[super-admins|admins|developers]'], 'as' => 'manage.'], function()
{
	Route::resource('push-notifications', 'Manage\PushNotificationsController')
	    ->only('index', 'create', 'store', 'edit', 'update', 'destroy');
});
// End OxygenPushNotifications Routes
