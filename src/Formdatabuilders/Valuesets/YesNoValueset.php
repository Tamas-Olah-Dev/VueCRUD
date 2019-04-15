<?php
/**
 * Created by PhpStorm.
 * User: caleb
 * Date: 4/15/19
 * Time: 3:07 PM
 */

namespace Datalytix\VueCRUD\Valuesets;


use Datalytix\KeyValue\canBeTurnedIntoKeyValueCollection;

class YesNoValueset
{
    use canBeTurnedIntoKeyValueCollection;

    public static function getKeyValueCollection()
    {
        return collect([
            0 => __('No'),
            1 => __('Yes')
        ]);
    }

    public static function getVueTreeselectCollection()
    {
        return self::getVueTreeselectValuesetFromKeyValueCollection(self::getKeyValueCollection());
    }
}