<?php

namespace Datalytix\VueCRUD\Indexfilters;


use Illuminate\Database\Eloquent\Builder;

class DateVueCRUDIndexfilter extends VueCRUDIndexfilterBase implements IVueCRUDIndexfilter
{
    protected $comparisonOperator;

    public function __construct($property, $label, $default, $value = null)
    {
        parent::__construct($property, $label, $default, $value);
        $this->type = 'datepicker';
        $this->comparisonOperator = '=';
    }

    public function setComparisonToEqual()
    {
        $this->comparisonOperator = '=';

        return $this;
    }

    public function setComparisonToGreaterThan()
    {
        $this->comparisonOperator = '>';

        return $this;
    }

    public function setComparisonToLessThan()
    {
        $this->comparisonOperator = '<';

        return $this;
    }

    public function setComparisonToGreaterThanOrEqual()
    {
        $this->comparisonOperator = '>=';

        return $this;
    }

    public function setComparisonToLessThanOrEqual()
    {
        $this->comparisonOperator = '<=';

        return $this;
    }

    public function addFilterToQuery(Builder $query, $requestField = null)
    {
        if ($requestField != null) {
            $this->value = request()->get($requestField);
        }
        return $query->when((string) $this->value != '', function($query) {
            return $query->whereDate(
                $this->property,
                $this->comparisonOperator,
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