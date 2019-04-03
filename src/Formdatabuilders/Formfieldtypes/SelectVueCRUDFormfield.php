<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class SelectVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * SelectVieCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'select';
        $this->type = null;
    }
}