<?php


namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class SelectWithAddVueCRUDFormfield extends VueCRUDFormfield
{
    protected $modelClass;

    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'custom-component';
        $this->type = 'component-wrapper-with-add-button';
        $this->props['subComponent'] = 'searchable-select';
        $this->props['subComponentValuesetProp'] = 'valueset';
        $this->props['defaultSubComponentProps'] = [
            'multiple' => false,
            'valueset' => [],
        ];
    }

    public function setModelClass($modelClass)
    {
        $this->modelClass = $modelClass;
        $this->props['formUrl'] = route($modelClass::getVueCRUDRouteName('create'));
        $this->props['storeUrl'] = route($modelClass::getVueCRUDRouteName('store'));
        $this->props['fetchUrl'] = route($modelClass::getVueCRUDRouteName('list'));
        $this->props['formAjaxOperationsUrl'] = route($modelClass::getVueCRUDRouteName('ajax_operations'), ['subject' => '___id___']);

        return $this;
    }
}