<?php namespace App\Http\Controllers;

use App\Field;
use App\FieldHelpers\gPoint;
use App\Http\Requests;
use App\Http\Requests\FieldRequest;
use App\Http\Controllers\Controller;
use App\FieldHelpers\FieldDefaults;
use App\FieldHelpers\UploadHandler;

use Geocoder\HttpAdapter\CurlHttpAdapter;
use Geocoder\Provider\NominatimProvider;
use Geocoder\Provider\YandexProvider;
use Geocoder\Tests\HttpAdapter\CurlHttpAdapterTest;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;
use Toin0u\Geocoder\Facade\Geocoder;

class FieldController extends Controller {


    /**
     * User must be logged in to access views in this controller.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $pid
     * @param $fid
     * @return Response
     */
	public function create($pid, $fid)
	{
        if(!FormController::validProjForm($pid, $fid)){
            return redirect('projects');
        }

        if(!FieldController::checkPermissions($fid, 'create')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

		$form = FormController::getForm($fid);
        return view('fields.create', compact('form'));
	}

    /**
     * Store a newly created resource in storage.
     *
     * @param FieldRequest $request
     * @return Response
     */
	public function store(FieldRequest $request)
    {
        //dd($request);
        $field = Field::Create($request->all());

        //special error check for combo list field
        if($field->type=='Combo List' && ($_REQUEST['cfname1']=='' | $_REQUEST['cfname2']=='')){
            flash()->error(trans('controller_field.comboname'));

            return redirect()->back()->withInput();
        }

        $field->options = FieldDefaults::getOptions($field->type);
        $field->default = FieldDefaults::getDefault($field->type);
        $field->save();

        //need to add field to layout xml
        $form = FormController::getForm($field->fid);
        $layout = explode('</LAYOUT>',$form->layout);
        $form->layout = $layout[0].'<ID>'.$field->flid.'</ID></LAYOUT>';
        $form->save();

        //if advanced options was selected we should call the correct one
        $advError = false;
        if($request->advance) {
            $optC = new OptionController();
            $result = $optC->updateAdvanced($field,$request);
            if($result != ''){
                $advError = true;
                flash()->error('There was an error with the advanced options. '.$result.' Please visit the options page of the field.');
            }
        }

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($form->fid);

        if(!$advError) //if we error on the adv page we should hide the success message so error can display
            flash()->overlay(trans('controller_field.fieldcreated'), trans('controller_field.goodjob'));

        return redirect('projects/'.$field->pid.'/forms/'.$field->fid);
	}

    /**
     * Display the specified resource.
     *
     * @param $pid
     * @param $fid
     * @param $flid
     * @return Response
     * @internal param int $id
     */
	public function show($pid, $fid, $flid)
	{
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = FieldController::getField($flid);
        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);

        $presets = OptionPresetController::getPresetsSupported($pid,$field);

        if($field->type=="Text") {
            return view('fields.options.text', compact('field', 'form', 'proj','presets'));
        }else if($field->type=="Rich Text") {
            return view('fields.options.richtext', compact('field', 'form', 'proj'));
        }else if($field->type=="Number") {
            return view('fields.options.number', compact('field', 'form', 'proj'));
        }else if($field->type=="List") {
            return view('fields.options.list', compact('field', 'form', 'proj','presets'));
        }else if($field->type=="Multi-Select List") {
            return view('fields.options.mslist', compact('field', 'form', 'proj','presets'));
        }else if($field->type=="Generated List") {
            return view('fields.options.genlist', compact('field', 'form', 'proj','presets'));
        }else if($field->type=="Combo List") {
            $presetsOne = $presets->get("one");
            $presetsTwo = $presets->get("two");
            return view('fields.options.combolist', compact('field', 'form', 'proj','presetsOne','presetsTwo'));
        }else if($field->type=="Date") {
            return view('fields.options.date', compact('field', 'form', 'proj'));
        }else if($field->type=="Schedule") {
            return view('fields.options.schedule', compact('field', 'form', 'proj','presets'));
        }else if($field->type=="Geolocator") {
            return view('fields.options.geolocator', compact('field', 'form', 'proj','presets'));
        }else if($field->type=="Documents") {
            return view('fields.options.documents', compact('field', 'form', 'proj'));
        }else if($field->type=="Gallery") {
            return view('fields.options.gallery', compact('field', 'form', 'proj'));
        }else if($field->type=="Playlist") {
            return view('fields.options.playlist', compact('field', 'form', 'proj'));
        }else if($field->type=="Video") {
            return view('fields.options.video', compact('field', 'form', 'proj'));
        }else if($field->type=="3D-Model") {
            return view('fields.options.3dmodel', compact('field', 'form', 'proj'));
        }else if($field->type=="Associator") {
            return view('fields.options.associator', compact('field', 'form', 'proj'));
        }
	}

