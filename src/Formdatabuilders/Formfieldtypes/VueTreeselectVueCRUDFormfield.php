<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class VueTreeselectVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * SelectVieCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'vue-treeselect';
        $this->type = null;
        $this->valuesetGetter = 'getVueTreeselectCompatibleCollection';
    }

    /**
     * @param bool $multiple
     * @return $this
     */
    public function setMultiple($multiple)
    {
        $this->props['multiple'] = $multiple;

        return $this;
    }
}