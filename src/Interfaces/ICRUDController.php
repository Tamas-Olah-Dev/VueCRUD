<?php


namespace App\Interfaces;


interface ICRUDController
{
    function getIndexColumns();
    function getDetailsFields();
    function getElements();
}