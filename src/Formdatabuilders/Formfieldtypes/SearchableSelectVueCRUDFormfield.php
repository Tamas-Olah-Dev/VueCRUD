<?php

namespace Datalytix\VueCRUD\Formdatabuilders\Formfieldtypes;


class SearchableSelectVueCRUDFormfield extends VueCRUDFormfield
{

    /**
     * SearchableSelectVueCRUDFormfield constructor.
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
        $this->kind = 'searchable-select';
        $this->props['labels'] = ['No options' => __('No options available'), 'Select...' => __('Select...')];
        $this->props['respectDisabledAttribute'] = true;
        $this->props['multiple'] = false;
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

    public static function addUndefinedToValueset($valueset)
    {
        $result = collect([collect(['id' => -1, 'label' => __('Please select:')])]);
        foreach ($valueset as $value) {
            $result->push($value);
        }
        return $result;
    }
}