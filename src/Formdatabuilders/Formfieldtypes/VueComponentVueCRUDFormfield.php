<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class VueComponentVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * VueComponentVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'custom-component';
    }

    public function setComponent($componentName)
    {
        $this->type = $componentName;

        return $this;
    }

    public function getComponent()
    {
        return $this->type;
    }
}