<?php

namespace EMedia\OxygenPushNotifications\Commands;

use EMedia\OxygenPushNotifications\Entities\PushNotifications\PushNotification;
use EMedia\Devices\Entities\Devices\Device;
use EMedia\OxygenPushNotifications\Domain\PushNotificationManager;
use Illuminate\Console\Command;

class TestPushNotificationsCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'oxygen:push-notifications-test';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send a test push notification';

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
		// message title
		$title = $this->ask('What is the message title?', 'Test Messasge');
		$message = $this->ask('What is the message body?', 'Message Body');

		$n = new PushNotification([
			'title' => $title,
			'message' => $message,
		]);
		$n->scheduled_at = now();
		$n->scheduled_timezone = now()->timezoneName;

		// ask test type
		$type = $this->choice('What is the test receiver type?', ['To User', 'To Device', 'To Topic']);

		switch ($type) {
			case 'To User':
				$this->sendToUser($n);
				break;
			case 'To Device':
				$this->sendToDevice($n);
				break;
			case 'To Topic':
				$this->sendToTopic($n);
				break;
			default:
				$this->error("Unknown type");
				return;
		}
	}

	protected function sendToUser($n)
	{
		$userId = $this->ask('What is the user ID?');
		$user = \App\Models\User::find($userId);
		if (!$user) {
			$this->error('User not found.');
			return false;
		}

		PushNotificationManager::sendPushNotificationToUser($user, $n);
	}

	protected function sendToDevice($n)
	{
		$deviceId = $this->ask('What is the device ID?');
		$device = Device::find($deviceId);
		if (!$device) {
			$this->error('Device not found.');
			return false;
		}

		$this->info('Sending to device type ' . $device->device_type);
		$this->info('Token: ' . $device->device_push_token);

		PushNotificationManager::sendPushNotificationToDevice($device, $n);
	}

	protected function sendToTopic($n)
	{
		$topicName = $this->ask('What is the topic name?', 'dev_test_topic');
		if (empty($topicName)) {
			$this->error('Topic is required');
			return false;
		}

		PushNotificationManager::sendPushNotificationToTopic($topicName, $n);
	}
}
