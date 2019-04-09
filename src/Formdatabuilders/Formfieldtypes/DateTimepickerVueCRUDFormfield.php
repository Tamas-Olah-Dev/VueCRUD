<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class DateTimepickerVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * DatepickerVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'datepicker';
        $this->setProps(['showTimeInputs' => 'true'], true);
    }

    public function setShowTodayButton(bool $value)
    {
        $this->setProps(['showTodayButton' => $value], true);
        return $this;
    }

}