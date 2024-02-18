<?php


namespace EMedia\OxygenPushNotifications\Http\Controllers\API\Traits;


use App\Entities\PushNotifications\PushNotification;
use EMedia\Api\Docs\APICall;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait HandlesAPIReturnListOfNotifications
{

	protected $pushNotificationsRepo;


	/**
	 *
	 * Get a list of notifications
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function index(Request $request)
	{
		document(function () {
			return (new APICall)
				->setGroup('Notifications')
				->setName('List Notifications')
				->setDescription('The `data` variable may contain additional data. It is the client\'s responsibility to validate the data and ensure the variables exist. The `unread_count` is the total unread messages to the given page. It does not calculate the sum of all unread messages for all pages. This should be sufficient for the practical use.')
				->setApiKeyHeader()
				->setParams($this->getDeviceIdHeaderParams())
				->setSuccessExample('{
    "unread_count": 6,
    "payload": [
        {
            "uuid": "5a3c3791-c5c4-4555-8ec6-18833a64d511",
            "title": "SENT_SINGLE_DEVICE_SEED_NOTIFICATION_2",
            "message": "Et rem cumque tenetur eum.",
            "badge_count": null,
            "data": null,
            "is_read": true,
            "sent_time_label": "1 hour ago"
        },
        {
            "uuid": "1234-1234-1234-SEED",
            "title": "SENT_TOPIC_IOS_SEED_NOTIFICATION_2",
            "message": "Nisi nisi ut quis consequatur quia incidunt.",
            "badge_count": null,
            "data": {
                "url": "http:\/\/www.google.com"
            },
            "is_read": true,
            "sent_time_label": "1 hour ago"
        },
        {
            "uuid": "d486e47b-5c66-4a5f-b1af-908b168160a3",
            "title": "SENT_SINGLE_DEVICE_Sint odit sed aliquid et veritatis nisi aut modi.",
            "message": "In voluptatem nesciunt quam asperiores exercitationem.",
            "badge_count": null,
            "data": null,
            "is_read": false,
            "sent_time_label": "2 hours ago"
        }
    ],
    "paginator": {
        "current_page": 1,
        "first_page_url": "http:\/\/bendix.devv\/api\/v1\/notifications?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http:\/\/bendix.devv\/api\/v1\/notifications?page=1",
        "next_page_url": null,
        "path": "http:\/\/bendix.devv\/api\/v1\/notifications",
        "per_page": 50,
        "prev_page_url": null,
        "to": 8,
        "total": 8
    },
    "message": "",
    "result": true
}')
				->setSuccessPaginatedObject(PushNotification::class);
		});

		$this->validateDeviceIdHeaders();

		$device = $this->devicesRepo->getByIDAndType($request->header('x-device-id'), $request->header('x-device-type'));

		if (!$device) {
			return response()->apiError("Can't find a device with given ID and type.");
		}

		// build the notifications
		$notifications = $this->pushNotificationsRepo->getNotificationsForNotifiableDevice($device);
		$unreadCount = 0;

		try {
			$notificationsResponseItems = new Collection();
			foreach ($notifications as $notification) {
				// go through the status objects (i.e. devices in this case)
				if ($notification->status->count()) {
					$devices = $notification->status;
					foreach ($devices as $device) {
						// if a status is marked as 'read', mark th notification as 'read'
						if ($device->pivot->read_at !== null) {
							$notification->read_at = $device->pivot->read_at;
							$notification->is_read = true;
							continue;
						}
					}
				}
				$notificationsResponseItems->push($notification);
			}
		} catch (\Exception $ex) {
			return response()->apiError($ex->getMessage());
		}

		$unreadItems = $notificationsResponseItems->filter(function ($item) {
			return $item->is_read === false;
		});

		$paginator = new \Illuminate\Pagination\LengthAwarePaginator(
			$notificationsResponseItems,	// collection of all items
			$notifications->total(), // total items
			$notifications->perPage(),  // items per page
			\Illuminate\Pagination\Paginator::resolveCurrentPage(), //resolve the path
			[
				'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), // options
				'unread_count' => $unreadItems->count(),
			]
		);

		return response()->apiSuccessPaginated($paginator, '', [
			'unread_count' => $unreadItems->count(),
		]);
	}

}