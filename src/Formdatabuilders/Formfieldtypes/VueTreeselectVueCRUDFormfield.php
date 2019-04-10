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
        $this->props['loadingText'] = __('Loading');
        $this->props['noChildrenText'] = __('No sub-options');
        $this->props['noOptionsText'] = __('No options available');
        $this->props['noResultsText'] = __('No results found');
        $this->props['clearAllText'] = __('Clear all');
        $this->props['clearValueText'] = __('Clear value');
        $this->props['placeholder'] = __('Select...');
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