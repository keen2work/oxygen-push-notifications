@extends('oxygen::layouts.master-dashboard')

@section('breadcrumbs')
    {{ lotus()->breadcrumbs([
        ['Dashboard', route('dashboard')],
        // ['Change The Resource Name', route('<change here>')],
        [$pageTitle, null, true]
    ]) }}
@stop

@section('pageMainActions')
    @include('oxygen::dashboard.partials.searchField')

    <a href="{{ entity_resource_path() . '/create' }}" class="btn btn-success"><em class="fas fa-plus-circle"></em> Add New</a>
@stop

{{-- DELETE THIS IF NOT USED
@section('pageSummary')
    <div>Content to be inserted at the bottom of the page</div>
@stop
 --}}

@section('content')
    @include('oxygen::dashboard.partials.table-allItems', [
        'tableHeader' => [
            'ID', 'Name', 'Created', 'Sent', 'Actions|text-right'
        ]
    ])

    @foreach ($allItems as $item)
        <tr>
            <td>{{ $item->id }}</td>
            <td>
                {{--<a href="{{ entity_resource_path() . '/' . $item->id }}"></a>--}}
                <strong>{{ $item->title }}</strong>
                <div>{{ $item->message }}</div>
                @if ($item->topic)
                    <div class="badge badge-info">{{ $item->topic_display_name }}</div>
                @elseif ($item->notifiable)
                    @if ($item->notifiable instanceof \App\Models\User)
                        <div class="badge badge-info">{{ $item->notifiable->full_name }}</div>
                    @elseif ($item->notifiable instanceof \EMedia\Devices\Entities\Devices\Device)
                        <div class="badge badge-info">{{ strtoupper($item->device_type) }} DEVICE</div>
                    @else
                        <div class="badge badge-danger">Unknown Receiver</div>
                    @endif
                @else
                    <div class="badge badge-danger">Unknown Receiver</div>
                @endif
            </td>
            <td>
                @if ($item->created_at)
                    {{ standard_datetime($item->created_at) }}
                @endif
            </td>
            {{--<td>--}}
                {{--@if ($item->scheduled_at)--}}
                    {{--<div>{{ standard_datetime($item->scheduled_at) }}</div>--}}
                    {{--<div class="badge badge-primary">{{ $item->scheduled_timezone }}</div>--}}
                {{--@endif--}}
            {{--</td>--}}
            <td>
                @if ($item->sent_at)
                    {{ standard_datetime($item->sent_at) }}
                @else
                    <span class="badge badge-warning">PENDING</span>
                @endif
            </td>
            <td class="text-right">
                <div class="btn-spaced">
                    @if (!$item->sent_at)
                        <a href="{{ entity_resource_path() . '/' . $item->id . '/edit' }}"
                           class="btn btn-warning js-tooltip"
                           title="Edit"><em class="fa fa-edit"></em> Edit</a>

                        @if (isset($isDestroyingEntityAllowed) && $isDestroyingEntityAllowed === true)
                            <form action="{{ entity_resource_path() . '/' . $item->id }}"
                                  method="POST" class="form form-inline js-confirm">
                                {{ method_field('delete') }}
                                {{ csrf_field() }}
                                <button class="btn btn-danger js-tooltip"
                                        title="Delete"><em class="fa fa-times"></em> Delete</button>
                            </form>
                        @endif
                    @endif

                    {{--
                    <form action="{{ entity_resource_path() . '/' . $item->id }}"
                          method="POST" class="form form-inline">
                        {{ method_field('put') }}
                        {{ csrf_field() }}
                        <input type="hidden" name="is_completed" value="{{ $item->is_completed }}" />
                        @if ($item->is_completed)
                            <button class="btn btn-info js-tooltip"
                                    title="Mark as Pending"><em class="fa fa-hourglass-half"></em></button>
                        @else
                            <button class="btn btn-success js-tooltip"
                                    title="Mark as Complete"><em class="fa fa-check"></em></button>
                        @endif
                    </form>
                    --}}



                </div>
            </td>
        </tr>
    @endforeach
@stop
