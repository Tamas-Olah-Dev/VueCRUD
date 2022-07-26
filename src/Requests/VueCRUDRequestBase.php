<?php

namespace Datalytix\VueCRUD\Requests;

use Datalytix\VueCRUD\Formdatabuilders\VueCRUDFormdatabuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class VueCRUDRequestBase extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $class = static::FORMDATABUILDER_CLASS;

        return $class::getValidationRules($this->getRequestType());
    }

    public function messages()
    {
        $class = static::FORMDATABUILDER_CLASS;

        return $class::getValidationMessages($this->getRequestType());
    }

    public function addFieldDataToResults($fieldId, &$results, $subject = null)
    {
        $class = static::FORMDATABUILDER_CLASS;
        $fieldData = $class::getFielddata($fieldId);
        if (($subject != null)
            && ($fieldData->getOnlyWhenCreating())) {
            return false;
        }
        if ($fieldData->getKind() == 'select') {
            if (!$fieldData->getMandatory()) {
                $results[$fieldId] = $this->nullIfInvalid($this->input($fieldId));

                return;
            }
        }
        $results[$fieldId] = $this->input($fieldId);
    }

    protected function getRequestType()
    {
        return (\Route::getCurrentRoute()->hasParameter('id') || (\Route::getCurrentRoute()->hasParameter('subject')))
            ? VueCRUDFormdatabuilder::REQUEST_TYPE_UPDATING
            : VueCRUDFormdatabuilder::REQUEST_TYPE_CREATING;
    }

    public function getRequestSubjectId()
    {
        if (\Route::getCurrentRoute()->hasParameter('id')) {
            return \Route::getCurrentRoute()->originalParameter('id');
        }
        if (\Route::getCurrentRoute()->hasParameter('subject')) {
            return \Route::getCurrentRoute()->originalParameter('subject');
        }

        return null;
    }

    protected function nullIfInvalid($value)
    {
        if ($value == -1) {
            return;
        }

        return $value;
    }

    protected function getBaseDatasetFromRequest($subjectClass)
    {
        //this is extremely basic, and while could work, you should not rely on it
        $keys = (new $subjectClass())->getFillable();
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->input($key, null);
        }

        return $result;
    }

    protected function addPositionToDatasetIfNecessary($subject, $subjectClass, $dataset)
    {
        if ($subject != null) {
            return $dataset;
        }
        if (!$subjectClass::hasPositioningEnabled()) {
            return $dataset;
        }
        $positioningRestrictions = [];
        foreach ($subjectClass::getRestrictingFields() as $field) {
            $positioningRestrictions[$field] = null;
        }
        foreach ($dataset as $key => $value) {
            if (array_key_exists($key, $positioningRestrictions)) {
                $positioningRestrictions[$key] = $value;
            }
        }
        $dataset[$subjectClass::getPositionField()] = $subjectClass::getFirstAvailablePosition($positioningRestrictions);

        return $dataset;
    }

    public function isCurrentStepFinal()
    {
        $class = static::FORMDATABUILDER_CLASS;

        return request()->get('currentStep') == $class::getLastStep();
    }


    public static function cleanRandomizationStringFromUploadFilename($filename)
    {
        return Str::after($filename, '___');
    }

}
