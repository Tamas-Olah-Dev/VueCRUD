<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class StaticVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * StaticVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'text';
        $this->type = 'static';
        $this->setCustomOptions(['visible' => false]);
    }

    public function setVisibleValue($value)
    {
        $this->customOptions['visible'] = $value;

        return $this;
    }
}