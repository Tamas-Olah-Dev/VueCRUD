<?php
/**
 * Created by PhpStorm.
 * User: caleb
 * Date: 2/26/19
 * Time: 11:25 AM
 */

namespace Datalytix\VueCRUD\Indexfilters;


use Illuminate\Database\Eloquent\Builder;

class VueTreeselectVueCRUDIndexfilter extends VueCRUDIndexfilterBase implements IVueCRUDIndexfilter
{
    public $props;

    public function __construct($property, $label, $default, $value = null)
    {
        parent::__construct($property, $label, $default, $value);
        $this->type = 'treeselect';
        $this->props = ['multiple' => false];
        $this->props['loadingText'] = __('Loading');
        $this->props['noChildrenText'] = __('No sub-options');
        $this->props['noOptionsText'] = __('No options available');
        $this->props['noResultsText'] = __('No results found');
        $this->props['clearAllText'] = __('Clear all');
        $this->props['clearValueText'] = __('Clear value');
        $this->props['placeholder'] = __('Select...');
    }

    /**
     * @return mixed
     */
    public function getValueSet()
    {
        return $this->valueset;
    }

    public function setMultiple($value)
    {
        $this->props['multiple'] = $value;

        return $this;
    }

    public function addFilterToQuery(Builder $query, $requestField = null)
    {
        if ($requestField != null) {
            $this->value = request()->get($requestField);
        }
        if (($this->value == null)
            || ((is_array($this->value)) && (count($this->value) == 0))) {
            return $query;
        }
        if ($this->props['multiple']) {
            return $query->whereIn($this->property, $this->value);
        } else {
            if ($this->value != -1) {
                return $query->where($this->property, '=', $this->value);
            }
        }

        return $query;
    }

    /**
     * @param array $valueset
     * @return VueTreeselectVueCRUDIndexfilter
     */
    public function setValueset($valueset): VueTreeselectVueCRUDIndexfilter
    {
        $this->props['options'] = $valueset;
        return $this;
    }
}