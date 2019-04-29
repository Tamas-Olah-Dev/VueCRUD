<?php

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

    public static function hasPositioningEnabled()
    {
        return method_exists(static::class, 'getRestrictingFields');
    }

    /** The following methods provide sensible defaults,
     * but they are to be overridden as needed.
     * */

    public static function shouldVueCRUDOperationsBeDisplayed()
    {
        // authorization logic can be implemented here
        return true;
    }

    public static function shouldVueCRUDAddButtonBeDisplayed()
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
            'details' => self::buildButtonFromConfigData('vuecrud.buttons.details'),
            'edit'   => self::buildButtonFromConfigData('vuecrud.buttons.edit'),
            'delete' => self::buildButtonFromConfigData('vuecrud.buttons.delete'),
        ];
    }

    public static function getModelManagerMainButtons()
    {
        $subjectName = defined('self::SUBJECT_NAME') ? ' '.mb_strtolower(self::SUBJECT_NAME) : '';
        $result = [
            'add' => self::buildButtonFromConfigData('vuecrud.buttons.add', [
                'class' => 'btn btn-outline-primary', 'html' => __('New'),
            ]),
            'save' => self::buildButtonFromConfigData('vuecrud.buttons.save', [
                'class' => 'btn btn-outline-primary btn-block', 'html' => __('Save'),
            ]),
            'proceed' => self::buildButtonFromConfigData('vuecrud.buttons.proceed', [
                'class' => 'btn btn-outline-primary btn-block', 'html' => __('Proceed'),
            ]),
            'backToList' => self::buildButtonFromConfigData('vuecrud.buttons.backToList', [
                'class' => 'btn btn-outline-secondary', 'html' => __('Back to the list'),
            ]),
            'cancel' => self::buildButtonFromConfigData('vuecrud.buttons.cancel', [
                'class' => 'btn btn-outline-secondary btn-block', 'html' => __('Cancel'),
            ]),
            'resetFilters' => self::buildButtonFromConfigData('vuecrud.buttons.resetFilters', [
                'class' => 'btn btn-outline-secondary', 'html' => __('Reset'),
            ]),
            'prevPage' => self::buildButtonFromConfigData('vuecrud.buttons.prevPage', [
                'class' => 'btn btn-outline-secondary', 'html' => '←',
            ]),
            'nextPage' => self::buildButtonFromConfigData('vuecrud.buttons.nextPage', [
                'class' => 'btn btn-outline-secondary', 'html' => '→',
            ]),
            'fileUpload' => self::buildButtonFromConfigData('vuecrud.buttons.fileUpload', [
                'class' => 'btn btn-outline-primary', 'html' => '+',
            ]),
            'search' => self::buildButtonFromConfigData('vuecrud.buttons.search', [
                'class' => 'btn btn-outline-secondary', 'html' => __('Search'),
            ]),
            'confirmDeletion' => self::buildButtonFromConfigData('vuecrud.buttons.confirmDeletion', [
                'class' => 'btn btn-danger', 'html' => __('Yes'),
            ]),
            'cancelDeletion' => self::buildButtonFromConfigData('vuecrud.buttons.cancelDeletion', [
                'class' => 'btn btn-secondary', 'html' => __('No'),
            ]),
        ];

        $result['add']['html'] = $result['add']['html'].$subjectName;

        return $result;
    }

    public static function buildButtonFromConfigData($configPath, $default = [])
    {
        $data = config($configPath, $default);
        if (isset($data['translationLabel'])) {
            $data['html'] = str_replace('__label__', __($data['translationLabel']), $data['html']);
        }

        return $data;
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
    // keyed by the property name generated by VueCRUDIndexfilterBase::buildPropertyName()
    // (so that if it's an array for combined search fields, it will be using the same name)
    abstract public static function getVueCRUDIndexFilters();

}