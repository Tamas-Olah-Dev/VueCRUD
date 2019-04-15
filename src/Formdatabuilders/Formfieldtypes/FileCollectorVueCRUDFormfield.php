<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class FileCollectorVueCRUDFormfield extends VueCRUDFormfield
{
    const ACCEPTS_PRESET_PDF = '.pdf,application/pdf';
    const ACCEPTS_PRESET_IMAGE = 'image/*';
    /**
     * FileCollectorVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'custom-component';
        $this->type = 'file-collector';
        $this->props['subComponentValuesetProp'] = 'originalOptions';
    }

    public function addRoutes($slug)
    {
        $id = \Route::getCurrentRoute()->hasParameter('subject')
            ? \Route::getCurrentRoute()->parameters()['subject']
            : -1;
        $this->props['uploadUrl'] = route('vuecrud_'.$slug.'_ajax_operations', ['subject' => $id]);

        return $this;
    }

    public function setLimit($limit)
    {
        $this->props['limit'] = $limit;

        return $this;
    }

    public function setObjectMode()
    {
        $this->props['mode'] = 'object';

        return $this;
    }

    public function setUrlMode()
    {
        $this->props['mode'] = 'url';

        return $this;
    }

    /**
     * @param $conditionString
     * https://w3c.github.io/html/sec-forms.html#element-attrdef-input-accept
     * @return $this
     */
    public function addAcceptCondition($conditionString)
    {
        if (!isset($this->props['accept'])) {
            $this->props['accept'] = [];
        }
        $this->props['accept'][] = $conditionString;

        return $this;
    }
}