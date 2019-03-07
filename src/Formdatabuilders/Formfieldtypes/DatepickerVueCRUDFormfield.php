<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class DatepickerVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * DatepickerVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'datepicker';
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

}