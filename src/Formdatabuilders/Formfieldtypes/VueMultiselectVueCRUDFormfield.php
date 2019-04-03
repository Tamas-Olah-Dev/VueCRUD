<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class VueMultiselectVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * SelectVieCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'vue-multiselect';
        $this->type = null;
    }
}