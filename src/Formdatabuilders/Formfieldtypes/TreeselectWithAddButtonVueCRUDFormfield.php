<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class TreeselectWithAddButtonVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * TreeselectWithAddButtonVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'custom-component';
        $this->type = 'component-wrapper-with-add-button';
        $this->props['subComponent'] = 'treeselect-wrapper';
        $this->props['subComponentValuesetProp'] = 'originalOptions';
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

    public function addRoutes($slug)
    {
        $this->props['fetchUrl'] = route('vuecrud_'.$slug.'_index');
        $this->props['formUrl'] = route('vuecrud_'.$slug.'_create');
        $this->props['storeUrl'] = route('vuecrud_'.$slug.'_store');
        $this->props['formAjaxOperationsUrl'] = route('vuecrud_'.$slug.'_ajax_operations', ['subject' => -1]);

        return $this;
    }

    public function setTreeselectLabelFieldName($name)
    {
        $this->props['defaultSubComponentProps'] = ['labelName' => $name];

        return $this;
    }
}