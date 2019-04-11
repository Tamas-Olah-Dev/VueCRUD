<?php
/**
 * Created by PhpStorm.
 * User: caleb
 * Date: 3/1/19
 * Time: 12:56 PM
 */

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class VueCRUDFormfield
{
    protected $property;
    protected $kind;
    protected $type;
    protected $containerClass;
    protected $additionalClass;
    protected $label;
    protected $valuesetClass;
    protected $mandatory;
    protected $default;
    protected $rules;
    protected $messages;
    protected $addChooseMessage;
    protected $props;
    protected $helpTooltip;
    protected $onlyWhenCreating;
    protected $customOptions;
    protected $valuesetGetter;
    protected $valuesetSortedGetter;
    protected $step;
    protected $conditions;
    protected $hideIf;

    /**
     * VueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        $this->property = '';
        $this->kind = '';
        $this->type = '';
        $this->containerClass = '';
        $this->additionalClass = '';
        $this->label = '';
        $this->valuesetClass = null;
        $this->mandatory = false;
        $this->default = null;
        $this->rules = [];
        $this->messages = [];
        $this->addChooseMessage = false;
        $this->props = [];
        $this->customOptions = [];
        $this->helpTooltip = '';
        $this->onlyWhenCreating = false;
        $this->valuesetGetter = null;
        $this->valuesetSortedGetter = null;
        $this->conditions = [];
        $this->hideIf = [];
        $allowedKeys = array_keys($this->toArray());
        $this->step = 1;
        foreach ($properties as $property => $value) {
            if (array_search($property, $allowedKeys) !== false) {
                $this->$property = $value;
            }
        }
    }

    public function toArray()
    {
        $result = [
            'property'         => $this->property,
            'kind'             => $this->kind,
            'type'             => $this->type,
            'containerClass'   => $this->containerClass,
            'additionalClass'  => $this->additionalClass,
            'label'            => $this->label,
            'valuesetClass'    => $this->valuesetClass,
            'mandatory'        => $this->mandatory,
            'default'          => $this->default,
            'rules'            => $this->rules,
            'messages'         => $this->messages,
            'addChooseMessage' => $this->addChooseMessage,
            'props'            => $this->props,
            'helpTooltip'      => $this->helpTooltip,
            'onlyWhenCreating' => $this->onlyWhenCreating,
            'customOptions'    => $this->customOptions,
            'conditions' => $this->conditions,
        ];

        return $result;
    }

    /**
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param mixed $property
     * @return VueCRUDFormfield
     */
    public function setProperty($property)
    {
        $this->property = $property;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * @param mixed $kind
     * @return VueCRUDFormfield
     */
    public function setKind($kind)
    {
        $this->kind = $kind;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return VueCRUDFormfield
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContainerClass()
    {
        return $this->containerClass;
    }

    /**
     * @param mixed $containerClass
     * @return VueCRUDFormfield
     */
    public function setContainerClass($containerClass)
    {
        $this->containerClass = $containerClass;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdditionalClass()
    {
        return $this->additionalClass;
    }

    /**
     * @param mixed $additionalClass
     * @return VueCRUDFormfield
     */
    public function setAdditionalClass($additionalClass)
    {
        $this->additionalClass = $additionalClass;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     * @return VueCRUDFormfield
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValuesetClass()
    {
        return $this->valuesetClass;
    }

    /**
     * @param mixed $valuesetClass
     * @return VueCRUDFormfield
     */
    public function setValuesetClass($valuesetClass)
    {
        $this->valuesetClass = $valuesetClass;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMandatory()
    {
        return $this->mandatory;
    }

    /**
     * @param mixed $mandatory
     * @return VueCRUDFormfield
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     * @return VueCRUDFormfield
     */
    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param mixed $rules
     * @return VueCRUDFormfield
     */
    public function setRules($rules)
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param mixed $messages
     * @return VueCRUDFormfield
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddChooseMessage()
    {
        return $this->addChooseMessage;
    }

    /**
     * @param mixed $addChooseMessage
     * @return VueCRUDFormfield
     */
    public function setAddChooseMessage($addChooseMessage)
    {
        $this->addChooseMessage = $addChooseMessage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProps()
    {
        return $this->props;
    }

    /**
     * @param mixed $props
     * @param bool $merge
     * @return VueCRUDFormfield
     * @internal param bool $add
     */
    public function setProps($props, $merge = true)
    {
        if ($merge) {
            $this->props = array_merge($this->props, $props);
        } else {
            $this->props = $props;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHelpTooltip()
    {
        return $this->helpTooltip;
    }

    /**
     * @param mixed $helpTooltip
     * @return VueCRUDFormfield
     */
    public function setHelpTooltip($helpTooltip)
    {
        $this->helpTooltip = $helpTooltip;
        return $this;
    }

    /**
     * @param mixed $onlyWhenCreating
     * @return VueCRUDFormfield
     */
    public function setOnlyWhenCreating($onlyWhenCreating)
    {
        $this->onlyWhenCreating = $onlyWhenCreating;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOnlyWhenCreating()
    {
        return $this->onlyWhenCreating;
    }

    public function setCustomOptions($customOptions, $merge = true)
    {
        if ($merge) {
            $this->customOptions = array_merge($this->customOptions, $customOptions);
        } else {
            $this->customOptions = $customOptions;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getCustomOptions()
    {
        return $this->customOptions;
    }

    /**
     * @param null $valuesetGetter
     * @return VueCRUDFormfield
     */
    public function setValuesetGetter($valuesetGetter)
    {
        $this->valuesetGetter = $valuesetGetter;
        return $this;
    }

    /**
     * @return null
     */
    public function getValuesetGetter()
    {
        return $this->valuesetGetter == null
            ? 'getKeyValueCollection'
            : $this->valuesetGetter;
    }

    /**
     * @param null $valuesetSortedGetter
     * @return VueCRUDFormfield
     */
    public function setValuesetSortedGetter($valuesetSortedGetter)
    {
        $this->valuesetSortedGetter = $valuesetSortedGetter;
        return $this;
    }

    /**
     * @return null
     */
    public function getValuesetSortedGetter()
    {
        return $this->valuesetSortedGetter;
    }

    /**
     * @param int $step
     * @return VueCRUDFormfield
     */
    public function setStep(int $step): VueCRUDFormfield
    {
        $this->step = $step;
        return $this;
    }

    /**
     * @return int
     */
    public function getStep(): int
    {
        return $this->step;
    }

    /**
     * @param array $conditions
     * @return VueCRUDFormfield
     */
    public function setConditions(array $conditions): VueCRUDFormfield
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * @return array
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function meetsConditions($subjectData)
    {
        $result = count($this->conditions) == 0;
        foreach ($this->conditions as $condition) {
            if (isset($subjectData[$condition['field']])) {
                if ($subjectData[$condition['field']] == $condition['value']) {
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * @param array $hideIf
     * @return VueCRUDFormfield
     */
    public function setHideIf(array $hideIf, $merge = true): VueCRUDFormfield
    {
        if ($merge) {
            $this->hideIf = array_merge($this->hideIf, $hideIf);
        } else {
            $this->hideIf = $hideIf;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getHideIf(): array
    {
        return $this->hideIf;
    }
}