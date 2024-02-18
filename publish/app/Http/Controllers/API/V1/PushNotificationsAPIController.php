<?php

namespace App\Http\Controllers\API\V1;

use App\Entities\PushNotifications\PushNotificationsRepository;
use EMedia\Devices\Entities\Devices\DevicesRepository;
use EMedia\OxygenPushNotifications\Http\Controllers\API\Traits\HandlesAPIMarkReadNotifications;
use EMedia\OxygenPushNotifications\Http\Controllers\API\Traits\HandlesAPIReturnListOfNotifications;
use EMedia\OxygenPushNotifications\Http\Controllers\API\Traits\HandlesAPIDeviceTokenSubscriptions;
use EMedia\OxygenPushNotifications\Http\Controllers\API\Traits\ValidatesDeviceHeaders;

class PushNotificationsAPIController extends \App\Http\Controllers\API\V1\APIBaseController
{
	use HandlesAPIDeviceTokenSubscriptions;
	use HandlesAPIReturnListOfNotifications;
	use HandlesAPIMarkReadNotifications;
	use ValidatesDeviceHeaders;

	/**
	 * @var PushNotificationsRepository
	 */
	protected $pushNotificationsRepo;
	/**
	 * @var DevicesRepository
	 */
	protected $devicesRepo;

	public function __construct()
	{
		$this->pushNotificationsRepo = app(PushNotificationsRepository::class);
		$this->devicesRepo = app(DevicesRepository::class);
	}

}