<?php

namespace Datalytix\VueCRUD\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasPositionThroughPivot
{
    public $pivotModelRestrictions = [];
    /**
     * @return array
     * this function returns a list of fields that are used as restrictions when changing position
     * for example if it returns ['role_id'], every non-static positioning function will
     * only look for elements with the same role_id as the subject
     */
    abstract public static function getRestrictingFields();

    abstract public static function getPositioningPivotForeignKey();
    abstract public static function getPositioningPivotModelclass();

    //override this to get more fields beside the position, like for visibility etc
    //don't use fields that are already present on the main model, like id
    public static function getPositioningPivotModelFields()
    {
        return ['position'];
    }


    //in some extreme situations it can be helpful to provide a specific model query
    //like when we are using descendant classes but want to position using the base
    public static function getBaseQuery()
    {
        return static::query();
    }

    // as there can be multiple pivot models, we need to know which ones are we working with on ajax requests
    // and during positioning.
    // the controller will pass every query field to setPivotModelRestrictions, and the task of the
    // filterDataForPivotModelRestrictions function is to only keep those that are actually needed to select
    // the currently needed pivot model
    // e.g. when an article belongs to multiple pages, we will only need the page_id to know which pivot
    // model we want to find in article_page

    abstract public static function filterDataForPivotModelRestrictions($data);

    public function setPivotModelRestrictions($pivotModelRestrictions)
    {
        $this->pivotModelRestrictions = static::filterDataForPivotModelRestrictions($pivotModelRestrictions);
    }

    public function getPivotModel()
    {
        return static::getPositioningPivotModelclass()::query()
            ->where(static::getPositioningPivotForeignKey(), '=', $this->getKey())
            ->where($this->pivotModelRestrictions)
            ->first();
    }

    //override this if instead of 'position' you want to use some other field
    public static function getPositionField()
    {
        return 'position';
    }

    public function scopeOrderByPosition($query, $direction = 'asc')
    {
        $positionField = static::getPositionField();
        return $query->orderBy($positionField, $direction);
    }

    public function scopeInPosition($query, $position)
    {
        $positionField = static::getPositionField();
        return $query->where($positionField, '=', $position);
    }


    public static function bootHasPositionThroughPivot()
    {
        static::addGlobalScope('withPosition', function(Builder $builder) {
            $model = new static();
            $selects = static::getPositioningPivotModelFields();
            $pivotkey = 'pivot_'.static::getPositioningPivotForeignKey();
            $selects[] = \DB::raw(static::getPositioningPivotForeignKey().' as '.$pivotkey);
            return $builder->rightJoinSub(
                static::getPositioningPivotModelclass()::select($selects),
                '_ptr',
                $model->getTable().'.'.$model->getKeyName(),
                '=',
                '_ptr.'.$pivotkey
            );
        });
    }

    public function exchangePositionWithElementInPosition($position)
    {
        $element = static::getBaseQuery()::inPosition($position)
            ->withRestrictions($this->buildRestrictions())
            ->first();
        if ($element === null) {
            return false;
        }
        $element->setPivotModelRestrictions($this->pivotModelRestrictions);

        return $this->exchangePositionWithElement($element);
    }

    public function exchangePositionWithElement($element)
    {
        if ($element === null) {
            return false;
        }
        $element->setPivotModelRestrictions($this->pivotModelRestrictions);
        $transactionResult = \DB::transaction(function() use ($element) {
            $positionField = static::getPositionField();
            $position = $element->$positionField;
            $element->getPivotModel()->update([$positionField => $this->$positionField]);
            $this->getPivotModel()->update([$positionField => $position]);
        });

        return $transactionResult === null;

    }

    public function moveUp()
    {
        return $this->exchangePositionWithElement($this->findPreviousElementByPosition());
    }

    public function moveDown()
    {
        return $this->exchangePositionWithElement($this->findNextElementByPosition());
    }

    public function buildRestrictions()
    {
        $result = [];
        foreach (static::getRestrictingFields() as $field) {
            $result[$field] = $this->$field;
        }

        return $result;
    }

    //restrictions allow for using multiple position sets in one database
    //for example if we want users handled differently depending on their role_id property,
    //we can use ['role_id' => %DESIREDVALUE%] as $restrictions and the operations will only apply to those
    //multiple restrictions can be specified in one array

    public function scopeWithRestrictions($query, $restrictions = null)
    {
        $restrictions = $restrictions === null ? $this->buildRestrictions() : $restrictions;
        return $query->when(count($restrictions) > 0, function ($query) use ($restrictions) {
            foreach ($restrictions as $field => $value) {
                $query = $query->where(\DB::raw($field), '=', $value);
            }
        });
    }

    public static function getFirstAvailablePosition($restrictions = [])
    {
        $positionField = static::getPositionField();
        $result = static::getBaseQuery()
            ->withRestrictions($restrictions)
            ->max($positionField);

        return intval($result) + 1;
    }

    public static function allByPosition($restrictions = [], $direction = 'asc')
    {
        $positionField = static::getPositionField();
        return static::getBaseQuery()::where('id', '>', 0)
            ->withRestrictions($restrictions)
            ->orderBy($positionField, $direction)
            ->get();
    }

    public static function allByPositionPaginated($itemsPerPage = 10)
    {
        $positionField = static::getPositionField();
        return static::getBaseQuery()::where('id', '>', 0)
            ->orderBy($positionField, 'asc')
            ->paginate($itemsPerPage);
    }

    public function updatePositionOfOtherElementsBeforeDelete()
    {
        $transactionResult = \DB::transaction(function () {
            $positionField = static::getPositionField();
            $itemsAbove = static::getBaseQuery()->where($positionField, '>', $this->$positionField)
                ->withRestrictions($this->buildRestrictions())
                ->get();

            foreach ($itemsAbove as $item) {
                $item->getPivotModel()->update([
                    $positionField => intval($item->$positionField) - 1,
                ]);
            }
        });

        return $transactionResult === null;
    }

    public function findPreviousElementByPosition()
    {
        $positionField = static::getPositionField();
        return static::getBaseQuery()->withRestrictions($this->buildRestrictions())
            ->where($positionField, '<', $this->$positionField)
            ->orderBy($positionField, 'desc')
            ->first();
    }

    public function findNextElementByPosition()
    {
        $positionField = static::getPositionField();
        return static::getBaseQuery()->withRestrictions($this->buildRestrictions())
            ->where($positionField, '>', $this->$positionField)
            ->orderBy($positionField, 'asc')
            ->first();
    }

    public function moveToPosition($position)
    {
        $positionField = static::getPositionField();

        if ($position == $this->$positionField) {
            return false;
        }
        $transactionResult = \DB::transaction(function() use ($position, $positionField) {
            $max = static::getFirstAvailablePosition($this->buildRestrictions());
            if ($position >= $max) {
                $position = $max - 1;
            }
            if ($position < 1) {
                $position = 1;
            }
            $start = $position < $this->$positionField ? $position : $this->$positionField + 1;
            $end = $position < $this->$positionField ? $this->$positionField - 1 : $position;
            $affectedElements = static::withRestrictions($this->buildRestrictions())
                ->whereBetween($positionField, [$start, $end])
                ->get();
            $step = $position < $this->$positionField ? 1 : -1;
            foreach ($affectedElements as $affectedElement) {
                $affectedElement->getPivotModel()->update([$positionField => $affectedElement->$positionField + $step]);
            }
            $this->getPivotModel()->update([$positionField => $position]);
        });

        return $transactionResult === null;
    }

}