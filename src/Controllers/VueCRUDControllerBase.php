<?php

namespace Datalytix\VueCRUD\Controllers;

use Illuminate\Routing\Route;

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

        $elementData = static::getElements();
        $filters = method_exists($class, 'getVueCRUDIndexFilters')
            ? (object) $class::getVueCRUDIndexFilters()
            : (object) [];
        $viewData = [
            'elements'        => $elementData->elements,
            'counts'          => $elementData->counts,
            'columns'         => $class::getVueCRUDIndexColumns(),
            'filters'         => $filters,
            'buttons'         => $class::getVueCRUDModellistButtons(),
            'allowOperations' => $class::shouldVueCRUDOperationsBeDisplayed(),
        ];
        if (request()->isXmlHttpRequest()) {
            return response()->json($viewData);
        }
        $viewData = array_merge($viewData, $this->getCRUDUrls());

        return view('vendor.vue-crud.model-manager', $viewData);
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

        return view('vue-crud-manager', [
            'editDataUrl' => route($this->getRouteName('edit'), ['subject' => $subject->id]),
            'storeUrl'    => route($this->getRouteName('update'), ['subject' => $subject->id]),
            'indexUrl'    => route($this->getRouteName('index')),
            'formTitle'   => __(ucfirst(static::SUBJECT_NAME).' szerkesztése'),
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

        return view('vue-crud-manager', [
            'editDataUrl' => route($this->getRouteName('create')),
            'storeUrl'    => route($this->getRouteName('store')),
            'indexUrl'    => route($this->getRouteName('index')),
            'formTitle'   => __('Új '.static::SUBJECT_NAME),
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
            'indexUrl'   => $this->validateRoute($this->getRouteName('index')),
            'detailsUrl' => $this->validateRoute($this->getRouteName('details'), ['subject' => '___id___']),
            'storeUrl'   => $this->validateRoute($this->getRouteName('store')),
            'createUrl'  => $this->validateRoute($this->getRouteName('create')),
            'editUrl'    => $this->validateRoute($this->getRouteName('edit'), ['subject' => '___id___']),
            'deleteUrl'  => $this->validateRoute($this->getRouteName('delete'), ['subject' => '___id___']),
            'updateUrl'  => $this->validateRoute($this->getRouteName('update'), ['subject' => '___id___']),
        ];

        return $result;
    }

    public function setSuccessfulAddPopupMessageInSession()
    {
        $this->setPopupMessageInSession(__(ucfirst(static::SUBJECT_NAME).' '.__('added successfully')));
    }

    public function setSuccessfulModificationPopupMessageInSession()
    {
        $this->setPopupMessageInSession(__(ucfirst(static::SUBJECT_NAME).' '.__('modified successfully')));
    }

    public function setPopupMessageInSession($message, $class = 'success')
    {
        session()->put('popupMessage', $message);
        session()->put('popupMessageClass', 'alert-'.$class);
    }
}
