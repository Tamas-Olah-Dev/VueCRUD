<?php


namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class ItemListWithSelectVueCRUDFormfield extends VueCRUDFormfield
{
    protected $modelClass;

    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'custom-component';
        $this->type = 'item-list-and-component';
        $this->props['component'] = 'componentWrapperWithAddButton';
        $this->props['componentProps'] = [
            'subComponent' => 'searchableSelect',
            'subComponentValuesetProp' => 'valueset',
            'defaultSubComponentProps' => ['multiple' => false, 'valueset' => []],
            'formUrl' => '',
            'storeUrl' => '',
            'fetchUrl' => '}'

        ];;
    }

    public function setModelClass($modelClass)
    {
        $this->modelClass = $modelClass;
        $this->props['componentProps']['formUrl'] = route($modelClass::getVueCRUDRouteName('create'));
        $this->props['componentProps']['storeUrl'] = route($modelClass::getVueCRUDRouteName('store'));
        $this->props['componentProps']['fetchUrl'] = route($modelClass::getVueCRUDRouteName('list'));
        $this->props['componentProps']['formAjaxOperationsUrl'] = route($modelClass::getVueCRUDRouteName('ajax_operations'), ['subject' => '___id___']);

        return $this;
    }
}