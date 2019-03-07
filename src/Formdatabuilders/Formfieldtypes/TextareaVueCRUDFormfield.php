<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class TextareaVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * TextVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'text';
        $this->type = 'simple';
    }
}