<?php

namespace OlahTamas\VueCRUD\Formdatabuilders;

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
        if (static::getFields()[$fieldId]['default'] != null) {
            $value = static::getFields()[$fieldId]['default'];
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
        if (static::getFields()[$fieldId]['type'] == 'yesno') {
            return collect([0 => 'Nem', 1 => 'Igen']);
        }
        if (static::getFields()[$fieldId]['type'] == 'custom') {
            return collect(static::getFields()[$fieldId]['valuesetClass']);
        }
        $valuesetClass = static::getFields()[$fieldId]['valuesetClass'];
        if ($valuesetClass === null) {
            return collect([]);
        }
        $result = collect([]);
        if ((static::getFields()[$fieldId]['addChooseMessage']) && (! $this->isValidValue($this->getValue($fieldId)))) {
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
                    'kind'           => $fieldData['kind'],
                    'type'           => $fieldData['type'],
                    'containerClass' => $this->defaultContainerClass.' '.$fieldData['containerClass'],
                    'class'          => $this->defaultInputClass.' '.$fieldData['additionalClass'],
                    'valueset'       => $this->getValueset($fieldId),
                    'valuesetSorted' => $this->getValuesetSorted($fieldId),
                    'label'          => __($fieldData['label']),
                    'value'          => $this->getValue($fieldId),
                    'mandatory'      => $fieldData['mandatory'],
                    'props'          => isset($fieldData['props']) ? json_encode($fieldData['props'],
                        JSON_FORCE_OBJECT) : null,
                    'helpTooltip'    => isset($fieldData['helpTooltip']) ? $fieldData['helpTooltip'] : null,
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
                if ($fieldData['mandatory']) {
                    $ruleset[] = 'required';
                } else {
                    $ruleset[] = 'nullable';
                }
                if ($fieldData['type'] == 'text') {
                    $ruleset[] = 'string';
                }
                if ($fieldData['type'] == 'numeric') {
                    $ruleset[] = 'numeric';
                }
                if ($fieldData['kind'] == 'select') {
                    if ($fieldData['mandatory']) {
                        $ruleset[] = 'not_in:-1';
                    }
                }
                $fieldDataRules = $fieldData['rules'];
                if (\Route::getCurrentRoute()->hasParameter('subject')) {
                    $fieldDataRules = collect($fieldDataRules)->transform(function($fieldDataRule, $key) {
                        if (str_contains($fieldDataRule, 'unique:')) {
                            $table = str_after($fieldDataRule, ':');
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
                $label = __(static::getFields()[$fieldId]['label']);
                $rulename = str_before($rule, ':');
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
        return static::getFields()[$fieldId];
    }

    protected static function isFieldOnlyNeededWhenCreating($fieldId)
    {
        $fieldData = self::getFielddata($fieldId);
        if (! isset($fieldData['onlyWhenCreating'])) {
            return false;
        }
        if ($fieldData['onlyWhenCreating'] == false) {
            return false;
        }

        return true;
    }
}
