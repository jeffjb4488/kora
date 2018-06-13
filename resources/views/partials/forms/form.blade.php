{!! Form::hidden('pid',$pid) !!}

<div class="form-group">
  {!! Form::label('name', 'Form Name') !!}
    <span class="error-message">{{array_key_exists("name", $errors->messages()) ? $errors->messages()["name"][0] : ''}}</span>
  @if ($type == 'edit')
    {!! Form::text('name', null, ['class' => 'text-input' . (array_key_exists("name", $errors->messages()) ? ' error' : ''), 'placeholder' => 'Enter the form name here']) !!}
  @else
    {!! Form::text('name', null, ['class' => 'text-input' . (array_key_exists("name", $errors->messages()) ? ' error' : ''), 'placeholder' => 'Enter the form name here', 'autofocus']) !!}
  @endif
</div>

<div class="form-group mt-xl">
  {!! Form::label('slug', 'Unique Form Identifier') !!}
    <span class="error-message">{{array_key_exists("slug", $errors->messages()) ? $errors->messages()["slug"][0] : ''}}</span>
  {!! Form::text('slug', null, ['class' => 'text-input' . (array_key_exists("slug", $errors->messages()) ? ' error' : ''), 'placeholder' => "Enter the form's unique ID here (no spaces, alpha-numeric values only)"]) !!}
</div>

<div class="form-group mt-xl">
  {!! Form::label('description', 'Description') !!}
    <span class="error-message">{{array_key_exists("description", $errors->messages()) ? $errors->messages()["description"][0] : ''}}</span>
  {!! Form::textarea('description', null, ['class' => 'text-area' . (array_key_exists("description", $errors->messages()) ? ' error' : ''), 'placeholder' => "Enter the form's description here (max. 255 characters)"]) !!}
</div>

@if($submitButtonText == 'Create Form')
  <div class="form-group mt-xl">
    {!! Form::label('admins', 'Select Additional Form Admins') !!}
    {!! Form::select('admins[]', $users, null, [
      'class' => 'multi-select',
      'multiple',
      'data-placeholder' => "Search and select the form admins",
      'id' => 'admins'
    ]) !!}
    <p class="sub-text  mt-xxs">
      Project admins are automatically assigned as admins to this new form, but you may select addition form admins above.
    </p>
  </div>

  @if (count($presets) > 0)
    <div class="form-group mt-xxxl">
      <div class="check-box-half">
        <input type="checkbox" value="1" id="active" class="check-box-input preset-input-js" name="active" />
        <span class="check"></span>
        <span class="placeholder">Apply Form Preset?</span>
      </div>

      <p class="sub-text mt-sm">
        This will apply the form layout structure of the selected form preset to this newly created form.
      </p>
    </div>

    <div class="form-group preset-select-container preset-select-container-js">
      <div class="preset-select-js mt-xl">
  
        <pre><?php var_dump($presets) ?></pre><br><!--$presets seems to have a PID attached to it, but it does not match the PID from the project that it was created from.  EX: Austin's Project has PID: 199, but This is the Form Name has a PID of 248 (was created from Austin's Project).  'form name' has a pid of 232, but belongs to 'kevin test project' which has a pid of 193-->
        
        <?php var_dump($pid) ?><br><br> <!--current project's PID, not relevant to presets-->
        
        <?php echo $project->name ?>
        
        <!--<?php $new_var = $project -> name ?>
        <?php var_dump($new_var) ?> this only returns the name of the projects that we are editing, not the name of the project the preset belongs to-->
        <br><br><br>

        
        
        {!! Form::label('preset', 'Select a Preset') !!}
        {!! Form::select('preset[]', [null=>null] + $presets, null, [
          'class' => 'single-select',
          'data-placeholder' => "Search and select the preset",
          'id' => 'presets'
        ]) !!}
      </div>
    </div>
  @endif

  <div class="form-group mt-xxxl mb-max">
    {!! Form::submit($submitButtonText, ['class' => 'btn validate-form-js']) !!}
  </div>
@else
  <div class="form-group mt-xl">
    <label for="preset">Use this Form as a Preset?</label>
    <div class="check-box">
      <input type="checkbox" value="1" id="preset" class="check-box-input" name="preset" {{$form->preset ? 'checked': ''}} />
      <div class="check-box-background"></div>
      <span class="check"></span>
      <span class="placeholder">Form is <strong>not</strong> set as a preset</span>
      <span class="placeholder-alt">Form is set as a preset</span>
    </div>

    <p class="sub-text mt-sm">
      Setting this form as a preset will  allow you to apply this forms information and layout structure to a new form.
    </p>
  </div>

  <div class="form-group">
    <div class="spacer"></div>

    <div class="form-permissions">
      <span class="question">Need to Edit Form Permissions?</span>

      <a class="action underline-middle-hover" href="{{action('FormGroupController@index', ['pid'=>$form->pid,'fid'=>$form->fid])}}">
        <span>Go to Form Permissions Page</span>
        <i class="icon icon-arrow-right"></i>
      </a>
    </div>

    <div class="spacer"></div>
  </div>

@if (\Auth::user()->admin || \Auth::user()->isFormAdmin($form))
  <div class="form-group no-padding">

    <div class="form-record-management">
      <p class="title">Test Record Management</p>
      <div class="button-container">
        <a href="#" class="btn half-sub-btn">Create Test Record</a>
        <a href="#" class="btn half-sub-btn warning disabled">Delete All Test Records?</a>
      </div>
    </div>

  </div>

  <div class="form-group">
    <div class="spacer"></div>

    <div class="form-file-size">
      <p class="title">Current Form Filesize - {{$filesize}}</p>
      <div class="button-container">
        <a href="#" class="btn half-sub-btn warning">Delete Old Record Files</a>
        <a href="#" class="btn half-sub-btn warning">Delete All Form Records?</a>
      </div>
    </div>

    <div class="spacer"></div>
  </div>
@endif

  <div class="form-group form-update-button">
    {!! Form::submit('Update Form',['class' => 'btn edit-btn update-form-submit pre-fixed-js validate-form-js']) !!}
  </div>

  <div class="form-group">
    <div class="form-cleanup">
      <a class="btn dot-btn trash warning form-trash-js tooltip" data-title="Delete Form?" href="#" tooltip="Delete Form">
        <i class="icon icon-trash"></i>
      </a>
    </div>
  </div>
@endif
