<?php

namespace Datalytix\VueCRUD\Formdatabuilders;

use Illuminate\Validation\Rule;

abstract class VueCRUDFormdatabuilder
{
    const REQUEST_TYPE_CREATING = 'creating';
    const REQUEST_TYPE_UPDATING = 'updating';

    public $formdata;
    public $subject;
    public $defaults;

    protected $defaultContainerClass = 'form-group';
    protected $defaultInputClass = 'form-control';

    public function getValue($fieldId)
    {
        $customGetterMethodName = 'get_'.$fieldId.'_value';
        if (method_exists($this, $customGetterMethodName)) {
            return $this->$customGetterMethodName();
        }

        return $this->getDefaultOrSubjectValue($fieldId);
    }

    public function getDefaultOrSubjectValue($fieldId)
    {
        $value = null;
        if (static::getFields()->get($fieldId)->getDefault() != null) {
            $value = static::getFields()->get($fieldId)->getDefault();
        }
        if ((is_array($this->defaults)) && (isset($this->defaults[$fieldId]))) {
            $value = $this->defaults[$fieldId];
        }
        if ($this->subject !== null) {
            $value = $this->subject->$fieldId;
        }

        return $value;
    }

    public function getValueset($fieldId)
    {
        if (static::getFields()->get($fieldId)->getType() == 'yesno') {
            return collect([0 => 'Nem', 1 => 'Igen']);
        }
        if (static::getFields()->get($fieldId)->getType() == 'custom') {
            return collect(static::getFields()->get($fieldId)->getValuesetClass());
        }
        $valuesetClass = static::getFields()->get($fieldId)->getValuesetClass();
        if ($valuesetClass === null) {
            return collect([]);
        }
        $result = collect([]);
        if ((static::getFields()->get($fieldId)->getAddChooseMessage()) && (! $this->isValidValue($this->getValue($fieldId)))) {
            $result->put(-1, __('Please select:'));
        }
        if (method_exists($valuesetClass, 'getKeyValueCollection')) {
            $valueset = call_user_func($valuesetClass.'::getKeyValueCollection');
            foreach ($valueset as $index => $value) {
                $result->put($index, $value);
            }
        }

        return $result;
    }

    public function getValuesetSorted($fieldId)
    {
        return $this->getValueset($fieldId)->mapWithKeys(function ($item, $key) {
            return [$item => $key];
        });
    }

    public function buildAllFields()
    {
        $this->formdata = [];
        foreach (static::getFields() as $fieldId => $fieldData) {
            if ($this->shouldBuildField($fieldId)) {
                $element = [
                    'kind'           => $fieldData->getKind(),
                    'type'           => $fieldData->getType(),
                    'containerClass' => $this->defaultContainerClass.' '.$fieldData->getContainerClass(),
                    'class'          => $this->defaultInputClass.' '.$fieldData->getAdditionalClass(),
                    'valueset'       => $this->getValueset($fieldId),
                    'valuesetSorted' => $this->getValuesetSorted($fieldId),
                    'label'          => __($fieldData->getLabel()),
                    'value'          => $this->getValue($fieldId),
                    'mandatory'      => $fieldData->getMandatory(),
                    'props'          => json_encode($fieldData->getProps(), JSON_FORCE_OBJECT),
                    'helpTooltip'    => $fieldData->getHelpTooltip(),
                ];
                $this->formdata[$fieldId] = $element;
            }
        }

        return $this;
    }

    public function getFormdataJson()
    {
        return json_encode($this->formdata);
    }

    public static function getValidationRules($requestType)
    {
        $rules = [];
        foreach (static::getFields() as $fieldId => $fieldData) {
            if (($requestType == self::REQUEST_TYPE_CREATING)
                || (! self::isFieldOnlyNeededWhenCreating($fieldId))
            ) {
                $ruleset = [];
                if ($fieldData->getMandatory()) {
                    $ruleset[] = 'required';
                } else {
                    $ruleset[] = 'nullable';
                }
                if ($fieldData->getType() == 'text') {
                    $ruleset[] = 'string';
                }
                if ($fieldData->getType() == 'numeric') {
                    $ruleset[] = 'numeric';
                }
                if ($fieldData->getKind() == 'select') {
                    if ($fieldData->getMandatory()) {
                        $ruleset[] = 'not_in:-1';
                    }
                }
                $fieldDataRules = $fieldData->getRules();
                if (\Route::getCurrentRoute()->hasParameter('subject')) {
                    $fieldDataRules = collect($fieldDataRules)->transform(function($fieldDataRule, $key) {
                        if (\Illuminate\Support\Str::contains($fieldDataRule, 'unique:')) {
                            $table = \Illuminate\Support\Str::after($fieldDataRule, ':');
                            return Rule::unique($table)->ignore(\Route::getCurrentRoute()->parameters()['subject']->id);
                        }
                        return $fieldDataRule;
                    })->all();
                }
                $ruleset = array_merge($ruleset, $fieldDataRules);
                $rules[$fieldId] = $ruleset;
            }
        }

        return $rules;
    }

    public static function getValidationMessages($requestType)
    {
        $rules = self::getValidationRules($requestType);
        $messages = [];
        foreach ($rules as $fieldId => $ruleset) {
            foreach ($ruleset as $rule) {
                $field = static::getFields()->get($fieldId);
                $label = __($field->getLabel());
                $rulename = \Illuminate\Support\Str::before($rule, ':');
                if (isset($field->getMessages()[$rulename])) {
                    $messages[$fieldId.'.'.$rulename] = $field->getMessages()[$rulename];
                } else {
                    switch ($rulename) {
                        case 'required':
                            $messages[$fieldId.'.required'] = __('Hiányzó mező').': '.$label;
                            break;
                        case 'not_in':
                            $messages[$fieldId.'.not_in'] = __('Hiányzó mező').': '.$label;
                            break;
                        case 'string':
                            $messages[$fieldId.'.string'] = __('Nem szöveges tartalom').': '.$label;
                            break;
                        case 'numeric':
                            $messages[$fieldId.'.numeric'] = __('Nem numerikus').': '.$label;
                            break;
                        case 'email':
                            $messages[$fieldId.'.email'] = __('Nem megfelelő e-mailcím').': '.$label;
                            break;
                        case 'date':
                            $messages[$fieldId.'.date'] = __('Nem megfelelő dátum').': '.$label;
                            break;
                        case 'confirmed':
                            $messages[$fieldId.'.confirmed'] = __('A két jelszómező tartalma nem egyezik').': '.$label;
                            break;
                        case 'same':
                            $messages[$fieldId.'.same'] = __('A két mező tartalma nem egyezik').': '.$label;
                            break;
                    }
                }
            }
        }

        return $messages;
    }

    public function buildJson()
    {
        $this->buildAllFields();

        return $this->getFormdataJson();
    }

    protected function isValidValue($value)
    {
        return $value !== null && $value != -1;
    }

    protected function shouldBuildField($fieldId)
    {
        if (! self::isFieldOnlyNeededWhenCreating($fieldId)) {
            return true;
        }
        if ($this->subject == null) {
            return true;
        }

        return false;
    }

    public static function getFielddata($fieldId)
    {
        return static::getFields()->get($fieldId);
    }

    protected static function isFieldOnlyNeededWhenCreating($fieldId)
    {
        $fieldData = self::getFielddata($fieldId);
        if ($fieldData->getOnlyWhenCreating() == false) {
            return false;
        }

        return true;
    }
}
