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

    public static function setVueCRUDRoutes($subjectSlug = null, $urlBase = '/', $nameSuffix = '')
    {
        $subjectSlug = self::getSubjectSlug($subjectSlug);
        if ($nameSuffix != '') {
            $nameSuffix = substr($nameSuffix, 0, 1) != '_' ? '_'.$nameSuffix : $nameSuffix;
        }
        \Route::get(
            $urlBase.$subjectSlug,
            self::getVueCRUDControllerMethod('index').$nameSuffix
        )->name(self::getVueCRUDRouteName('index', $subjectSlug.$nameSuffix));
        \Route::get(
            $urlBase.$subjectSlug.'/{subject}/show',
            self::getVueCRUDControllerMethod('details').$nameSuffix
        )->name(self::getVueCRUDRouteName('details', $subjectSlug.$nameSuffix));
        \Route::get(
            $urlBase.$subjectSlug.'/new',
            self::getVueCRUDControllerMethod('create').$nameSuffix
        )->name(self::getVueCRUDRouteName('create', $subjectSlug.$nameSuffix));
        \Route::get(
            $urlBase.$subjectSlug.'/{subject}/edit',
            self::getVueCRUDControllerMethod('edit').$nameSuffix
        )->name(self::getVueCRUDRouteName('edit', $subjectSlug.$nameSuffix));
        \Route::post(
            $urlBase.$subjectSlug,
            self::getVueCRUDControllerMethod('store').$nameSuffix
        )->name(self::getVueCRUDRouteName('store', $subjectSlug.$nameSuffix));
        \Route::post(
            $urlBase.$subjectSlug.'/{subject}',
            self::getVueCRUDControllerMethod('update').$nameSuffix
        )->name(self::getVueCRUDRouteName('update', $subjectSlug.$nameSuffix));
        \Route::delete(
            $urlBase.$subjectSlug.'/{subject}',
            self::getVueCRUDControllerMethod('delete').$nameSuffix
        )->name(self::getVueCRUDRouteName('delete', $subjectSlug.$nameSuffix));
        \Route::post(
            $urlBase.$subjectSlug.'/{subject}/ajax',
            self::getVueCRUDControllerMethod('ajaxOperations').$nameSuffix
        )->name(self::getVueCRUDRouteName('ajax_operations', $subjectSlug.$nameSuffix));

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

    protected static function getVueCRUDRouteName($operation, $subjectSlug = null, $nameSuffix = '')
    {
        $subjectSlug = self::getSubjectSlug($subjectSlug);

        return 'vuecrud_'.$subjectSlug.$nameSuffix.'_'.$operation;
    }

    public static function hasPositioningEnabled()
    {
        return method_exists(static::class, 'getRestrictingFields');
    }

    public static function getVueCRUDOptionalAjaxFunctions()
    {
        return array_merge(
            self::getVueCRUDMassFunctions(),
            self::getVueCRUDExportFunctions(),
            self::getVueCRUDAdditionalAjaxFunctions()
        );
    }

    /** The following methods provide sensible defaults,
     * but they are to be overridden as needed.
     * */


    public static function getVueCRUDAdditionalAjaxFunctions()
    {
        return [];
    }

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

    // an array of functionalities that can be applied to multiple selected items.
    // if this array is not empty, a selection column will be present on the model list
    // keys are controller method names, while the values are arrays containing the following values:
    //  'type' => 'method' or 'component', depending on whether we want a simple ajax call to a controller method or to show a custom Vue.js component
    //  'label' => The label to show on the button
    //  'confirm' => null or a confirmation message to be shown before running the action
    //  'component' => null, or a component name,
    //  'componentProps' => null or an array of props passed to the component
    public static function getVueCRUDMassFunctions()
    {
        return [];
    }

    // an array of export functions, keyed by the controller method
    // exportCsv and exportHTML are two predefined methods that can be added with
    // a label of our choosing and can be used instantly
    // exportXlsx is also implemented, but it needs the phpspreadsheet package
    // if any of these methods are implemented on the model, the export content
    // will be provided by them instead of the controller's built-in methods
    public static function getVueCRUDExportFunctions()
    {
        return [];
    }

    // by overriding this, we can set up custom column structure for exports
    // by default it uses the index column definitions
    public static function getVueCRUDExportColumns()
    {
        return static::getVueCRUDIndexColumns();
    }

    public static function getVueCRUDHTMLExportTableStyle()
    {
        return [
            'table' => 'border: 1px solid darkgrey; min-width: 600px;',
            'th' => '',
            'tr' => '',
            'td' => 'border: 1px solid lightgrey; padding: 3px',
        ];
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
        $result = [
            'details' => self::buildButtonFromConfigData('vuecrud.buttons.details'),
            'edit'   => self::buildButtonFromConfigData('vuecrud.buttons.edit'),
            'delete' => self::buildButtonFromConfigData('vuecrud.buttons.delete'),
        ];

        if (method_exists(static::class, 'modifyModellistButtons')) {
            $result = static::modifyModellistButtons($result);
        }

        return $result;

    }

    public static function getModelManagerMainButtons()
    {
        if (defined('self::SUBJECT_NAME')) {
            $subjectName = config('vuecrud.translateConstants', false)
                ? ' '.mb_strtolower(__(self::SUBJECT_NAME))
                : ' '.mb_strtolower(self::SUBJECT_NAME);
        } else {
            $subjectName = '';
        }
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
            'massOperations' => self::buildButtonFromConfigData('vuecrud.buttons.massOperations', [
                'class' => 'btn btn-primary', 'html' => __('Operations on the selected items'),
            ]),
            'exportOperations' => self::buildButtonFromConfigData('vuecrud.buttons.exportOperations', [
                'class' => 'btn btn-primary', 'html' => __('Export items'),
            ]),
        ];

        $result['add']['html'] = $result['add']['html'].$subjectName;

        if (method_exists(static::class, 'modifyModelManagerMainButtons')) {
            $result = static::modifyModelManagerMainButtons($result);
        }

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

    public static function getIdProperty()
    {
        return (new static())->getKeyName();
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
    // (so that if it's an array for combined search fields, it will be using the same name).
    //
    // If the $tab public property is set on the filters, the filters will be grouped into tabs.
    // The labels of the tabs will be the $tab property's value
    abstract public static function getVueCRUDIndexFilters();


    public static function getVueCRUDIndexLink($filters = [], $subjectSlug = null, $nameSuffix = '')
    {
        $subjectSlug = $subjectSlug === null ? self::getSubjectSlug() : $subjectSlug;
        return route(self::getVueCRUDRouteName('index', $subjectSlug, $nameSuffix), $filters);
    }
}
