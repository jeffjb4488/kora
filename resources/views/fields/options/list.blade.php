@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldAjaxController@updateOptions', $field->pid, $field->fid, $field->flid, $field->type], 'onsubmit' => 'selectAll()']) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_list.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('searchable',trans('fields_options_list.search').': ') !!}
        {!! Form::select('searchable',['false', 'true'], $field->searchable, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('extsearch',trans('fields_options_list.extsearch').': ') !!}
        {!! Form::select('extsearch',['false', 'true'], $field->extsearch, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('viewable',trans('fields_options_list.viewable').': ') !!}
        {!! Form::select('viewable',['false', 'true'], $field->viewable, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('viewresults',trans('fields_options_list.viewresults').': ') !!}
        {!! Form::select('viewresults',['false', 'true'], $field->viewresults, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('extview',trans('fields_options_list.extview').': ') !!}
        {!! Form::select('extview',['false', 'true'], $field->extview, ['class' => 'form-control']) !!}
    </div>

    <hr>

    <div class="form-group">
        {!! Form::label('default',trans('fields_options_list.def').': ') !!}
        {!! Form::select('default',\App\ListField::getList($field,true), $field->default,['class' => 'form-control', 'id' => 'default']) !!}
    </div>

    <div class="list_option_form">
        <div>
            {!! Form::label('options',trans('fields_options_list.options').': ') !!}
            <select multiple class="form-control list_options" name="options[]">
                @foreach(\App\ListField::getList($field,false) as $opt)
                    <option value="{{$opt}}">{{$opt}}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-primary remove_option">{{trans('fields_options_list.delete')}}</button>
            <button type="button" class="btn btn-primary move_option_up">{{trans('fields_options_list.up')}}</button>
            <button type="button" class="btn btn-primary move_option_down">{{trans('fields_options_list.down')}}</button>
        </div>
        <div>
            <span><input type="text" class="new_list_option"></span>
            <span><button type="button" class="btn btn-primary add_option">{{trans('fields_options_list.add')}}</button></span>
        </div>
    </div>
    <br>
    <div class="form-group">
        {!! Form::submit('Update List Options',['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    @include('partials.option_preset')

    @include('errors.list')
@stop

@section('footer')
    <script>
        $('#default').select2();

        $('.list_option_form').on('click', '.remove_option', function(){
            val = $('option:selected', '.list_options').val();

            $('option:selected', '.list_options').remove();
            $("#default option[value='"+val+"']").remove();
        });
        $('.list_option_form').on('click', '.move_option_up', function(){
            val = $('option:selected', '.list_options').val();
            defOpt = $("#default option[value='"+val+"']");

            $('.list_options').find('option:selected').each(function() {
                $(this).insertBefore($(this).prev());
            });
            defOpt.insertBefore(defOpt.prev());
        });
        $('.list_option_form').on('click', '.move_option_down', function(){
            val = $('option:selected', '.list_options').val();
            defOpt = $("#default option[value='"+val+"']");

            $('.list_options').find('option:selected').each(function() {
                $(this).insertAfter($(this).next());
            });
            defOpt.insertAfter(defOpt.next());
        });
        $('.list_option_form').on('click', '.add_option', function() {
            val = $('.new_list_option').val();
            val = val.trim();

            if(val != ''){
                $('.list_options').append($("<option/>", {
                    value: val,
                    text: val
                }));
                $('#default').append($("<option/>", {
                    value: val,
                    text: val
                }));
                $('.new_list_option').val('');
            }
        });

        function selectAll(){
            selectBox = $('.list_options > option').each(function(){
                $(this).attr('selected', 'selected');
            });
        }
    </script>
@stop