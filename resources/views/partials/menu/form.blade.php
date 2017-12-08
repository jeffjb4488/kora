<li class="navigation-item">
  <a href="#" class="menu-toggle navigation-toggle-js">
    <i class="icon icon-minus mr-sm"></i>
    <span>{{ \App\Http\Controllers\FormController::getForm($fid)->name }}</span>
    <i class="icon icon-chevron"></i>
  </a>

  <ul class="navigation-sub-menu navigation-sub-menu-js">
      <li class="link link-head">
        <a href="{{ url('/projects/'.$pid).'/forms/'.$fid}}">
          <i class="icon icon-form"></i>
          <span>Form Home</span>
        </a>
      </li>

      <li class="spacer full"></li>

      <li class="link">
        <a href="{{action('FieldController@create', ['pid'=>$pid, 'fid' => $fid])}}">Create New Field</a>
      </li>

      @if(isset($passed_field))
        <?php
          $fieldsInForm = \App\Field::where('fid', '=', $fid)->get()->all();
          $cnt = sizeof($fieldsInForm);
        ?>
        @if($cnt > 0)
          <li class="link" id="form-submenu">
            <a href='#' class="navigation-sub-menu-toggle-js" data-toggle="dropdown">
              <span>Jump to Field</span>
              <img class="icon" src="http://localhost:8888/Kora3/public/assets/images/menu_plus.svg">
            </a>
            <ul class="dropdown-menu scrollable-submenu">
                @foreach($fieldsInForm as $field)
                  @if($field->flid != $passed_field->flid)
                    <li class="link">
                      <a href="{{ url('/projects/'.$pid).'/forms/'.$field->fid .'/fields/'.$field->flid.'/options'}}">{{ $field->name }}</a>
                    </li>
                  @endif
                @endforeach
            </ul>
          </li>
        @endif
      @endif

      <li class="link">
        <a href="{{ url('/projects/'.$pid).'/forms/'.$fid.'/records'}}">View Form Records</a>
      </li>

      @if(\Auth::user()->canIngestRecords(\App\Http\Controllers\FormController::getForm($fid)))
        <li class="link">
          <a href="{{ action('RecordController@create',['pid' => $pid, 'fid' => $fid]) }}">Create New Record</a>
        </li>
      @endif

      @if(\Auth::user()->canIngestRecords(\App\Http\Controllers\FormController::getForm($fid)))
        <li class="spacer full"></li>
        <li class="link">
          <a href="{{ action('RecordController@importRecordsView',['pid' => $pid, 'fid' => $fid]) }}">Import Records</a>
        </li>
      @endif

      <li class="link">
        <a href="{{ action('RecordController@showMassAssignmentView',['pid' => $pid, 'fid' => $fid]) }}">Batch Assign Field Values</a>
      </li>

      @if (\Auth::user()->admin || \Auth::user()->isFormAdmin(\App\Http\Controllers\FormController::getForm($fid)))
        <li class="link">
          <a href="{{action('RevisionController@index', ['pid'=>$pid, 'fid'=>$fid])}}">Manage Record Revisions</a>
        </li>

        <li class="link">
          <a href="{{action('RecordPresetController@index', ['pid'=>$pid, 'fid'=>$fid])}}">Manage Record Presets</a>
        </li>

        <li class="link">
          <a href="{{ action('ExportController@exportRecords',['fid'=>$fid, 'pid' => $pid]) }}">Export All Records</a>
        </li>

        <li class="link">
          <a href="{{ action('FormController@edit', ['pid'=>$pid, 'fid'=>$fid]) }}">Edit Form Information</a>
        </li>

        <li class="link">
          <a href="{{ action('FormGroupController@index', ['pid'=>$pid, 'fid'=>$fid]) }}">Form Permissions</a>
        </li>

        <li class="link">
          <a href="{{action('AssociationController@index', ['fid'=>$fid, 'pid'=>$pid])}}">Association Permissions</a>
        </li>

        <li class="link">
          <a href="{{ action('ExportController@exportForm',['fid'=>$fid, 'pid' => $pid]) }}">Export Form</a>
        </li>
      @endif

      <li class="link">
        <a href="{{url('/projects/'.$pid).'/forms/'.$fid.'/metadata/setup'}}">Link Open Data</a>
      </li>
  </ul>
</li>
