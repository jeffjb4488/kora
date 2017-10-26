@extends('app', ['page_title' => "Editing {$form->name}", 'page_class' => 'form-edit'])

@section('stylesheets')
  <!-- No Additional Stylesheets Necessary -->
@stop

@section('header')
  <section class="head">
      <div class="inner-wrap center">
        <h1 class="title">
          <i class="icon icon-project-edit"></i>
          <span>Edit Form</span>
        </h1>
        <p class="description">Edit the form information below, and then select “Update Form”</p>
      </div>
  </section>
@stop

@section('body')
  <section class="edit-form center">
    {!! Form::model($form,  ['method' => 'PATCH', 'action' => ['FormController@update',$form->pid, $form->fid]]) !!}
    @include('partials.forms.form',['submitButtonText' => 'Update Form','pid' => $form->pid])
    {!! Form::close() !!}

    <div class="modal modal-js modal-mask form-cleanup-modal-js">
      <div class="content small">
        <div class="header">
          <span class="title title-js"></span>
          <a href="#" class="modal-toggle modal-toggle-js">
            <i class="icon icon-cancel"></i>
          </a>
        </div>
        <div class="body">
          @include("partials.forms.edit.formDeleteForm")
        </div>
      </div>
    </div>
  </section>
@stop

@section('footer')

@stop

@section('javascripts')
  @include('partials.forms.javascripts')

  <script type="text/javascript">
    Kora.Forms.Edit();
  </script>
@stop
