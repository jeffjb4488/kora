@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('content')
    <span><h1>{{ $form->name }}</h1></span>
    <div><b>Internal Name:</b> {{ $form->slug }}</div>
    <div><b>Description:</b> {{ $form->description }}</div>
    <div>
        <a href="{{ action('RecordController@create',['pid' => $form->pid, 'fid' => $form->fid]) }}">[New Record]</a>
    </div>
    <hr/>
    <h2>Records</h2>

    @foreach($form->records as $record)
        <div class="panel panel-default">
            <div>
                <b>Record:</b> <a href="{{ action('RecordController@show',['pid' => $form->pid, 'fid' => $form->fid, 'rid' => $record->rid]) }}">{{ $record->kid }}</a>
            </div>
            @foreach($form->fields as $field)
                <div>
                    <span><b>{{ $field->name }}:</b> </span>
                    <span>
                        @if($field->type=='Text')
                            @foreach($record->textfields as $tf)
                                @if($tf->flid == $field->flid)
                                    {{ $tf->text }}
                                @endif
                            @endforeach
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    @endforeach
@stop