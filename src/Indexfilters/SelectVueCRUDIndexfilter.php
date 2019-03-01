<?php

namespace OlahTamas\VueCRUD\Indexfilters;


use Illuminate\Database\Eloquent\Builder;

class SelectVueCRUDIndexfilter extends VueCRUDIndexfilterBase implements IVueCRUDIndexfilter
{
    protected $valueset;

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
        return $query->when((string) $this->value != '', function($query) {
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
     */
    public function setValueSet($valueset)
    {
        $this->valueset = $valueset;
    }

}