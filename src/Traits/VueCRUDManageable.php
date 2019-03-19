<?php
/**
 * Created by PhpStorm.
 * User: caleb
 * Date: 2/21/19
 * Time: 11:49 AM
 */

namespace Datalytix\VueCRUD\Traits;

use Illuminate\Support\Str;

trait VueCRUDManageable
{
    protected $allowOperations = true;
    protected $buttons = null;

    public static function getSubjectSlug($default = null)
    {
        return $default == null
            ? static::SUBJECT_SLUG
            : $default;
    }

    public static function setVueCRUDRoutes($subjectSlug = null, $urlBase = '/')
    {
        $subjectSlug = self::getSubjectSlug($subjectSlug);

        \Route::get(
            $urlBase.$subjectSlug,
            self::getVueCRUDControllerMethod('index')
        )->name(self::getVueCRUDRouteName('index', $subjectSlug));
        \Route::get(
            $urlBase.$subjectSlug.'/{subject}/show',
            self::getVueCRUDControllerMethod('details')
        )->name(self::getVueCRUDRouteName('details', $subjectSlug));
        \Route::get(
            $urlBase.$subjectSlug.'/new',
            self::getVueCRUDControllerMethod('create')
        )->name(self::getVueCRUDRouteName('create', $subjectSlug));
        \Route::get(
            $urlBase.$subjectSlug.'/{subject}/edit',
            self::getVueCRUDControllerMethod('edit')
        )->name(self::getVueCRUDRouteName('edit', $subjectSlug));
        \Route::post(
            $urlBase.$subjectSlug,
            self::getVueCRUDControllerMethod('store')
        )->name(self::getVueCRUDRouteName('store', $subjectSlug));
        \Route::post(
            $urlBase.$subjectSlug.'/{subject}',
            self::getVueCRUDControllerMethod('update')
        )->name(self::getVueCRUDRouteName('update', $subjectSlug));
        \Route::delete(
            $urlBase.$subjectSlug.'/{subject}',
            self::getVueCRUDControllerMethod('delete')
        )->name(self::getVueCRUDRouteName('delete', $subjectSlug));
        \Route::post(
            $urlBase.$subjectSlug.'/{subject}/ajax',
            self::getVueCRUDControllerMethod('ajaxOperations')
        )->name(self::getVueCRUDRouteName('ajax_operations', $subjectSlug));

    }

    public static function getVueCRUDControllerClassname()
    {
        return 'App\\Http\\Controllers\\'.class_basename(static::class).'VueCRUDController';
    }

    public static function getVueCRUDFormdatabuilderClassname()
    {
        return 'App\\Formdatabuilders\\'.class_basename(static::class).'VueCRUDFormdatabuilder';
    }

    public static function getVueCRUDControllerMethod($operation)
    {
        return class_basename(static::class).'VueCRUDController@'.$operation;
    }

    protected static function getVueCRUDRouteName($operation, $subjectSlug = null)
    {
        $subjectSlug = self::getSubjectSlug($subjectSlug);

        return 'vuecrud_'.$subjectSlug.'_'.$operation;
    }

    /** The following methods provide sensible defaults,
     * but they are to be overridden as needed.
     * */

    public static function shouldVueCRUDOperationsBeDisplayed()
    {
        // authorization logic can be implemented here
        return true;
    }

    public static function getVueCRUDModellistButtons()
    {
        /**
         * details, edit and delete are built-in functions in the ModelManager component
         * we can also use special buttons that activate Vue.js components if we add them this way:
         *  'datepicker' => [
         *      'class'       => 'btn btn-outline-danger',
         *      'html'        => __('Datepicker')',
         *      'componentName' => 'datePicker',
         *   ]
         */
        return [
            'details' => [
                'class'       => 'btn btn-outline-primary',
                'html'        => __('Details'),
            ],
            'edit'   => [
                'class'       => 'btn btn-outline-secondary',
                'html'        => __('Edit'),
            ],
            'delete' => [
                'class'       => 'btn btn-outline-danger',
                'html'        => __('Delete'),
            ],
        ];
    }

    // an array of column head labels, keyed by the related field name on the model
    // e.g ['id' => 'ID']
    abstract public static function getVueCRUDIndexColumns();

    // an array of property names the query can be sorted by, keyed by the related field name on the model
    // e.g ['id' => 'ID']
    abstract public static function getVueCRUDSortingIndexColumns();

    // an array of description title labels, keyed by the related field name on the model
    // e.g ['id' => 'ID']
    abstract public function getVueCRUDDetailsFields();

    // a collection of index filters implementing the IVueCRUDIndexfilter interface,
    // keyed by the property name
    abstract public static function getVueCRUDIndexFilters();

}