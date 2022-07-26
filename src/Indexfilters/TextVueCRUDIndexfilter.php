<?php
/**
 * Created by PhpStorm.
 * User: caleb
 * Date: 2/26/19
 * Time: 11:25 AM
 */

namespace Datalytix\VueCRUD\Indexfilters;


use Illuminate\Database\Eloquent\Builder;

class TextVueCRUDIndexfilter extends VueCRUDIndexfilterBase implements IVueCRUDIndexfilter
{
    protected $useHaving = false;

    public function __construct($property, $label, $default, $value = null)
    {
        parent::__construct($property, $label, $default, $value);
        $this->type = 'text';
    }

    public function addFilterToQuery(Builder $query, $requestField = null)
    {
        if ($requestField != null) {
            $this->value = request()->get($requestField);
        }
        if ((string) $this->value == '') {
            return $query;
        }
        return $this->useHaving
            ? $this->addAsHaving($query)
            : $this->addAsWhere($query);
    }

    protected function addAsWhere($query)
    {
        if (is_array($this->property)) {
            return $query->where(function($query) {
                foreach ($this->property as $field) {
                    $query->orWhere($field, 'like', '%'.$this->value.'%');
                }
                return $query;
            });

        } else {
            return $query->where(
                $this->property,
                'like',
                '%'.$this->value.'%'
            );
        }
    }

    protected function addAsHaving($query)
    {
        if (is_array($this->property)) {
            $raws = [];
            foreach ($this->property as $field) {
                $raws[] = $field.' like "%'.$this->value.'%" ';
            }
            return $query->havingRaw(' ('.implode(' OR ', $raws).') ');
        } else {
            return $query->having(
                $this->property,
                'like',
                '%'.$this->value.'%'
            );
        }
    }

    /**
     * @param bool $useHaving
     * @return TextVueCRUDIndexfilter
     */
    public function setUseHaving(bool $useHaving): TextVueCRUDIndexfilter
    {
        $this->useHaving = $useHaving;
        return $this;
    }
}
