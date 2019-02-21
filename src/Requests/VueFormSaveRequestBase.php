<?php

namespace App\Http\Requests;

use App\Forms\VueFormDataBuilder;
use Illuminate\Foundation\Http\FormRequest;

class VueFormSaveRequestBase extends FormRequest
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
            && ((isset($fieldData['onlyWhenCreating'])) && ($fieldData['onlyWhenCreating']))) {
            return false;
        }
        if ($fieldData['kind'] == 'select') {
            if (!$fieldData['mandatory']) {
                $results[$fieldId] = $this->nullIfInvalid($this->input($fieldId));

                return;
            }
        }
        $results[$fieldId] = $this->input($fieldId);
    }

    protected function getRequestType()
    {
        return (\Route::getCurrentRoute()->hasParameter('id') || (\Route::getCurrentRoute()->hasParameter('subject')))
            ? VueFormDataBuilder::REQUEST_TYPE_UPDATING
            : VueFormDataBuilder::REQUEST_TYPE_CREATING;
    }

    protected function nullIfInvalid($value)
    {
        if ($value == -1) {
            return;
        }

        return $value;
    }
}
