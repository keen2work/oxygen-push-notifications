<?php

namespace EMedia\OxygenPushNotifications\Entities\PushNotifications;


use Illuminate\Database\Eloquent\Model;
use Kreait\Firebase\Messaging\Notification;

interface PushNotificationInterface
{

	/**
	 *
	 * Return the Cloud Notification Object
	 *
	 * @return Notification
	 */
	public function getCloudNotification();

	/**
	 *
	 * Allow updating the sent timestamp
	 *
	 * @param null $model
	 *
	 * @return mixed
	 */
	public function touchSentTimestamp($updateTimestamp = true);

}
