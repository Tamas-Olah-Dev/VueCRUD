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
        $result = self::query()
            ->withRestrictions($restrictions)
            ->max('position');

        return intval($result) + 1;
    }

    public function scopeOrderByPosition($query, $direction = 'asc')
    {
        return $query->orderBy('position', $direction);
    }

    public function scopeInPosition($query, $position)
    {
        return $query->where('position', '=', $position);
    }

    public static function allByPosition($restrictions = [])
    {
        return self::where('id', '>', 0)
            ->withRestrictions($restrictions)
            ->orderBy('position', 'asc')
            ->get();
    }

    public static function allByPositionPaginated($itemsPerPage = 10)
    {
        return self::where('id', '>', 0)->orderBy('position', 'asc')->paginate($itemsPerPage);
    }

    public function updatePositionOfOtherElementsBeforeDelete()
    {
        $transactionResult = \DB::transaction(function () {
            $itemsAbove = self::where('position', '>', $this->position)
                ->withRestrictions($this->buildRestrictions())
                ->get();

            foreach ($itemsAbove as $item) {
                $item->update([
                    'position' => intval($item->position) - 1,
                ]);
            }
        });

        return $transactionResult === null;
    }

    public function findPreviousElementByPosition()
    {
        return self::withRestrictions($this->buildRestrictions())
            ->where('position', '<', $this->position)
            ->orderBy('position', 'desc')
            ->limit(1)
            ->first();
    }

    public function findNextElementByPosition()
    {
        return self::withRestrictions($this->buildRestrictions())
            ->where('position', '>', $this->position)
            ->orderBy('position', 'asc')
            ->limit(1)
            ->first();
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
            $position = $element->position;
            $element->update(['position' => $this->position]);
            $this->update(['position' => $position]);
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


    /**
     * @return array
     * this function returns a list of fields that are used as restrictions when changing position
     * for example if it returns ['role_id'], every non-static positioning function will
     * only look for elements with the same role_id as the subject
     */
    abstract public static function getRestrictingFields();
}
