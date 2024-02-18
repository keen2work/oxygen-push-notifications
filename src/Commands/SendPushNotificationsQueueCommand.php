<?php

namespace EMedia\OxygenPushNotifications\Commands;

use EMedia\OxygenPushNotifications\Entities\PushNotifications\PushNotificationsRepository;
use EMedia\OxygenPushNotifications\Domain\PushNotificationManager;
use Illuminate\Console\Command;

class SendPushNotificationsQueueCommand extends Command
{

	/* @var PushNotificationsRepository */
	protected $pushNotificationsRepo;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'oxygen:push-notifications-send {--id=}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Process existing push notifications queue and send notifications';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->pushNotificationsRepo = app(PushNotificationsRepository::class);
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 * @throws \EMedia\OxygenPushNotifications\Exceptions\UnknownRecepientException
	 */
	public function handle()
	{
		$id = $this->option('id');

		if ($id) {
			$pushNotification = $this->pushNotificationsRepo->getUnsetPushNotification($id);

			if ($pushNotification) {
				PushNotificationManager::sendStoredPushNotification($pushNotification);
				$this->info('Push notification processed.');
			} else {
				$this->info('No valid push notifications found.');
			}
		} else {
			// get all pending notifications
			$pushNotifications = $this->pushNotificationsRepo->getUnsentPushNotifications();

			// send one at a time
			foreach ($pushNotifications as $pushNotification) {
				PushNotificationManager::sendStoredPushNotification($pushNotification);
				sleep(2);	// increase this if you get throttled, or limit the query above
			}

			$this->info("Processed " . $pushNotifications->count() . ' notifications.');
		}
	}
}
