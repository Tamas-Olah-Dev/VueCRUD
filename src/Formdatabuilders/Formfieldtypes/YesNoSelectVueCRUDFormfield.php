<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


use Datalytix\VueCRUD\Formdatabuilders\Valuesets\YesNoValueset;

class YesNoSelectVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * TextVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'select';
        $this->type = 'yesno';
        $this->setValuesetClass(YesNoValueset::class);
    }
}