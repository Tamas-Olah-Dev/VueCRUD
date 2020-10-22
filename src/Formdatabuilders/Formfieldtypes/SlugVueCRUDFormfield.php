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
        $this->customOptions['source'] = [];
    }

    public function addSourceFieldName($fieldname)
    {
        $this->customOptions['source'][] = $fieldname;

        return $this;
    }
}