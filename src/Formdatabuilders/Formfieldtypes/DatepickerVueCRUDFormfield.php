<?php

namespace Datalytix\VueCRUD\Formdatabuilders;


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
}