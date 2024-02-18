<?php

namespace EMedia\OxygenPushNotifications\Commands;

use EMedia\OxygenPushNotifications\Domain\PushNotificationManager;
use EMedia\OxygenPushNotifications\Domain\PushNotificationTopic;
use Illuminate\Console\Command;

class SubscribeDevicesToTopic extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'oxygen:push-notifications-subscribe-devices
    							{--topic=all_devices}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Subscribe devices to a push notifications to a topic';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
    	$topic = $this->option('topic');
    	$result = [];

    	switch ($topic) {
			case PushNotificationTopic::TOPIC_ALL_DEVICES:
				$result = PushNotificationManager::registerAllDevicesToGeneralBroadcast();
				break;
			case PushNotificationTopic::TOPIC_ANDROID_DEVICES;
				$result = PushNotificationManager::registerDevicesToAndroidBroadcast();
				break;
			case PushNotificationTopic::TOPIC_IOS_DEVICES;
				$result = PushNotificationManager::registerDevicesToIOSBroadcast();
				break;
			default:
				throw new \InvalidArgumentException("The topic `{$topic}` is not a valid topic.");
    	}

    	$this->info(json_encode($result));
	}
}
