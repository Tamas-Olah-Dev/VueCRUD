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
        $this->props = [];
    }

    /**
     * @param $conditionString
     * https://w3c.github.io/html/sec-forms.html#element-attrdef-input-accept
     * @return $this
     */
    public function addAcceptCondition($conditionString)
    {
        if (!isset($this->props['additionalFileTypes'])) {
            $this->props['additionalFileTypes'] = [];
        }
        $this->props['additionalFileTypes'][] = $conditionString;

        return $this;
    }

}