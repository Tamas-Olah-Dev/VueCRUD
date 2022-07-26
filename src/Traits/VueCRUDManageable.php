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
        $subjectSlug = static::getSubjectSlug($subjectSlug);
        if ($nameSuffix != '') {
            $nameSuffix = substr($nameSuffix, 0, 1) != '_' ? '_'.$nameSuffix : $nameSuffix;
        }
        \Route::get(
            $urlBase.$subjectSlug,
            static::getVueCRUDControllerMethod('index').$nameSuffix
        )->name(static::getVueCRUDRouteName('index', $subjectSlug.$nameSuffix));
        \Route::get(
            $urlBase.$subjectSlug.'/{subject}/show',
            static::getVueCRUDControllerMethod('details').$nameSuffix
        )->name(static::getVueCRUDRouteName('details', $subjectSlug.$nameSuffix));
        \Route::get(
            $urlBase.$subjectSlug.'/new',
            static::getVueCRUDControllerMethod('create').$nameSuffix
        )->name(static::getVueCRUDRouteName('create', $subjectSlug.$nameSuffix));
        \Route::get(
            $urlBase.$subjectSlug.'/{subject}/edit',
            static::getVueCRUDControllerMethod('edit').$nameSuffix
        )->name(static::getVueCRUDRouteName('edit', $subjectSlug.$nameSuffix));
        \Route::post(
            $urlBase.$subjectSlug,
            static::getVueCRUDControllerMethod('store').$nameSuffix
        )->name(static::getVueCRUDRouteName('store', $subjectSlug.$nameSuffix));
        \Route::post(
            $urlBase.$subjectSlug.'/{subject}',
            static::getVueCRUDControllerMethod('update').$nameSuffix
        )->name(static::getVueCRUDRouteName('update', $subjectSlug.$nameSuffix));
        \Route::delete(
            $urlBase.$subjectSlug.'/{subject}',
            static::getVueCRUDControllerMethod('delete').$nameSuffix
        )->name(static::getVueCRUDRouteName('delete', $subjectSlug.$nameSuffix));
        \Route::post(
            $urlBase.$subjectSlug.'/{subject}/ajax',
            static::getVueCRUDControllerMethod('ajaxOperations').$nameSuffix
        )->name(static::getVueCRUDRouteName('ajax_operations', $subjectSlug.$nameSuffix));
        \Route::get(
            $urlBase.$subjectSlug.'/elements/list',
            static::getVueCRUDControllerMethod('list').$nameSuffix
        )->name(static::getVueCRUDRouteName('list', $subjectSlug.$nameSuffix));

    }

    public static function getVueCRUDControllerClassname()
    {
        return '\\App\\Http\\Controllers\\'.class_basename(static::class).'VueCRUDController';
    }

    public static function getVueCRUDFormdatabuilderClassname()
    {
        return '\\App\\Formdatabuilders\\'.class_basename(static::class).'VueCRUDFormdatabuilder';
    }

    public static function getVueCRUDControllerMethod($operation)
    {
        return static::getVueCRUDControllerClassname().'@'.$operation;
    }

    public static function getVueCRUDRouteName($operation, $subjectSlug = null, $nameSuffix = '')
    {
        $subjectSlug = static::getSubjectSlug($subjectSlug);

        return 'vuecrud_'.$subjectSlug.$nameSuffix.'_'.$operation;
    }

    public static function hasPositioningEnabled()
    {
        return method_exists(static::class, 'getRestrictingFields');
    }

    public static function getVueCRUDOptionalAjaxFunctions()
    {
        return array_merge(
            static::getVueCRUDMassFunctions(),
            static::getVueCRUDExportFunctions(),
            static::getVueCRUDAdditionalAjaxFunctions()
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
            'details' => static::buildButtonFromConfigData('vuecrud.buttons.details'),
            'edit'   => static::buildButtonFromConfigData('vuecrud.buttons.edit'),
            'delete' => static::buildButtonFromConfigData('vuecrud.buttons.delete'),
        ];

        if (method_exists(static::class, 'modifyModellistButtons')) {
            $result = static::modifyModellistButtons($result);
        }

        return $result;

    }

    public static function getModelManagerMainButtons()
    {
        if (defined('static::SUBJECT_NAME')) {
            $subjectName = config('vuecrud.translateConstants', false)
                ? ' '.mb_strtolower(__(static::SUBJECT_NAME))
                : ' '.mb_strtolower(static::SUBJECT_NAME);
        } else {
            $subjectName = '';
        }
        $result = [
            'add' => static::buildButtonFromConfigData('vuecrud.buttons.add', [
                'class' => 'btn btn-outline-primary', 'html' => __('New'),
            ]),
            'save' => static::buildButtonFromConfigData('vuecrud.buttons.save', [
                'class' => 'btn btn-outline-primary btn-block', 'html' => __('Save'),
            ]),
            'save_and_close' => static::buildButtonFromConfigData('vuecrud.buttons.save_and_close', [
                'class' => 'btn btn-outline-primary btn-block', 'html' => __('Save and close'),
            ]),
            'save_without_closing' => static::buildButtonFromConfigData('vuecrud.buttons.save_without_closing', [
                'class' => 'btn btn-outline-primary btn-block', 'html' => __('Save changes'),
            ]),
            'proceed' => static::buildButtonFromConfigData('vuecrud.buttons.proceed', [
                'class' => 'btn btn-outline-primary btn-block', 'html' => __('Proceed'),
            ]),
            'backToList' => static::buildButtonFromConfigData('vuecrud.buttons.backToList', [
                'class' => 'btn btn-outline-secondary', 'html' => __('Back to the list'),
            ]),
            'cancel' => static::buildButtonFromConfigData('vuecrud.buttons.cancel', [
                'class' => 'btn btn-outline-secondary btn-block', 'html' => __('Cancel'),
            ]),
            'resetFilters' => static::buildButtonFromConfigData('vuecrud.buttons.resetFilters', [
                'class' => 'btn btn-outline-secondary', 'html' => __('Reset'),
            ]),
            'prevPage' => static::buildButtonFromConfigData('vuecrud.buttons.prevPage', [
                'class' => 'btn btn-outline-secondary', 'html' => '←',
            ]),
            'nextPage' => static::buildButtonFromConfigData('vuecrud.buttons.nextPage', [
                'class' => 'btn btn-outline-secondary', 'html' => '→',
            ]),
            'fileUpload' => static::buildButtonFromConfigData('vuecrud.buttons.fileUpload', [
                'class' => 'btn btn-outline-primary', 'html' => '+',
            ]),
            'search' => static::buildButtonFromConfigData('vuecrud.buttons.search', [
                'class' => 'btn btn-outline-secondary', 'html' => __('Search'),
            ]),
            'confirmDeletion' => static::buildButtonFromConfigData('vuecrud.buttons.confirmDeletion', [
                'class' => 'btn btn-danger', 'html' => __('Yes'),
            ]),
            'cancelDeletion' => static::buildButtonFromConfigData('vuecrud.buttons.cancelDeletion', [
                'class' => 'btn btn-secondary', 'html' => __('No'),
            ]),
            'massOperations' => static::buildButtonFromConfigData('vuecrud.buttons.massOperations', [
                'class' => 'btn btn-primary', 'html' => __('Operations on the selected items'),
            ]),
            'exportOperations' => static::buildButtonFromConfigData('vuecrud.buttons.exportOperations', [
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
        $subjectSlug = $subjectSlug === null ? static::getSubjectSlug() : $subjectSlug;
        return route(static::getVueCRUDRouteName('index', $subjectSlug, $nameSuffix), $filters);
    }


    /**
     * @param $indexParameters
     * @return string
     * When we want an url to manage sub-items of an item, this method can generate
     * a string that can be passed in that url as a 'referer' query parameter.
     * This allows for showing a breadcrumb-like back link on the management page of the sub-items
     *
     * Example: we have pages with blocks. Blocks have a page_id, and for that we set up an invisible select
     * filter so that we can generate an url for every page that leads to the blocks of the page
     * (vuecrud_block_index?page_id=xx)
     *
     * If we call this method and add to the link above a 'referer' parameter with its value,
     * we can render a link that leads back to the pages management page.
     *
     * We can pass in mandatory filter parameters, so if the blocks have a similar sub-item link to
     * for example items, we can pass in ['page_id'], and the result will point to the
     * index of block belonging to the page we navigated here from.
     */
    public static function getVueCRUDBackreferenceParameterValue($indexParameters)
    {
        $params = [];
        foreach ($indexParameters as $indexParameter) {
            if (request()->has($indexParameter)) {
                $params[$indexParameter] = request()->get($indexParameter);
            }
        }
        return base64_encode(serialize([
            'url' => route('vuecrud_'.static::SUBJECT_SLUG.'_index', $params),
            'label' => __('Back to').' '.mb_strtolower(__(static::SUBJECT_NAME_PLURAL))
        ]));
    }

    public static function getVueCRUDParentIndexLink()
    {
        // a customization hook that allows specifying the backlink explained in the
        // comments of getVueCRUDBackreferenceParameterValue
        // if there is no referer, we can still generate link data here, and the controller
        // will be able to use it. The same arra format has to be used
        // as in getVueCRUDBackreferenceParameterValue, but without b64 encoding and serialization.

        return null;
    }

    public function getVuecrudDisabledOperationButtonsAttribute()
    {
        // by overriding this on a model we can tailor the operation buttons to be shown
        // on a given element
        // and by providing a method that can be implemented we can use a system-wide trait
        if (method_exists($this, 'getVuecrudDisabledButtons')) {
            return $this->getVuecrudDisabledButtons();
        }
        return [];
    }
}
