<?php

namespace App\Dataproviders;


interface IDataProvider
{
    function getBaseQuery();
    function getQuery();
    function getCounts();
    function getElements();
    static function getFilterFields();

}