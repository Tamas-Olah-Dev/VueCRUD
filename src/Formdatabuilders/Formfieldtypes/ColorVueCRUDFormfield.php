<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class ColorVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * ColorVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'custom-component';
        $this->type = 'color-picker';
    }

    public function setPresets($presets)
    {
        //an array of ['label' => 'Label', 'value' => '#aaaaaa'] items
        $this->setProps(['presets' => $presets], true);

        return $this;
    }

}