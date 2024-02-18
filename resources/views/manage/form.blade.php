@extends('oxygen::layouts.master-dashboard')

@push('js')
    <script>
		$(document).ready(function() {
			if (!$().datetimepicker) {
                console.error('DEVELOPER ERROR: datetimepicker() not loaded. See the setup instructions to load the date time plugin.');
			} else {
				$('.js-datetime-picker').datetimepicker({
					icons: {
						time: 'fa fa-clock',
						date: 'fa fa-calendar',
						up: 'fa fa-chevron-up',
						down: 'fa fa-chevron-down',
						previous: 'fa fa-chevron-left',
						next: 'fa fa-chevron-right',
						today: 'fa fa-desktop',
						clear: 'fa fa-trash',
						close: 'fa fa-times'
					},
					// timeZone: 'Australia/Melbourne',
				})
			}
		});
    </script>
@endpush

@section ('content')
    {{ lotus()->pageHeadline($pageTitle) }}

    <form action="{{ entity_resource_path() }}" method="post" class="form-horizontal" autocomplete="off">
        {{ csrf_field() }}

        @if ($entity->id)
            {{ method_field('put') }}
            <input type="hidden" name="id" value="{{ $entity->id }}" />
        @endif

        {{ $form->render('title') }}
        {{ $form->render('message') }}

        <div class="form-group row">
            <label for="" class="col-sm-4 control-label">Scheduled Send Time</label>
            <div class="col-sm-8">
                <div class="input-group date" id="datetimepicker1" data-target-input="nearest">
                    <input type="text" class="form-control datetimepicker-input js-datetime-picker"
                           data-target="#datetimepicker1"
                           name="scheduled_at_string"
                           autocomplete="never-again"
                           value="{{ $entity->scheduled_at_string }}"
                    />
                    <div class="input-group-append" data-target="#datetimepicker1" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
            </div>
        </div>

        @if ($device)
            <div class="form-group row">
                <label for="" class="col-sm-4 control-label">Send to Device</label>
                <div class="col-sm-8">
                    {{ strtoupper($device->device_type) }} {{ $device->device_id }}
                    <input type="hidden" class="form-control" name="device_id"
                           readonly="readonly"
                           value="{{ $device->id }}" />
                </div>
            </div>
            <hr>
        @else
            {{ $form->render('topic') }}
        @endif

        {!! $form->renderSubmit() !!}
    </form>
@stop
