<?php

namespace Datalytix\VueCRUD\Formdatabuilders;


class TextVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * TextVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'input';
        $this->type = 'text';
    }
}