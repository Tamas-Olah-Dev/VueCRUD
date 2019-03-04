<?php

namespace Datalytix\VueCRUD\Formdatabuilders;


class CheckboxgroupVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * TextVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'checkbox';
        $this->type = null;
    }
}