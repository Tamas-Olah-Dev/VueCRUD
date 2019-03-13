<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class ImagePickerVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * TextVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'imagepicker';
    }
}