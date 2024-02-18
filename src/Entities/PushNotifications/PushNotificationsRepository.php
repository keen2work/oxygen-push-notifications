<?php


namespace EMedia\OxygenPushNotifications\Entities\PushNotifications;


use App\Entities\BaseRepository;
use Carbon\Carbon;
use EMedia\Devices\Entities\Devices\Device;
use EMedia\OxygenPushNotifications\Domain\PushNotificationTopic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PushNotificationsRepository extends BaseRepository
{

	public function __construct(PushNotification $model)
	{
		parent::__construct($model);
	}

	/**
	 *
	 * Get an unsent push notification by ID
	 *
	 * @param $id
	 *
	 * @return \Illuminate\Database\Eloquent\Builder|Model
	 */
	public function getUnsetPushNotification($id)
	{
		$query = $this->getUnsentQuery();

		$query->where('id', $id);

		return $query->first();
	}

	/**
	 *
	 * Return all Unsent Push notification objects
	 *
	 * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
	 */
	public function getUnsentPushNotifications()
	{
		$query = $this->getUnsentQuery();

		return $query->get();
	}

	/**
	 *
	 * Build the unset push notifications query
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	protected function getUnsentQuery()
	{
		$query = PushNotification::query();

		$query->whereNull('sent_at');

		// for safety, we don't send any messages older than 1 hour from the scheduled time
		// this is to prevent any CRON errors, and the CRON to send older messages later on
		$query->where('scheduled_at', '>=', Carbon::now()->subHours(1));

		$query->where('scheduled_at', '<=', \Carbon\Carbon::now());
		// TODO: add timezone check

		// check for the recipient
		$query->where(function ($q) {
			$q->whereNotNull('topic');
			$q->orWhereNotNull('notifiable_id');
		});

		$query->orderBy('scheduled_at');

		return $query;
	}


	protected function beforeSavingModel(Request $request, $entity)
	{
		$scheduledAtTime = now();
		if ($request->filled('scheduled_at_string')) {
			try {
				$scheduledAtTime = \Carbon\Carbon::createFromFormat('m/d/Y h:i A', $request->scheduled_at_string);
			} catch (\Exception $ex) {

			}
		}

		// https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html
		// set the android channel ID
		/** @var PushNotification $entity */
		$entity->androidConfig = [
			'notification' => [
				'android_channel_id' => 'app-channel-custom-id',
				'sound' => 'default',
				'badge' => 1,
			]
		];

		$entity->apnsConfig = [
			'payload' => [
				'apns' => [
					'sound' => 'default',
					'badge' => 1,
				]
			]
		];

		// if there's no topic, but has a device_id, then send to that device
		$device = null;
		if (request()->has('device_id')) {
			$device = Device::where('id', request()->input('device_id'))->first();
			if ($device) $entity->notifiable()->associate($device);
		}

		$entity->scheduled_at = $scheduledAtTime;
		$entity->scheduled_timezone = $scheduledAtTime->timezoneName;
	}

	public function getNotificationsForNotifiableDevice(Device $notifiable, $limit = 50)
	{
		$query = PushNotification::query();

		$query->whereNotNull('sent_at');

		$query->with(['status' => function ($q) use ($notifiable) {
			return $q->wherePivot('device_id', '=', $notifiable->id);
		}]);

		$broadcastTopics = [PushNotificationTopic::TOPIC_ALL_DEVICES];

		if ($notifiable->device_type === 'apple') {
			$broadcastTopics[] = PushNotificationTopic::TOPIC_IOS_DEVICES;
		} elseif ($notifiable->device_type === 'android') {
			$broadcastTopics[] = PushNotificationTopic::TOPIC_ANDROID_DEVICES;
		}

		// match by either the device or the topic
		$query->where(function ($q) use ($broadcastTopics, $notifiable) {
			$q->where(function ($q1) use ($notifiable) {
				$q1->where('notifiable_type', get_class($notifiable));
				$q1->where('notifiable_id', $notifiable->id);
			})->orWhere(function ($q2) use ($broadcastTopics) {
				$q2->whereIn('topic', $broadcastTopics);
			});
		});

		$query->orderBy('sent_at', 'desc');

		return $query->paginate($limit);
	}

	public function markAsRead(Model $model)
	{
		$model->read_at = now();
		$model->save();

		return $model;
	}

}
