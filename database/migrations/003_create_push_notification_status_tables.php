<?php


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePushNotificationStatusTables extends \Illuminate\Database\Migrations\Migration
{

	public function up()
	{
		Schema::create('push_notification_status', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('push_notification_id')->references('id')->on('push_notifications');
			$table->integer('device_id')->references('id')->on('devices');
			$table->dateTime('seen_at')->nullable();
			$table->dateTime('read_at')->nullable();
		});
	}

	public function down()
	{
		Schema::dropIfExists('push_notifications_read_status');
	}

}