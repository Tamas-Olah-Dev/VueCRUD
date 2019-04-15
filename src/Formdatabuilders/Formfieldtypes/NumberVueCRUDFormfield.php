<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class NumberVueCRUDFormfield extends VueCRUDFormfield
{
    protected $forceInteger;

    /**
     * NumberVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'input';
        $this->type = 'number';
        $this->forceInteger = false;
    }

    /**
     * @param bool $forceInteger
     * @return NumberVueCRUDFormfield
     */
    public function setForceInteger(bool $forceInteger): NumberVueCRUDFormfield
    {
        $this->forceInteger = $forceInteger;
        return $this;
    }

    /**
     * @return bool
     */
    public function getForceInteger(): bool
    {
        return $this->forceInteger;
    }

    public function setMax($max)
    {
        $this->customOptions['max'] = $max;

        return $this;
    }

    public function setMin($min)
    {
        $this->customOptions['min'] = $min;

        return $this;
    }

    public function setInputStep($step)
    {
        $this->customOptions['step'] = $step;

        return $this;
    }

    /**
     * @param array $rules
     * valid keys for $rules as 'min', 'max' and 'step'
     * @return VueCRUDFormfield
     */
    public function setNumberRules(array $rules)
    {
        if (isset($rules['max'])) {
            $this->setMax($rules['max']);
        }
        if (isset($rules['min'])) {
            $this->setMin($rules['min']);
        }
        if (isset($rules['step'])) {
            $this->setInputStep($rules['step']);
        }

        return $this;
    }

    public function getMin()
    {
        return isset($this->customOptions['min'])
            ? $this->customOptions['min']
            : null;
    }

    public function getMax()
    {
        return isset($this->customOptions['max'])
            ? $this->customOptions['max']
            : null;
    }

    public function getInputStep()
    {
        return isset($this->customOptions['step'])
            ? $this->customOptions['step']
            : null;
    }
}