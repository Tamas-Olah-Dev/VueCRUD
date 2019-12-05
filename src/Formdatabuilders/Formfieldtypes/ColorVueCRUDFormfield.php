<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class ColorVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * ColorVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'color';
        $this->type = 'text';
    }
}