@extends('fields.show')

@section('fieldOptions')
    <div class="form-group date-input-form-group date-input-form-group-js mt-xxxl">
        <label>Default Date</label>
        <div class="form-input-container">
            <div class="form-group">
                <label>Select Date</label>

                <div class="date-inputs-container date-inputs-container-js">
                    {!! Form::select('default_month',['' => '', '0' => 'Current Month',
                        '1' => '01 - '.date("F", mktime(0, 0, 0, 1, 10)), '2' => '02 - '.date("F", mktime(0, 0, 0, 2, 10)),
                        '3' => '03 - '.date("F", mktime(0, 0, 0, 3, 10)), '4' => '04 - '.date("F", mktime(0, 0, 0, 4, 10)),
                        '5' => '05 - '.date("F", mktime(0, 0, 0, 5, 10)), '6' => '06 - '.date("F", mktime(0, 0, 0, 6, 10)),
                        '7' => '07 - '.date("F", mktime(0, 0, 0, 7, 10)), '8' => '08 - '.date("F", mktime(0, 0, 0, 8, 10)),
                        '9' => '09 - '.date("F", mktime(0, 0, 0, 9, 10)), '10' => '10 - '.date("F", mktime(0, 0, 0, 10, 10)),
                        '11' => '11 - '.date("F", mktime(0, 0, 0, 11, 10)), '12' => '12 - '.date("F", mktime(0, 0, 0, 12, 10))],
                        (!is_null($field['default']) ? $field['default']['month'] : null), ['class' => 'single-select', 'data-placeholder'=>"Select a Month", 'id' => 'default_month']) !!}

                    <select name="default_day" id='default_day' class="single-select" data-placeholder="Select a Day">
                        <option value=""></option>
                        @php
                            if(!is_null($field['default']) && $field['default']['day'] === 0)
                                echo "<option value=" . 0 . " selected>Current Day</option>";
                            else
                                echo "<option value=" . 0 . ">Current Day</option>";

                            $i = 1;
                            while($i <= 31) {
                                if(!is_null($field['default']) && $field['default']['day'] == $i)
                                    echo "<option value=" . $i . " selected>" . $i . "</option>";
                                else
                                    echo "<option value=" . $i . ">" . $i . "</option>";
                                $i++;
                            }
                        @endphp
                    </select>

                    <select name="default_year" class="single-select default-year-js" data-placeholder="Select a Year">
                        <option value=""></option>
                        @php
                            if(!is_null($field['default']) && $field['default']['year'] === 0)
                                echo "<option value=" . 0 . " selected>Current Year</option>";
                            else
                                echo "<option value=" . 0 . ">Current Year</option>";

                            $i = $field['options']['Start'];
                            $j = $field['options']['End'];
                            while($i <= $j) {
                                if(!is_null($field['default']) && $field['default']['year'] == $i)
                                    echo "<option value=" . $i . " selected>" . $i . "</option>";
                                else
                                    echo "<option value=" . $i . ">" . $i . "</option>";
                                $i++;
                            }
                        @endphp
                    </select>
                </div>

                <div class="form-group mt-xl">
                    <div class="check-box-half">
                        <input type="checkbox" value="1" id="preset" class="check-box-input" name="default_circa"
                            {{ ((!is_null($field['default']) && $field['default']['circa']) ? 'checked' : '') }}>
                        <span class="check"></span>
                        <span class="placeholder">Mark this date as an approximate (Circa)?</span>
                    </div>
                </div>

                <div class="form-group mt-xl">
                    <label>Select Calendar/Date Notation</label>
                    <div class="check-box-half mr-m">
                        <input type="checkbox" value="CE" class="check-box-input era-check-js" name="default_era"
                            {{ ((is_null($field['default']) || $field['default']['era'] == 'CE') ? 'checked' : '') }}>
                        <span class="check"></span>
                        <span class="placeholder">CE</span>
                    </div>

                    <div class="check-box-half mr-m">
                        <input type="checkbox" value="BCE" class="check-box-input era-check-js" name="default_era"
                            {{ ((!is_null($field['default']) && $field['default']['era'] == 'BCE') ? 'checked' : '') }}>
                        <span class="check"></span>
                        <span class="placeholder">BCE</span>
                    </div>

                    <div class="check-box-half mr-m">
                        <input type="checkbox" value="BP" class="check-box-input era-check-js" name="default_era"
                            {{ ((!is_null($field['default']) && $field['default']['era'] == 'BP') ? 'checked' : '') }}>
                        <span class="check"></span>
                        <span class="placeholder">BP</span>
                    </div>

                    <div class="check-box-half">
                        <input type="checkbox" value="KYA BP" class="check-box-input era-check-js" name="default_era"
                            {{ ((!is_null($field['default']) && $field['default']['era'] == 'KYA BP') ? 'checked' : '') }}>
                        <span class="check"></span>
                        <span class="placeholder">KYA BP</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('format','Date Format') !!}
        {!! Form::select('format', ['MMDDYYYY' => 'MM DD, YYYY','DDMMYYYY' => 'DD MM YYYY','YYYYMMDD' => 'YYYY MM DD'], $field['options']['Format'], ['class' => 'single-select']) !!}
    </div>

    <div class="form-group mt-xl half pr-m">
        {!! Form::label('start','Start Year') !!}
        <span class="error-message"></span>
        <div class="number-input-container number-input-container-js">
            {!! Form::input('number', 'start', $field['options']['Start'], ['class' => 'text-input start-year-js', 'placeholder' => 'Enter start year here']) !!}
        </div>
    </div>

    <div class="form-group mt-xl half pl-m">
        {!! Form::label('end','End Year') !!}
        <span class="error-message"></span>
        <div class="number-input-container number-input-container-js">
            {!! Form::input('number', 'end', $field['options']['End'], ['class' => 'text-input end-year-js', 'placeholder' => 'Enter end year here']) !!}
        </div>
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('circa','Show Circa Approximations?') !!}
        {!! Form::select('circa', [0 => 'No', 1 => 'Yes'], $field['options']['ShowCirca'], ['class' => 'single-select']) !!}
    </div>

    <div class="form-group mt-xl">
        {!! Form::label('era','Show Calendar/Date Notation?') !!}
        {!! Form::select('era', [0 => 'No', 1 => 'Yes'], $field['options']['ShowEra'], ['class' => 'single-select']) !!}
    </div>
@stop

@section('fieldOptionsJS')
    Kora.Fields.Options('Date');
    Kora.Inputs.Number();
@stop
