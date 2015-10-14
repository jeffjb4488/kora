@extends('app')


@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-body">

                        <span><h3>Environment File Settings</h3></span>
                        <hr>
                        @foreach($configs as $config => $value)
                            <div class="form-group">
                                <label class="">{{$value[0]}}</label>
                                <input id="{{$config}}"class="form-control" type="text" value="{{$value[1]}}">
                                <button class="btn btn-primary form-control" onClick="updateEnvConfigs('{{$config}}','{{$value[0]}}')" type="submit">Update {{$value[0]}}</button>
                            </div>
                        @endforeach


                        <hr>

                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong>  Make sure you entered everything correctly<br><br>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>


@stop

@section('footer')
    <script>
        function updateEnvConfigs(id,config){
            var updateURL ="{{action('InstallController@updateEnvConfigs')}}";
            $.ajax({
                url:updateURL,
                method:'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "type":config,
                    "value": $('#'+id).val(),
                },
                success: function(data){
                    //console.log($('#'+id).val());
                   location.reload();
                },
                error: function(jqxhr, textStatus, errorThrown){
                    console.log("Error in changing metadata visibility");
                    console.log("text status: " + textStatus);
                    console.log("error thrown: "+errorThrown);
                    alert("There was a problem updating that, make sure you have permission!");
                }
            });
        }
    </script>

@stop