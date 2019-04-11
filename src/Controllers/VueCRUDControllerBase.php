<?php

namespace Datalytix\VueCRUD\Controllers;

use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class VueCRUDControllerBase
{
    public function getSubject($id)
    {
        $class = static::SUBJECT_CLASS;

        return $class::find($id);
    }

    public function index()
    {
        $class = static::SUBJECT_CLASS;

        $positionedView = $class::hasPositioningEnabled()
            && $this->filtersRequirePositionedView();
        if ($positionedView) {
            request()->merge([
                'sorting_field' => $class::getPositionField(),
                'sorting_direction' => 'asc'
            ]);
        }
        $elementData = static::getElements();
        $filters = method_exists($class, 'getVueCRUDIndexFilters')
            ? (object) $class::getVueCRUDIndexFilters()
            : (object) [];

        $viewData = [
            'title' => $this->getSubjectNamePlural(),
            'pageTitleContent' => $this->getSubjectNamePlural(),
            'pageTitle' => config('app.name').' - '.$this->getSubjectNamePlural(),
            'elements'         => $elementData->elements,
            'counts'           => $elementData->counts,
            'columns'          => $this->getIndexColumns($positionedView),
            'sortingColumns'   => $this->getSortingColumns($positionedView),
            'sortingField'     => $elementData->sortingField,
            'sortingDirection' => $elementData->sortingDirection,
            'filters'          => $filters,
            'buttons'          => $this->getModellistButtons($positionedView),
            'mainButtons' => $class::getModelManagerMainButtons(),
            'allowOperations'  => $class::shouldVueCRUDOperationsBeDisplayed(),
            'positionedView' => $positionedView,
        ];
        if (request()->isXmlHttpRequest()) {
            return response()->json($viewData);
        }
        $viewData = array_merge($viewData, $this->getCRUDUrls());

        return view($this->getModelManagerViewName(), $viewData);
    }

    protected function getModellistButtons($positionedView)
    {
        $class = static::SUBJECT_CLASS;
        if ($positionedView) {
            $base = $class::getVueCRUDModellistButtons();
            $base['moveUp'] = $class::buildButtonFromConfigData('vuecrud.buttons.moveUp');
            $base['moveDown'] = $class::buildButtonFromConfigData('vuecrud.buttons.moveDown');
            return $base;
        } else {
            return $class::getVueCRUDModellistButtons();
        }

    }

    protected function getIndexColumns($positionedView)
    {
        $class = static::SUBJECT_CLASS;
        if ($positionedView) {
            return array_merge([$class::getPositionField() => __('Position')], $class::getVueCRUDIndexColumns());
        } else {
            return $class::getVueCRUDIndexColumns();
        }
    }

    protected function getSortingColumns($positionedView)
    {
        $class = static::SUBJECT_CLASS;
        if ($positionedView) {
            return [];
        } else {
            return $class::getVueCRUDSortingIndexColumns();
        }
    }

    public function details($subject)
    {
        $subject = $this->getSubject($subject);
        if ($subject === null) {
            abort(404);
        }
        if (method_exists($this, 'addAdditionalDetails')) {
            $this->addAdditionalDetails($subject);
        }

        $fields = $subject->getVueCRUDDetailsFields();

        return response()->json([
            'model'  => $subject,
            'fields' => $fields,
        ]);
    }

    public function edit($subject)
    {
        $subject = $this->getSubject($subject);
        $formdatabuilderClass = $subject::getVueCRUDFormdatabuilderClassname();
        $formDataBuilder = new $formdatabuilderClass($subject);
        if (request()->isXmlHttpRequest()) {
            return response($formDataBuilder->buildJson());
        }

        return view($this->getModelManagerViewName(), [
            'editDataUrl' => route($this->getRouteName('edit'), ['subject' => $subject->id]),
            'storeUrl'    => route($this->getRouteName('update'), ['subject' => $subject->id]),
            'indexUrl'    => route($this->getRouteName('index')),
            'formTitle'   => __('Edit').' '.ucfirst($this->getSubjectName()),
        ]);
    }

    public function create()
    {
        $class = static::SUBJECT_CLASS;
        $formdatabuilderClass = $class::getVueCRUDFormdatabuilderClassname();
        $formDataBuilder = new $formdatabuilderClass();
        if (request()->isXmlHttpRequest()) {
            return response($formDataBuilder->buildJson());
        }

        return view($this->getModelManagerViewName(), [
            'editDataUrl' => route($this->getRouteName('create')),
            'storeUrl'    => route($this->getRouteName('store')),
            'indexUrl'    => route($this->getRouteName('index')),
            'formTitle'   => __('New').$this->getSubjectName(),
        ]);
    }

    public function getRouteName($operation)
    {
        $class = static::SUBJECT_CLASS;

        return $class::getVueCRUDRouteName($operation);
    }

    public function delete($subject)
    {
        $element = $this->getSubject($subject);
        if (method_exists($element, 'remove')) {
            $result = $element->remove();
        } else {
            $result = $element->delete();
        }
        if ($result) {
            return response(200);
        }

        abort(419);
    }

    protected function validateRoute($routeName, $parameters = [])
    {
        return \Route::has($routeName)
            ? route($routeName, $parameters)
            : '';
    }

    protected function getCRUDUrls()
    {
        $result = [
            'indexUrl'          => $this->validateRoute($this->getRouteName('index')),
            'detailsUrl'        => $this->validateRoute($this->getRouteName('details'), ['subject' => '___id___']),
            'storeUrl'          => $this->validateRoute($this->getRouteName('store')),
            'createUrl'         => $this->validateRoute($this->getRouteName('create')),
            'editUrl'           => $this->validateRoute($this->getRouteName('edit'), ['subject' => '___id___']),
            'deleteUrl'         => $this->validateRoute($this->getRouteName('delete'), ['subject' => '___id___']),
            'updateUrl'         => $this->validateRoute($this->getRouteName('update'), ['subject' => '___id___']),
            'ajaxOperationsUrl' => $this->validateRoute($this->getRouteName('ajax_operations'),
                ['subject' => '___id___']),
        ];

        return $result;
    }

    public function setSuccessfulAddPopupMessageInSession()
    {
        $this->setPopupMessageInSession(__(ucfirst($this->getSubjectName()).' '.__('added successfully')));
    }

    public function setSuccessfulModificationPopupMessageInSession()
    {
        $this->setPopupMessageInSession(__(ucfirst($this->getSubjectName()).' '.__('modified successfully')));
    }

    public function setPopupMessageInSession($message, $class = 'success')
    {
        session()->put('popupMessage', $message);
        session()->put('popupMessageClass', 'alert-'.$class);
    }

    protected function getAllowedAjaxOperations()
    {
        return [
            'trixStoreAttachment',
            'trixRemoveAttachment',
            'storePublicAttachment',
            'removePublicAttachment',
            'trixGeneratePreview',
            'move',
            'moveTo',
        ];
    }

    public function ajaxOperations($subject)
    {
        $allowedActions = $this->getAllowedAjaxOperations();
        if (array_search(request()->get('action'), $allowedActions) === false) {
            abort(404);
        }

        return $this->{request()->get('action')}();
    }

    protected function trixStoreAttachment()
    {
        $filename = $this->saveUploadedFileToPublic();

        return response()->json(['url' => asset('storage/'.$filename)]);
    }

    protected function trixRemoveAttachment()
    {
        return $this->removePublicAttachment();
    }

    protected function storePublicAttachment()
    {
        $processedFilename = $this->saveUploadedFileToPublic();
        if (method_exists($this, 'processUploadedFile')) {
            return response()->json([
                'url' => $this->processUploadedFile($processedFilename),
            ]);
        } else {
            return response()->json([
                'url' => asset('storage'
                    .DIRECTORY_SEPARATOR
                    .'attachments'
                    .DIRECTORY_SEPARATOR
                    .basename($processedFilename)),
            ]);
        }
    }

    protected function removePublicAttachment()
    {
        $file = basename(request()->get('url'));
        if (method_exists($this, 'processRemovedFile')) {
            $this->processRemovedFile($file);
        }
        if (\Storage::disk('public')->exists('attachments'.DIRECTORY_SEPARATOR.$file)) {
            \Storage::disk('public')->delete('attachments'.DIRECTORY_SEPARATOR.$file);
        }

        return response('OK');
    }

    protected function saveUploadedFileToPublic()
    {
//        $originalFileInfo = pathinfo(request()->get('fileName'));
//        if (!isset($originalFileInfo['extension'])) {
//            $originalFileInfo['extension'] = '';
//        }
        $filename = $this->generatePublicFilename(
            request()->get('fileName')
        );
        \Storage::disk('public')->put(
            $filename,
            base64_decode(Str::after(request()->get('fileData'), ';base64,'))
        );

        return storage_path('app'
            .DIRECTORY_SEPARATOR
            .'public'
            .DIRECTORY_SEPARATOR
            .'attachments'
            .DIRECTORY_SEPARATOR
            .basename($filename));
    }

    protected function generatePublicFilename($originalName)
    {
        $basePath = 'attachments'.DIRECTORY_SEPARATOR;
        $filename = $basePath.Str::random(32).'___'.$originalName;
        while (\Storage::disk('public')->exists($filename)) {
            $filename = $basePath.Str::random(32).'___'.$originalName;
        }

        return $filename;
    }

    public function cleanRandomizationStringFromUploadFilename($filename)
    {
        return \Str::after('___', $filename);
    }

    protected function getModelManagerViewName()
    {
        return defined('static::CUSTOM_VIEW_PATH')
            ? static::CUSTOM_VIEW_PATH
            : config('vuecrud.vueCrudDefaultView', 'vendor.vue-crud.model-manager');
    }

    public function trixGeneratePreview()
    {
        return method_exists($this, 'renderPreview')
            ? $this->renderPreview()
            : $this->defaultPreview();
    }

    public function defaultPreview()
    {
        return response(request()->get('content'));
    }


    public function move()
    {
        $result = false;
        $current = $this->getSubject(request()->get('id'));
        if (request()->has('direction')) {
            if (request()->get('direction') == 1) {
                $result = $current->moveDown();
            } else {
                $result = $current->moveUp();
            }
        } else {
            if (request()->has('position')) {
                $result = $current->moveToPosition(request()->get('position'));
            }
        }

        return $result ? response('OK') : response('Hiba történt', 422);
    }

    protected function filtersRequirePositionedView()
    {
        $class = static::SUBJECT_CLASS;
        if (!$class::hasPositioningEnabled()) {
            return false;
        }
        $validNullResponses = [null, 0, -1, ''];
        $filters = $class::getVueCRUDIndexFilters();
        foreach ($class::getRestrictingFields() as $restrictingField) {
            if (!isset($filters[$restrictingField])) {
                return false;
            }
            if (array_search(request()->get($restrictingField), $validNullResponses) !== false) {
                return false;
            }
        }

        return true;
    }

    protected function getSubjectName()
    {
        $class = static::SUBJECT_CLASS;
        if (defined($class.'::SUBJECT_NAME')) {
            return $class::SUBJECT_NAME;
        }
        return static::SUBJECT_NAME;
    }

    protected function getSubjectNamePlural()
    {
        $class = static::SUBJECT_CLASS;
        if (defined($class.'::SUBJECT_NAME_PLURAL')) {
            return $class::SUBJECT_NAME_PLURAL;
        }
        return static::SUBJECT_NAME;
    }

    protected function getModificationResponse($subject)
    {
        if (request('respondWith', 'url') == 'url') {
            return route($this->getRouteName('index'));
        }

        return $subject;
    }
}
