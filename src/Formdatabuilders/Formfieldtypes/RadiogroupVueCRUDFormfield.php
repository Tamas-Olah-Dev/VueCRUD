<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class RadiogroupVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * TextVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'radio';
        $this->type = null;
    }
}