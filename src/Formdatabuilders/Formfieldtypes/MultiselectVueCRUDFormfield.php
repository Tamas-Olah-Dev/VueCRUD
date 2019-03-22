<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class MultiselectVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * MultiselectVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'multiselect';
        $this->type = null;
    }
}