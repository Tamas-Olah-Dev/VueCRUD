<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class FileCollectorVueCRUDFormfield extends VueCRUDFormfield
{

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
        $id = \Route::getCurrentRoute()->hasParameter('subject')
            ? \Route::getCurrentRoute()->parameters()['subject']
            : -1;
        $this->props['uploadUrl'] = route('vuecrud_'.$slug.'_ajax_operations', ['subject' => $id]);

        return $this;
    }

    public function setTreeselectLabelFieldName($name)
    {
        $this->props['defaultSubComponentProps'] = ['labelName' => $name];

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
}