<?php


namespace EMedia\OxygenPushNotifications\Http\Controllers\API\Traits;


use EMedia\Api\Docs\APICall;
use EMedia\Api\Docs\Param;
use EMedia\Api\Docs\ParamType;
use EMedia\Api\Domain\Postman\PostmanVar;
use EMedia\Devices\Entities\Devices\Device;
use EMedia\OxygenPushNotifications\Domain\PushNotificationManager;
use Illuminate\Http\Request;

trait HandlesAPIDeviceTokenSubscriptions
{

	protected $devicesRepo;

	/**
	 *
	 * Subscribe a device to the database
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function subscribe(Request $request)
	{
		document(function () {
			return (new APICall)
				->setGroup('Notifications')
				->setName('Subscribe Device')
				->setApiKeyHeader()
				->setParams(array_merge($this->getDeviceIdHeaderParams(), [
					(new Param('x-device-push-token', Param::TYPE_STRING, 'Unique push token for the device'))
						->setLocation(Param::LOCATION_HEADER)
						->setVariable('{{x-device-push-token}}'),
				]))
				->setSuccessExample('{
					"payload": {
						"device_id": "TEST_1234",
						"device_type": "apple",
						"device_push_token": "PUSH_TEST_1234",
						"access_token": "1557201626ZkDGtOp0kKomSZiXYE14ULz0qZX5gVAmJNC",
						"access_token_expires_at": "2019-08-05 14:00:26",
						"updated_at": "2019-05-07 14:00:26",
						"created_at": "2019-05-07 14:00:26",
						"id": 5
					},
					"message": "",
					"results": true
				}')
				->setSuccessObject(Device::class);
		});

		$this->validateDeviceIdHeaders();

		$pushToken = $request->header('x-device-push-token');
		if (empty($pushToken)) {
			return response()->apiError('A valid push token is required to subscribe. Send a token as x-device-push-token.');
		}

		$deviceData = [
			'device_id' => $request->header('x-device-id'),
			'device_type' => $request->header('x-device-type'),
			'device_push_token' => $request->header('x-device-push-token'),
		];
		$device = $this->devicesRepo->createOrUpdateByIDAndType($deviceData);

		// subscribe device to topic
		try {
			PushNotificationManager::subscribeDeviceToBroadcastTopics($device, true);
		} catch (\Exception $ex) {
			report($ex);
		}

		return response()->apiSuccess($device);
	}

}