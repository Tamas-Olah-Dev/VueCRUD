<?php

namespace Datalytix\VueCRUD\Traits;

trait hasPosition
{
    //restrictions allow for using multiple position sets in one database
    //for example if we want users handled differently depending on their role_id property,
    //we can use ['role_id' => %DESIREDVALUE%] as $restrictions and the operations will only apply to those
    //multiple restrictions can be specified in one array

    public function scopeWithRestrictions($query, $restrictions = null)
    {
        $restrictions = $restrictions === null ? $this->buildRestrictions() : $restrictions;
        return $query->when(count($restrictions) > 0, function ($query) use ($restrictions) {
            foreach ($restrictions as $field => $value) {
                $query = $query->where($field, '=', $value);
            }
        });
    }

    public static function getFirstAvailablePosition($restrictions = [])
    {
        $positionField = static::getPositionField();
        $result = self::query()
            ->withRestrictions($restrictions)
            ->max($positionField);

        return intval($result) + 1;
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

    public static function allByPosition($restrictions = [], $direction = 'asc')
    {
        $positionField = static::getPositionField();
        return self::where('id', '>', 0)
            ->withRestrictions($restrictions)
            ->orderBy($positionField, $direction)
            ->get();
    }

    public static function allByPositionPaginated($itemsPerPage = 10)
    {
        $positionField = static::getPositionField();
        return self::where('id', '>', 0)
            ->orderBy($positionField, 'asc')
            ->paginate($itemsPerPage);
    }

    public function updatePositionOfOtherElementsBeforeDelete()
    {
        $transactionResult = \DB::transaction(function () {
            $positionField = static::getPositionField();
            $itemsAbove = self::where($positionField, '>', $this->$positionField)
                ->withRestrictions($this->buildRestrictions())
                ->get();

            foreach ($itemsAbove as $item) {
                $item->update([
                    $positionField => intval($item->$positionField) - 1,
                ]);
            }
        });

        return $transactionResult === null;
    }

    public function findPreviousElementByPosition()
    {
        $positionField = static::getPositionField();
        return self::withRestrictions($this->buildRestrictions())
            ->where($positionField, '<', $this->$positionField)
            ->orderBy($positionField, 'desc')
            ->limit(1)
            ->first();
    }

    public function findNextElementByPosition()
    {
        $positionField = static::getPositionField();
        return self::withRestrictions($this->buildRestrictions())
            ->where($positionField, '>', $this->$positionField)
            ->orderBy($positionField, 'asc')
            ->limit(1)
            ->first();
    }

    public function moveToPosition($position)
    {
        $positionField = static::getPositionField();

        if ($position == $this->$positionField) {
            return false;
        }
        $transactionResult = \DB::transaction(function() use ($position, $positionField) {
            $max = self::getFirstAvailablePosition($this->buildRestrictions());
            if ($position >= $max) {
                $position = $max - 1;
            }
            if ($position < 1) {
                $position = 1;
            }
            $start = $position < $this->$positionField ? $position : $this->$positionField + 1;
            $end = $position < $this->$positionField ? $this->$positionField - 1 : $position;
            $affectedElements = self::withRestrictions($this->buildRestrictions())
                ->whereBetween($positionField, [$start, $end])
                ->get();
            $step = $position < $this->$positionField ? 1 : -1;
            foreach ($affectedElements as $affectedElement) {
                $affectedElement->update([$positionField => $affectedElement->$positionField + $step]);
            }
            $this->update([$positionField => $position]);
        });

        return $transactionResult === null;
    }

    public function exchangePositionWithElementInPosition($position)
    {
        $element = self::inPosition($position)
            ->withRestrictions($this->buildRestrictions())
            ->first();
        if ($element === null) {
            return false;
        }

        return $this->exchangePositionWithElement($element);
    }

    public function exchangePositionWithElement($element)
    {
        if ($element === null) {
            return false;
        }
        $transactionResult = \DB::transaction(function() use ($element) {
            $positionField = static::getPositionField();
            $position = $element->$positionField;
            $element->update([$positionField => $this->$positionField]);
            $this->update([$positionField => $position]);
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
        foreach (self::getRestrictingFields() as $field) {
            $result[$field] = $this->$field;
        }

        return $result;
    }

    //override this if instead of 'position' you want to use some other field
    public static function getPositionField()
    {
        return 'position';
    }


    /**
     * @return array
     * this function returns a list of fields that are used as restrictions when changing position
     * for example if it returns ['role_id'], every non-static positioning function will
     * only look for elements with the same role_id as the subject
     */
    abstract public static function getRestrictingFields();
}
