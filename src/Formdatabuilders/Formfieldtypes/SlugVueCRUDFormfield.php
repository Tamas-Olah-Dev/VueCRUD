<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class SlugVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * SlugVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'slug';
        $this->type = 'slug';
    }
}