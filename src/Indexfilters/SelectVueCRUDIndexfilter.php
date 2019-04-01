<?php

namespace Datalytix\VueCRUD\Indexfilters;


use Illuminate\Database\Eloquent\Builder;

class SelectVueCRUDIndexfilter extends VueCRUDIndexfilterBase implements IVueCRUDIndexfilter
{
    public $valueset;

    public function __construct($property, $label, $default, $value = null)
    {
        parent::__construct($property, $label, $default, $value);
        $this->type = 'select';
        $this->valueset = [];
    }

    public function addFilterToQuery(Builder $query, $requestField = null)
    {
        if ($requestField != null) {
            $this->value = request()->get($requestField);
        }
        return $query->when((string) $this->value != '' && (string) $this->value != '-1', function($query) {
            return $query->where(
                $this->property,
                '=',
                $this->value
            );
        });
    }

    /**
     * @return mixed
     */
    public function getValueSet()
    {
        return $this->valueset;
    }

    /**
     * $valueset needs to be an array of value => label pairs
     * @param mixed $valueset
     * @param null $undefinedIndex
     * @param null $undefinedLabel
     */
    public function setValueSet($valueset, $undefinedIndex = null, $undefinedLabel = null)
    {
        $this->valueset = [];
        if (($undefinedIndex !== null) && ($undefinedLabel !== null)) {
            $this->valueset[] = ['value' => $undefinedIndex, 'label' => $undefinedLabel];
        }
        foreach ($valueset as $value => $label) {
            $this->valueset[] = [
                'value' => $value,
                'label' => $label
            ];
        }
    }
}