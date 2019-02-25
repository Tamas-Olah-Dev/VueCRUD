<?php

namespace App\Dataproviders;


interface IDataProvider
{
    function getBaseQuery(); //get the query for getting all records
    function getQuery(); //the final, paginated and filtered query
    function getCounts(); // numbers of all elements, filtered elements and the elements of the current page
    function getElements();
    static function getFilterFields();

}