    /**
     * Show the form for editing the specified resource.
     *
     * @param $pid
     * @param $fid
     * @param $flid
     * @return Response
     * @internal param int $id
     */
	public function edit($pid, $fid, $flid)
	{
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = FieldController::getField($flid);

        return view('fields.edit', compact('field', 'fid', 'pid','presets'));
	}

    /**
     * Update the specified resource in storage.
     *
     * @param $flid
     * @param FieldRequest $request
     * @return Response
     * @internal param int $id
     */
	public function update($pid, $fid, $flid, FieldRequest $request)
	{
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

		$field = FieldController::getField($flid);

        $field->update($request->all());

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($fid);

        flash()->overlay(trans('controller_field.fieldupdated'), trans('controller_field.goodjob'));

        return redirect('projects/'.$pid.'/forms/'.$fid);
	}

    public static function updateRequired($pid, $fid, $flid, $req)
    {
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = FieldController::getField($flid);

        $field->required = $req;
        $field->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($fid);
    }

    public static function updateSearchable($pid, $fid, $flid, $search)
    {
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = FieldController::getField($flid);

        $field->searchable = $search;
        $field->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($fid);
    }

    public static function updateDefault($pid, $fid, $flid, $def)
    {
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = FieldController::getField($flid);

        $field->default = $def;

        $field->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($fid);
    }

    public static function updateOptions($pid, $fid, $flid, $opt, $value)
    {
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = FieldController::getField($flid);

        $options = $field->options;
        $tag = '[!'.$opt.'!]';
        $array = explode($tag,$options);

        $field->options = $array[0].$tag.$value.$tag.$array[2];
        $field->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($fid);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $pid
     * @param $fid
     * @param $flid
     * @return Response
     * @internal param int $id
     */
	public function destroy($pid, $fid, $flid)
	{
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects/'.$pid.'forms/');
        }

        if(!FieldController::checkPermissions($fid, 'delete')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = FieldController::getField($flid);
        $field->delete();

        $form = FormController::getForm($fid);
        $layout = explode('<ID>'.$field->flid.'</ID>',$form->layout);
        $form->layout = $layout[0].$layout[1];
        $form->save();

        RevisionController::wipeRollbacks($form->fid);

        flash()->overlay(trans('controller_field.deleted'), trans('controller_field.goodjob'));
	}

    /**
     * Get field object for use in controller.
     *
     * @param $flid
     * @return mixed
     */
    public static function getField($flid)
    {
        $field = Field::where('flid', '=', $flid)->first();
        if(is_null($field)){
            $field = Field::where('slug','=',$flid)->first();
        }

        return $field;
    }

    /**
     * Validate that a field belongs to a form and project.
     *
     * @param $pid
     * @param $fid
     * @param $flid
     * @return bool
     */
    public static function validProjFormField($pid, $fid, $flid)
    {
        $field = FieldController::getField($flid);
        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);

        if (!FormController::validProjForm($pid, $fid))
            return false;

        if (is_null($field) || is_null($form) || is_null($proj))
            return false;
        else if ($field->fid == $form->fid)
            return true;
        else
            return false;
    }

    public static function getFieldOption($field, $key){
        $options = $field->options;
        $tag = '[!'.$key.'!]';
        $value = explode($tag,$options)[1];

        return $value;
    }

    /**
     * Checks if a user has a certain permission.
     * If no permission is provided checkPermissions simply decides if they are in any form group.
     * This acts as the "can read" permission level.
     *
     * @param $fid
     * @param string $permission
     * @return bool
     */
    private static function checkPermissions($fid, $permission='')
    {
        switch($permission) {
            case 'create':
                if(!(\Auth::user()->canCreateFields(FormController::getForm($fid))))
                {
                    flash()->overlay(trans('controller_field.createper'), trans('controller_field.whoops'));
                    return false;
                }
                return true;
            case 'edit':
                if(!(\Auth::user()->canEditFields(FormController::getForm($fid))))
                {
                    flash()->overlay(trans('controller_field.editper'), trans('controller_field.whoops'));
                    return false;
                }
                return true;
            case 'delete':
                if(!(\Auth::user()->canDeleteFields(FormController::getForm($fid))))
                {
                    flash()->overlay(trans('controller_field.deleteper'), trans('controller_field.whoops'));
                    return false;
                }
                return true;
            default:
                if(!(\Auth::user()->inAFormGroup(FormController::getForm($fid))))
                {
                    flash()->overlay(trans('controller_field.viewper'), trans('controller_field.whoops'));
                    return false;
                }
                return true;
        }
    }

    public static function listArrayToString($array){
        if(is_array($array)){
            $list = $array[0];
            for($i=1;$i<sizeof($array);$i++){
                $list .= '[!]'.$array[$i];
            }
            return $list;
        }

        return '';
    }

}
