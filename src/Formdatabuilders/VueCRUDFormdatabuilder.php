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

    protected $defaultContainerClass = 'form-group col';
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
            $result = collect([0 => 'Nem', 1 => 'Igen']);
            if ((static::getFields()->get($fieldId)->getAddChooseMessage()) && (! $this->isValidValue($this->getValue($fieldId)))) {
                $result->put(-1, __('Please select:'));
            }
            return $result;
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
        $valuesetGetterMethod = static::getFields()->get($fieldId)->getValuesetGetter();
        if (method_exists($valuesetClass, $valuesetGetterMethod)) {
            $valueset = call_user_func($valuesetClass.'::'.$valuesetGetterMethod);
            foreach ($valueset as $index => $value) {
                $result->put($index, $value);
            }
        }

        return $result;
    }

    public function getValuesetSorted($fieldId)
    {
        if (static::getFields()->get($fieldId)->getValuesetSortedGetter() != null) {
            $valuesetClass = static::getFields()->get($fieldId)->getValuesetClass();
            $valuesetGetterMethod = static::getFields()->get($fieldId)->getValuesetSortedGetter();
            if (method_exists($valuesetClass, $valuesetGetterMethod)) {
                return call_user_func($valuesetClass.'::'.$valuesetGetterMethod);
            }
        }
        return $this->getValueset($fieldId)->mapWithKeys(function ($item, $key) {
            return [$item => $key];
        });
    }

    public function buildAllFields()
    {
        $this->formdata = [];
        if ($this->subject == null) {
            $fields = self::getFieldsForStep(self::getCurrentStep());
        } else {
            $fields = self::getFieldsForStep(self::getLastStep());
        }
        foreach ($fields as $fieldId => $fieldData) {
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
                    'props'          => json_encode($fieldData->getProps()),
                    'helpTooltip'    => $fieldData->getHelpTooltip(),
                    'customOptions'    => $fieldData->getCustomOptions()
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

    public static function getValidationRules($requestType, $step = 1)
    {
        $rules = [];
        foreach (self::getFieldsForStep($step) as $fieldId => $fieldData) {
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
                $label = static::getValidationErrorLabel(__($field->getLabel()));
                $rulename = \Illuminate\Support\Str::before($rule, ':');
                if (isset($field->getMessages()[$rulename])) {
                    $messages[$fieldId.'.'.$rulename] = $field->getMessages()[$rulename];
                } else {
                    switch ($rulename) {
                        case 'required':
                            $messages[$fieldId.'.required'] = __('Hiányzó mező').$label;
                            break;
                        case 'not_in':
                            $messages[$fieldId.'.not_in'] = __('Hiányzó mező').$label;
                            break;
                        case 'string':
                            $messages[$fieldId.'.string'] = __('Nem szöveges tartalom').$label;
                            break;
                        case 'numeric':
                            $messages[$fieldId.'.numeric'] = __('Nem numerikus').$label;
                            break;
                        case 'email':
                            $messages[$fieldId.'.email'] = __('Nem megfelelő e-mailcím').$label;
                            break;
                        case 'date':
                            $messages[$fieldId.'.date'] = __('Nem megfelelő dátum').$label;
                            break;
                        case 'confirmed':
                            $messages[$fieldId.'.confirmed'] = __('A két jelszómező tartalma nem egyezik').$label;
                            break;
                        case 'same':
                            $messages[$fieldId.'.same'] = __('A két mező tartalma nem egyezik').$label;
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
        if ($this->subject != null) {
            if (self::getLastStep() != static::getFielddata($fieldId)->getStep()) {
                return false;
            }
        }

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

    protected static function getValidationErrorLabel($label)
    {
        //override this in the final formdatabuilder to change the label
        // or hide it completely by returning '';

        return ': '.$label;
    }

    public static function getSteps()
    {
        return static::getFields()->map(function($item) {
            return $item->getStep();
        })->values()->unique()->sort()->values();
    }

    public static function getLastStep()
    {
        return self::getSteps()->last();
    }

    public static function hasMultipleSteps()
    {
        return self::getSteps()->count() > 1;
    }

    public static function getFieldsForStep($step = 1)
    {
        return static::getFields()->filter(function ($item) use ($step) {
            return $item->getStep() == $step;
        });
    }

    protected static function getCurrentStep()
    {
        //TODO: find out current step
        return 1;
    }
}
