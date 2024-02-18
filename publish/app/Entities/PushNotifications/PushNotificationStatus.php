<?php


namespace App\Entities\PushNotifications;


use App\Entities\Devices\Device;
use Illuminate\Database\Eloquent\Model;

class PushNotificationStatus extends Model
{

	protected $table = 'push_notification_status';

	public function pushNotification()
	{
		return $this->belongsTo(PushNotification::class);
	}

	public function device()
	{
		return $this->belongsTo(Device::class);
	}

}