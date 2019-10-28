<?php

namespace Datalytix\VueCRUD\Indexfilters;


use Illuminate\Database\Eloquent\Builder;

class MultiSelectVueCRUDIndexfilter extends VueCRUDIndexfilterBase implements IVueCRUDIndexfilter
{
    public $valueset;
    public $props;
    public $component;

    public function __construct($property, $label, $default, $value = null)
    {
        parent::__construct($property, $label, $default, $value);
        $this->type = 'custom-component';
        $this->component = 'MultiSelect';
        $this->valueset = [];
        $this->props = [
            'idProperty' => 'value',
            'labelProperty' => 'label'
        ];
    }

    public function setMultiple($multiple)
    {
        $this->props['multiple'] = $multiple;

        return $this;
    }

    public function addFilterToQuery(Builder $query, $requestField = null)
    {
        if ($requestField != null) {
            $this->value = request()->get($requestField);
        }
        if ($this->props['multiple']) {
            return $query->whereIn($this->property, $this->value);
        } else {
            return $query->when((string) $this->value != '' && (string) $this->value != '-1', function($query) {
                return $query->where(
                    $this->property,
                    '=',
                    $this->value
                );
            });
        }
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
        $this->props['valueset'] = $this->valueset;
    }

    public function setValuesetFromModelCollection($collection, $idProperty = 'id', $labelProperty = 'name', $undefinedIndex = null, $undefinedLabel = null)
    {
        $this->valueset = [];
        if (($undefinedIndex !== null) && ($undefinedLabel !== null)) {
            $this->valueset[] = ['value' => $undefinedIndex, 'label' => $undefinedLabel];
        }
        foreach ($collection as $item) {
            $this->valueset[] = [
                'value' => $item->$idProperty,
                'label' => $item->$labelProperty
            ];
        }
        $this->props['valueset'] = $this->valueset;
    }

    /**
     * @param mixed $props
     * @return MultiSelectVueCRUDIndexfilter
     */
    public function setProps($props)
    {
        $this->props = $props;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProps()
    {
        return $this->props;
    }
}