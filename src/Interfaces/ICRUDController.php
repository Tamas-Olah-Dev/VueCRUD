<?php


namespace OlahTamas\VueCRUD\Interfaces;


interface ICRUDController
{
    function getIndexColumns();
    function getDetailsFields();
    function getElements();
}