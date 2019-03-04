<?php
/**
 * Created by PhpStorm.
 * User: caleb
 * Date: 3/1/19
 * Time: 10:45 AM
 */

namespace Datalytix\VueCRUD\Indexfilters;


use Illuminate\Database\Eloquent\Builder;

interface IVueCRUDIndexfilter
{
    public function addFilterToQuery(Builder $query);

}