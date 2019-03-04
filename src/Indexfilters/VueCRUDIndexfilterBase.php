<?php
/**
 * Created by PhpStorm.
 * User: caleb
 * Date: 2/26/19
 * Time: 11:20 AM
 */

namespace Datalytix\VueCRUD\Indexfilters;


abstract class VueCRUDIndexfilterBase
{
    public $property;
    public $label;
    public $type;
    public $value;
    public $default;

    public function __construct($property, $label, $default, $value = null)
    {
        $this->setProperty($property);
        $this->setLabel($label);
        $this->setValue($value);
        $this->setDefault($default);
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
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value = null)
    {
        if ($value === null) {
            $this->value = $value;
        } else {
            $this->value = request()->get($this->property);
        }
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
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
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
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }
}