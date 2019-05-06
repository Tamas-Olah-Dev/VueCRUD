<?php

namespace Datalytix\VueCRUD\Formdatabuilders;

use Datalytix\VueCRUD\Formdatabuilders\Valuesets\YesNoValueset;
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

    protected $steps = [];

    public function addStepLabel($index, $label)
    {
        $this->steps[$index] = $label;

        return $this;
    }

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
        if (static::getFields()->get($fieldId)->getDefault() !== null) {
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
        $field = static::getFielddata($fieldId);
        if ($field->getType() == 'yesno') {
            $result = collect([]);
            if (($field->getAddChooseMessage()) && (! $this->isValidValue($this->getValue($fieldId)))) {
                $result->put(-1, __('Please select:'));
            }
            $result = $result->merge(YesNoValueset::getKeyValueCollection());
            return $result;
        }
        if ($field->getType() == 'custom') {
            $class = $field->getValuesetClass();
            $getter = $field->getValuesetGetter();
            $result = $class::$getter();
            if ($field->getAddChooseMessage()) {
                if (method_exists($field, 'addUndefinedToValueset')) {
                    $result = $field::addUndefinedToValueset($result);
                } else {
                    $result->put(-1, __('Please select:'));
                }
            }
            return $result;
        }
        $valuesetClass = $field->getValuesetClass();
        if ($valuesetClass === null) {
            return collect([]);
        }
        $result = collect([]);
        if (($field->getAddChooseMessage()) && (! $this->isValidValue($this->getValue($fieldId)))) {
            if (method_exists($field, 'addUndefinedToValueset()')) {
                $result = $field::addUndefinedToValueset($result);
            } else {
                $result->put(-1, __('Please select:'));
            }
        }
        $valuesetGetterMethod = $field->getValuesetGetter();
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
        $fields = static::getFields();
        foreach ($fields as $fieldId => $fieldData) {
            if ($this->shouldBuildField($fieldId)) {
                $element = [
                    'step'           => $fieldData->getStep(),
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
                    'customOptions'  => $fieldData->getCustomOptions(),
                    'conditions'     => $fieldData->getConditions(),
                    'hideIf'         => $fieldData->getHideIf(),
                    'placeholder'    => $fieldData->getPlaceholder(),
                ];
                $this->formdata[$fieldId] = $element;
            }
        }

        return $this;
    }

    public function getConditionFields()
    {
        $result = [];
        foreach (static::getFields() as $field) {
            foreach ($field->getConditions() as $condition) {
                $result[$condition['field']] = 1;
            }
        }

        return array_keys($result);
    }

    protected function buildConfigurationFormdata()
    {
        return [
            'config' => [
                'mode'            => $this->subject === null ? 'creating' : 'editing',
                'steps'           => self::getSteps(),
                'stepLabels'      => $this->steps,
                'conditionFields' => $this->getConditionFields(),
            ],
        ];
    }

    public function getFormdataJson()
    {
        return json_encode(array_merge($this->buildConfigurationFormdata(), $this->formdata));
    }

    public static function getValidationRules($requestType)
    {
        $rules = [];
        foreach (self::getFieldsForStep(self::getCurrentStep()) as $fieldId => $fieldData) {
            if ((self::shouldBuildValidationField($fieldId))
                && (($requestType == self::REQUEST_TYPE_CREATING)
                    || (! self::isFieldOnlyNeededWhenCreating($fieldId)))
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
                if (($fieldData->getType() == 'numeric') || ($fieldData->getType() == 'number')) {
                    $ruleset[] = 'numeric';
                    if (($fieldData->getType() == 'number') && ($fieldData->getForceInteger())) {
                        $ruleset[] = 'integer';
                    }
                    if (($fieldData->getType() == 'number') && ($fieldData->getMin() != null)) {
                        $ruleset[] = 'min:'.$fieldData->getMin();
                    }
                    if (($fieldData->getType() == 'number') && ($fieldData->getMax() != null)) {
                        $ruleset[] = 'max:'.$fieldData->getMax();
                    }
                }
                if ($fieldData->getKind() == 'select') {
                    if ($fieldData->getMandatory()) {
                        $ruleset[] = 'not_in:-1';
                    }
                }
                $fieldDataRules = $fieldData->getRules();
                if (\Route::getCurrentRoute()->hasParameter('subject')) {
                    $fieldDataRules = collect($fieldDataRules)->transform(function ($fieldDataRule, $key) {
                        if(is_string($fieldDataRule)) {
                            if (\Illuminate\Support\Str::contains($fieldDataRule, 'unique:')) {
                                $table = \Illuminate\Support\Str::after($fieldDataRule, ':');
                                return Rule::unique($table)->ignore(\Route::getCurrentRoute()->parameters()['subject']->id);
                            }
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
                if (is_string($rule)) {
                    $rulename = \Illuminate\Support\Str::before($rule, ':');
                } else {
                    $rulename = get_class($rule);
                }
                if (isset($field->getMessages()[$rulename])) {
                    $messages[$fieldId.'.'.$rulename] = $field->getMessages()[$rulename];
                } else {
                    switch ($rulename) {
                        case 'required':
                            $messages[$fieldId.'.required'] = __('Value missing').$label;
                            break;
                        case 'not_in':
                            $messages[$fieldId.'.not_in'] = __('Value missing').$label;
                            break;
                        case 'string':
                            $messages[$fieldId.'.string'] = __('Not a string').$label;
                            break;
                        case 'numeric':
                            $messages[$fieldId.'.numeric'] = __('Not a number').$label;
                            break;
                        case 'integer':
                            $messages[$fieldId.'.integer'] = __('Not an integer').$label;
                            break;
                        case 'email':
                            $messages[$fieldId.'.email'] = __('Not a valid e-mail address').$label;
                            break;
                        case 'date':
                            $messages[$fieldId.'.date'] = __('Not a valid date').$label;
                            break;
                        case 'confirmed':
                            $messages[$fieldId.'.confirmed'] = __('The two password fields do not match').$label;
                            break;
                        case 'same':
                            $messages[$fieldId.'.same'] = __('The two fields do not match').$label;
                            break;
                        case 'min':
                            $messages[$fieldId.'.min'] = __('Value has to be at least :min', ['min' => $field->getMin()]);
                            break;
                        case 'max':
                            $messages[$fieldId.'.max'] = __('Value cannot exceed :max', ['max' => $field->getMax()]);
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

    protected static function shouldBuildValidationField($fieldId)
    {
        $fieldData = self::getFielddata($fieldId);
        if (self::getCurrentStep() != $fieldData->getStep()) {
            return false;
        }
        if (request()->has('subjectdata')) {
            if (! $fieldData->meetsConditions((array) json_decode(request()->get('subjectdata')))) {
                return false;
            }
        }
        if (! $fieldData->meetsConditions(request()->all())) {
            return false;
        }

        return true;
    }

    protected function shouldBuildField($fieldId)
    {
        $fieldData = self::getFielddata($fieldId);
        if ($this->subject != null) {
            $step = self::getLastStep();
        } else {
            $step = 1;
        }
        if (($this->subject == null) && (self::getCurrentStep($step) != $fieldData->getStep())) {
            return false;
        }
        if (request()->has('subjectdata')) {
            if (! $fieldData->meetsConditions((array) json_decode(request()->get('subjectdata')))) {
                return false;
            }
        }
        if (($this->subject != null) && (! $fieldData->meetsConditions($this->subject->toArray()))) {
            return false;
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
        return static::getFields()->map(function ($item) {
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

    protected static function getCurrentStep($default = 1)
    {
        return request()->get('currentStep', $default);
    }
}
