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
}