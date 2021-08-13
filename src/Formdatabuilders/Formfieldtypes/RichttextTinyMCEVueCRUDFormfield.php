<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class RichttextTinyMCEVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * TextVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'text';
        $this->type = 'richtext-tinymce';
        $this->setCustomOptions(['minHeight' => '600']);
        $this->props = ['componentId' => random_int(0, 9999999)];
    }
}