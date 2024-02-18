<?php


namespace EMedia\OxygenPushNotifications\Http\Controllers\API\Traits;


use EMedia\Api\Docs\Param;
use Illuminate\Support\Facades\Validator;

trait ValidatesDeviceHeaders
{

	protected function getDeviceIdHeaderParams()
	{
		return [
			(new Param('x-device-id', Param::TYPE_STRING, 'Unique ID of the device'))
				->setLocation(Param::LOCATION_HEADER)
				->setVariable('{{x-device-id}}'),

			(new Param('x-device-type', Param::TYPE_STRING, 'Type of the device `apple` or `android`'))
				->setLocation(Param::LOCATION_HEADER)
				->setVariable('{{x-device-type}}')
				->setDefaultValue('apple')
		];
	}

	protected function validateDeviceIdHeaders()
	{
		if (empty(request()->header('x-device-id'))) {
			throw new \Illuminate\Http\Exceptions\HttpResponseException(
				response()->apiError('x-device-id not found in header', [], \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY));
		}

		if (empty(request()->header('x-device-type'))) {
			throw new \Illuminate\Http\Exceptions\HttpResponseException(
				response()->apiError('x-device-type not found in header', [], \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY));
		}
	}

}