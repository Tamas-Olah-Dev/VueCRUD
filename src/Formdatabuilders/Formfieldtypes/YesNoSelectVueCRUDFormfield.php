<?php

namespace OlahTamas\VueCRUD\Formdatabuilders;


class YesNoSelectVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * TextVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'select';
        $this->type = 'yesno';
    }
}