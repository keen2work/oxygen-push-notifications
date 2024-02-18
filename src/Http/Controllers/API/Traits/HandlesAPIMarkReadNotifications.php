<?php


namespace EMedia\OxygenPushNotifications\Http\Controllers\API\Traits;


use App\Entities\PushNotifications\PushNotification;
use EMedia\Api\Docs\APICall;
use EMedia\Api\Docs\Param;
use EMedia\Api\Domain\Postman\PostmanVar;
use Illuminate\Http\Request;

trait HandlesAPIMarkReadNotifications
{

	protected $pushNotificationsRepo;

	/**
	 *
	 * Mark a notification as read
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function markRead(Request $request, $uuid)
	{
		document(function () {
			return (new APICall)
				->setGroup('Notifications')
				->setName('Mark a Notification as Read')
				->setApiKeyHeader()
				->setParams(array_merge($this->getDeviceIdHeaderParams(), [
					(new Param('uuid', 'String', 'Notification Uuid - Sent by the server for the notification'))
						->setDefaultValue('uuid-1234-1234-1234')
						->setLocation(Param::LOCATION_PATH),
				]))
				->setSuccessExample('{
									"payload": {
										"uuid": "1234-1234-1234-SEED",
										"title": "SENT_TOPIC_IOS_SEED_NOTIFICATION_2",
										"message": "Nostrum velit beatae ad accusamus.",
										"badge_count": null,
										"data": [],
										"is_read": true
									},
									"message": "",
									"results": true
								}')
				->setSuccessObject(PushNotification::class);
		});

		$this->validateDeviceIdHeaders();

		$device = $this->devicesRepo->getByIDAndType($request->header('x-device-id'), $request->header('x-device-type'));

		if (!$device) {
			return response()->apiError("Can't find a device with given ID and type.");
		}

		/** @var PushNotification $notification */
		$notification = $this->pushNotificationsRepo->findByUuid($request->uuid);

		// get the notification
		if (!$notification) {
			return response()->apiError('Invalid UUID');
		}

		// if it's individual/device - mark as read
		if ($notification->isUserOrDeviceNotification()) {
			$this->pushNotificationsRepo->markAsRead($notification);
		} else {
			// if it's a topic - mark as read on the read table
			try {
				$notification->status()->sync([
					$device->id => [
						'read_at' => now()
					]
				]);
			} catch (\Exception $ex) {
				report($ex);
				return response()->apiError($ex->getMessage());
			}

			// update the value for the response
			$notification->read_at = now();
		}

		// return the response
		return response()->apiSuccess($notification);
	}

}