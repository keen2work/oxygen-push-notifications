<?php


namespace EMedia\OxygenPushNotifications\Http\Controllers\Manage;


use EMedia\OxygenPushNotifications\Entities\PushNotifications\PushNotification;
use EMedia\OxygenPushNotifications\Entities\PushNotifications\PushNotificationsRepository;
use App\Http\Controllers\Controller;

use EMedia\Devices\Entities\Devices\Device;
use EMedia\Devices\Entities\Devices\DevicesRepository;
use EMedia\Formation\Builder\Formation;
use ElegantMedia\OxygenFoundation\Http\Traits\Web\CanCRUD;
use EMedia\OxygenPushNotifications\Domain\PushNotificationManager;
use EMedia\OxygenPushNotifications\Domain\PushNotificationTopic;
use EMedia\OxygenPushNotifications\Exceptions\UnknownRecepientException;
use Illuminate\Http\Request;

class PushNotificationsController extends Controller
{

	use CanCRUD;

	public function __construct(PushNotificationsRepository $repo, PushNotification $model)
	{
        $this->repo = $repo;
		$this->model = $model;

		$this->entitySingular = 'Push Notification';
		$this->entityPlural   = 'Push Notifications';

        $this->resourceEntityName = 'Push Notifications';

        $this->viewsVendorName = 'oxygen-push-notifications';

        $this->resourcePrefix = 'manage';

        $this->isDestroyAllowed = true;
	}

	protected function indexRouteName()
	{
		return 'manage.push-notifications.index';
	}

	protected function indexViewName()
	{
		return 'oxygen-push-notifications::manage.index';
	}

	protected function formViewName(): string
	{
		return 'oxygen-push-notifications::manage.form';
	}

	/**
	 *
	 * Create a new record view
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function create()
	{
		$device = null;
		if (request()->has('device_id')) {
			$devicesRepo = app(DevicesRepository::class);
			$device = $devicesRepo->find(request()->input('device_id'));
		}

		$data = [
			'pageTitle' => $this->getCreatePageTitle(),
			'entity' => $this->model,
			'form' => new Formation($this->model),
			'device' => $device,
		];

		$viewName = $this->getCreateViewName();

		return view($viewName, $data);
	}

	/**
	 *
	 * Edit the resource
	 *
	 * @param $id
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function edit($id)
	{
		$entity = $this->repo->find($id);
		$form = new Formation($entity);

		$device = null;
		if ($entity->notifiable instanceof Device) {
			$device = $entity->notifiable;
		}

		$data = [
			'pageTitle' => $this->getEditPageTitle($entity),
			'entity' => $entity,
			'form' => $form,
			'device' => $device,
		];

        $viewName = $this->getEditViewName();

		return view($viewName, $data);
	}


	/**
	 * @param Request $request
	 * @param null    $id
	 *
	 * @return mixed
	 * @throws UnknownRecepientException
	 */
	protected function storeOrUpdateRequest(Request $request, $id = null, $rules = null, $messages = null)
	{
		$this->validate($request, $rules);

		/** @var PushNotification $entity */
		$entity = $this->repo->fillModelFromRequest($request, $id);

		// send push notification
		if (env('OXYGEN_PUSH_NOTIFICATIONS_SANDBOX', false)) {
			PushNotificationManager::sendStoredPushNotification($entity);
		}

		return $entity;
	}
}
