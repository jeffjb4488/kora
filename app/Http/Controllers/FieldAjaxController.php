<?php namespace App\Http\Controllers;

use App\KoraFields\FileTypeField;
use App\KoraFields\GeolocatorField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FieldAjaxController extends Controller { //TODO::CASTLE there really should be minimal logic in this place. Logic should be in field functions

    /*
    |--------------------------------------------------------------------------
    | Field Ajax Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles ajax requests within Kora3/laravel to make Route
    | for specific field related functions. This class merely calls the requested
    | field function, and then returns its result. We do this so field classes can
    | maintain their functions, but since routing in laravel is used to call
    | Controllers instead of Models for best practice, we use this Controller as
    | the go between
    |
    */

    /**
     * Constructs controller and makes sure user is authenticated.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active', ['except' => ['publicRecordFile']]);
    }

    /**
     * Gets field form for advanced create view.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return View
     */
    public function getAdvancedOptionsPage($pid, $fid, Request $request) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $form = FormController::getForm($fid);
        $type = $request->type;

        return view($form->getFieldModel($type)->getAdvancedFieldOptionsView(), compact('fid'));
    }

    /**
     * View single image/video/audio/document from a record.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @param  int $flid - Field ID
     * @param  string $filename - Image filename
     * @return Redirect
     */
    public function singleRichtext($pid, $fid, $rid, $flid) { //TODO::CASTLE
        $field = self::getField($flid);
        $record = RecordController::getRecord($pid.'-'.$fid.'-'.$rid);
        $typedField = $field->getTypedFieldFromRID($rid);

        return view('fields.singleRichtext', compact('field', 'record', 'typedField'));
    }

    /**
     * Validates the address for a Geolocator field.
     *
     * @param  Request $request
     * @return bool - Result of address validity
     */
    public function validateAddress(Request $request) {
        return GeolocatorField::validateAddress($request);
    }

    /**
     * Converts provide lat/long, utm, or geo coordinates into the other types.
     *
     * @param  Request $request
     * @return string - Geolocator formatted string of the converted coordinates
     */
    public function geoConvert(Request $request) { //TODO::CASTLE
        return GeolocatorField::geoConvert($request);
    }

    /**
     * View single image/video/audio/document from a record.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @param  View - The geo view
     * @return Redirect
     */
    public function singleGeolocator($pid, $fid, $rid, Request $request) { //TODO::CASTLE
        $form = FormController::getForm($fid);
        $flid = $request->flid;
        $field = FieldController::getField($flid,$fid);
        $record = RecordController::getRecord($pid.'-'.$fid.'-'.$rid);
        $value = $record->{$flid};
        $typedField = $form->getFieldModel($field['type']);

        return view('fields.singleGeolocator', compact('field', 'record','value','flid','typedField'));
    }

    /**
     * Saves a temporary version of an uploaded file.
     *
     * @param  int $fid - Form ID
     * @param  int $flid - File field that record file will be loaded to
     * @param  Request $request
     */
    public function saveTmpFile($fid, $flid) {
        $field = FieldController::getField($flid,$fid);
        $form = FormController::getForm($fid);
        $form->getFieldModel($field['type'])->saveTmpFile($form, $flid, $field);
    }

    /**
     * Removes a temporary file for a particular field.
     *
     * @param  int $fid - Form ID
     * @param  int $flid - File field to clear temp files for
     * @param  string $name - Name of the file to delete
     * @param  Request $request
     */
    public function delTmpFile($fid, $flid, $filename) {
        $field = FieldController::getField($flid,$fid);
        $form = FormController::getForm($fid);
        $form->getFieldModel($field['type'])->delTmpFile($fid, $flid, $filename);
    }

    /**
     * Public access link for a file.
     *
     * @param  string $filename - Name of the file
     * @return string - the file
     */
    public function publicRecordFile($filename) {
        return FileTypeField::publicRecordFile($filename);
    }

    /**
     * Downloads a file from a particular record field.
     *
     * @param  int $kid - Record Kora ID
     * @param  string $filename - Name of the file
     * @return string - html for the file download
     */
    public function getFileDownload($kid, $filename) {
        return FileTypeField::getFileDownload($kid, $filename);
    }

    /**
     * Downloads a zip file from a particular record field.
     *
     * @param  int $kid - Record Kora ID
     * @param  string $filename - Name of the file
     * @return string - html for the file download
     */
    public function getZipDownload($kid, $filename) {
        return FileTypeField::getZipDownload($kid, $filename);
    }

    /**
     * View single image/video/audio/document from a record.
     *
     * @param  int $kid - Record Kora ID
     * @param  string $filename - Name of the file
     * @return Redirect
     */
    public function singleResource($kid, $filename) {
        return FileTypeField::singleResource($kid, $filename);
    }

    /**
     * Gets the image associated with the Gallery Field of a particular record.
     *
     * @param  int $kid - Record Kora ID
     * @param  int $flid - Field ID
     * @param  string $filename - Name of image file
     * @param  string $type - Get either the full image or a thumbnail of the image
     * @return string - html for the file download
     */
    public function getImgDisplay($kid, $flid, $filename, $type) { //TODO::CASTLE
        $record = RecordController::getRecord($kid);
        $field = FieldController::getField($flid,$record->form_id);

        $form = FormController::getForm($record->form_id);
        $galleryField = $form->getFieldModel($field['type']);

        return $galleryField->getImgDisplay($record, $filename, $type);
    }

    /**
     * Validates record data for a Combo List Field.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @param  Request $request
     * @return JsonResponse - Returns success/error message
     */
    public function validateComboListOpt($pid, $fid, $flid, Request $request) { //TODO::CASTLE
        if(!FieldController::validProjFormField($pid, $fid, $flid))
            return response()->json(["status"=>false,"message"=>"field_invalid"],500);

        return ComboListField::validateComboListOpt($flid, $request);
    }
}
