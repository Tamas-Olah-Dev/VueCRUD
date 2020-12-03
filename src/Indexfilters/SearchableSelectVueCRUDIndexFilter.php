<?php

namespace Datalytix\VueCRUD\Indexfilters;


use Illuminate\Database\Eloquent\Builder;

class SearchableSelectVueCRUDIndexFilter extends VueCRUDIndexfilterBase implements IVueCRUDIndexfilter
{
    public $valueset;
    public $props;
    public $component;

    public function __construct($property, $label, $default, $value = null)
    {
        parent::__construct($property, $label, $default, $value);
        $this->type = 'custom-component';
        $this->component = 'SearchableSelect';
        $this->valueset = [];
        $this->props['labels'] = ['No options' => __('No options available'), 'Select...' => __('Select...')];
        $this->props['respectDisabledAttribute'] = true;
        $this->props['multiple'] = false;
        $this->props['idProperty'] = 'id';
        $this->props['labelProperty'] = 'name';
        $this->props['undefinedValue'] = -1;
        $this->props['undefinedLabel'] = __('All');
    }

    public function setValueset($valueset)
    {
        $this->props['valueset'] = $valueset;

        return $this;
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