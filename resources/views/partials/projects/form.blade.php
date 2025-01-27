<div class="form-group mt-xl">
  {!! Form::label('name', 'Project Name') !!}
  <span class="error-message">{{array_key_exists("name", $errors->messages()) ? $errors->messages()["name"][0] : ''}}</span>
  @if ($type == 'edit')
    {!! Form::text('name', null, ['class' => 'text-input' . (array_key_exists("name", $errors->messages()) ? ' error' : ''), 'placeholder' => 'Enter the project name here']) !!}
  @else
    {!! Form::text('name', null, ['class' => 'text-input' . (array_key_exists("name", $errors->messages()) ? ' error' : ''), 'placeholder' => 'Enter the project name here', 'autofocus']) !!}
  @endif
</div>

<div class="form-group mt-xl">
    {!! Form::label('description', 'Description') !!}
    <span class="error-message">{{array_key_exists("description", $errors->messages()) ? $errors->messages()["description"][0] : ''}}</span>
    {!! Form::textarea('description', null, ['class' => 'text-area' . (array_key_exists("description", $errors->messages()) ? ' error' : ''), 'placeholder' => "Enter the projects description here (max. 1000 characters)"]) !!}
</div>

@if($projectMode == 'project_create')
<div class="form-group mt-xl">
    {!! Form::label('admins', 'Select Project Admins') !!}
    {!! Form::select('admins[]', $userNames, null, [
      'class' => 'multi-select',
      'multiple',
      'data-placeholder' => "Search and select the project admins",
      'id' => 'admins'
    ]) !!}
    <p class="sub-text mt-xs">
      As the creator of this project, you are automatically added as a project admin.
    </p>
</div>
@endif

@if($projectMode == 'project_edit')
<div class="form-group mt-xl">
  <div class="spacer"></div>

  <div class="project-permissions">
    <span class="question">Need to Edit Project Permissions?</span>

    <a class="action underline-middle-hover" href="{{ action('ProjectGroupController@index', ['pid' => $pid]) }}">
      <span>Go to Project Permissions Page</span>
      <i class="icon icon-arrow-right"></i>
    </a>
  </div>

  <div class="spacer"></div>
</div>
@endif

@if($projectMode == 'project_create')
<div class="form-group mt-100-xl">
  {!! Form::submit('Create Project',['class' => 'btn validate-project-js']) !!}
</div>
@elseif($projectMode == 'project_edit')
<div class="form-group project-update-button">
  {!! Form::submit('Update Project',['class' => 'btn edit-btn update-project-submit pre-fixed-js validate-project-js']) !!}
</div>
@endif


@if($projectMode == 'project_edit')
<div class="form-group">
  <div class="project-cleanup">
    <a class="btn dot-btn archive warning project-archive-js tooltip" data-title="Archive Project?" href="#" tooltip="Archive Project">
      <i class="icon icon-archive"></i>
    </a>

    <a class="btn dot-btn trash warning project-trash-js tooltip" data-title="Delete Project?" href="#" tooltip="Delete Project">
      <i class="icon icon-trash"></i>
    </a>
  </div>
</div>
@endif
