{!! csrf_field() !!}
<input type="hidden" name="_method" value="PATCH">
<input type="hidden" name="selected_id" value="">
<input type="hidden" name="options" value="">

<div class="card-container" id="card-container"></div>

<div class="line-container"><span class="line"></span></div>
<div class="card">
    <div class="left">
        <div class="move-actions">
            <a><i class="icon icon-arrow-up"></i></a>
            <a><i class="icon icon-arrow-down"></i></a>
        </div>
        <a class="underline-middle-hover pb-xxs" href="#">Quick Option</a>
    </div>
</div>
<div class="card">
    <div class="left">
        <div class="move-actions">
            <a><i class="icon icon-arrow-up"></i></a>
            <a><i class="icon icon-arrow-down"></i></a>
        </div>
        <a class="underline-middle-hover pb-xxs" href="#">Other Quick Option</a>
    </div>
</div>
<div class="card">
    <div class="left">
        <div class="move-actions">
            <a><i class="icon icon-arrow-up"></i></a>
            <a><i class="icon icon-arrow-down"></i></a>
        </div>
        <a class="underline-middle-hover pb-xxs" href="#">Quick Option</a>
    </div>
</div>

<template id="quick-action-template-js">
    <div class="card">
        <div class="left">
            <div class="move-actions">
                <a class="move-card-js up-js"><i class="icon icon-arrow-up"></i></a>
                <a class="move-card-js down-js"><i class="icon icon-arrow-down"></i></a>
            </div>
            <a class="underline-middle-hover pb-xxs quick-action-title-js" href="#">Quick Option</a>
        </div>
    </div>
</template>

<div class="form-group mt-xxl">
    {!! Form::submit('Update Quick Actions',['class' => 'btn edit-quick-actions-submit-js']) !!}
</div>