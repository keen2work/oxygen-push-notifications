<?php

return [

	/*
	|--------------------------------------------------------------------------
	| NOTIFICATION FEATURES
	|--------------------------------------------------------------------------
	*/

	'notifications' => [
		// allow any user to register
		'send-device-push-notifications' => env('DEVICE_PUSH_NOTIFICATIONS_ENABLED', true),
	],

];
