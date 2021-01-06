<?php

namespace Datalytix\VueCRUD\Controllers;

use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Style\Color;

class VueCRUDControllerBase
{
    public function getSubject($id)
    {
        $class = static::SUBJECT_CLASS;

        return $class::find($id);
    }

    public function index($nameSuffix = '')
    {
        $class = static::SUBJECT_CLASS;

        $positionedView = $class::hasPositioningEnabled()
            && $this->filtersRequirePositionedView();
        if ($positionedView) {
            request()->merge([
                'sorting_field'     => $class::getPositionField(),
                'sorting_direction' => 'asc',
            ]);
        } else {
            if (! request()->has('sorting_field')) {
                if (method_exists($class, 'getVueCRUDDefaultSortingData')) {
                    request()->merge($class::getVueCRUDDefaultSortingData());
                } else {
                    if (count($class::getVueCRUDSortingIndexColumns()) > 0) {
                        request()->merge([
                            'sorting_field'     => array_values($class::getVueCRUDSortingIndexColumns())[0],
                            'sorting_direction' => 'asc',
                        ]);
                    }
                }
            }
        }
        $filters = method_exists($class, 'getVueCRUDIndexFilters')
            ? (object) $class::getVueCRUDIndexFilters()
            : (object) [];
        $viewData = [
            'title'            => $this->getSubjectNamePlural(),
            'pageTitleContent' => $this->getSubjectNamePlural(),
            'pageTitle'        => $this->getSubjectNamePlural().' - '.config('app.name'),
            'columns'          => $this->getIndexColumns($positionedView),
            'sortingColumns'   => $this->getSortingColumns($positionedView),
            'sortingField'     => request()->get('sorting_field'),
            'sortingDirection' => request()->get('sorting_direction'),
            'filters'          => $filters,
            'buttons'          => $this->getModellistButtons($positionedView),
            'subjectName'      => $this->getSubjectName(),
            'mainButtons'      => $class::getModelManagerMainButtons(),
            'allowOperations'  => $class::shouldVueCRUDOperationsBeDisplayed(),
            'allowAdding'      => $class::shouldVueCRUDAddButtonBeDisplayed(),
            'positionedView'   => $positionedView,
            'massOperations'   => (object) $class::getVueCRUDMassFunctions(),
            'exportOperations' => (object) $class::getVueCRUDExportFunctions(),
            'idProperty'       => $class::getIdProperty(),
        ];
        if (request()->isXmlHttpRequest()) {
            $elementData = static::getElements();

            $viewData['elements'] = $elementData->elements;
            $viewData['counts'] = $elementData->counts;
            return response()->json($viewData);
        }
        $backlink = $class::getVueCRUDParentIndexLink();
        if (request()->has('referer')) {
            $backlink = unserialize(base64_decode(request()->get('referer')));
        }

        $viewData['backlink'] = $backlink;
        $viewData['defaultFilters'] = $filters;
        $viewData = array_merge($viewData, $this->getCRUDUrls($nameSuffix));

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
        if (method_exists($subject, 'addAdditionalDetails')) {
            $subject['additional_details_rendered'] = $subject->addAdditionalDetails();
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

    public function getRouteName($operation, $nameSuffix = '')
    {
        $class = static::SUBJECT_CLASS;

        return $class::getVueCRUDRouteName($operation, null, $nameSuffix);
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

    protected function getCRUDUrls($nameSuffix)
    {
        $result = [
            'indexUrl'          => $this->validateRoute($this->getRouteName('index', $nameSuffix)),
            'detailsUrl'        => $this->validateRoute($this->getRouteName('details', $nameSuffix),
                ['subject' => '___id___']),
            'storeUrl'          => $this->validateRoute($this->getRouteName('store', $nameSuffix)),
            'createUrl'         => $this->validateRoute($this->getRouteName('create', $nameSuffix)),
            'editUrl'           => $this->validateRoute($this->getRouteName('edit', $nameSuffix),
                ['subject' => '___id___']),
            'deleteUrl'         => $this->validateRoute($this->getRouteName('delete', $nameSuffix),
                ['subject' => '___id___']),
            'updateUrl'         => $this->validateRoute($this->getRouteName('update', $nameSuffix),
                ['subject' => '___id___']),
            'ajaxOperationsUrl' => $this->validateRoute($this->getRouteName('ajax_operations', $nameSuffix),
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
        $class = static::SUBJECT_CLASS;
        $classMassOperations = array_keys($class::getVueCRUDOptionalAjaxFunctions());

        return array_merge($classMassOperations, [
            'storeProfilePicture',
            'removeProfilePicture',
            'trixStoreAttachment',
            'trixRemoveAttachment',
            'storePublicAttachment',
            'removePublicAttachment',
            'trixGeneratePreview',
            'move',
            'moveTo',
        ]);
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
        if (request()->get('mode') == 'url') {
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
        } else {
            if (method_exists($this, 'processUploadedFileToObject')) {
                return response()->json($this->processUploadedFileToObject($processedFilename));
            } else {
                return response()->json([
                    'id'   => -1,
                    'name' => $processedFilename,
                ]);
            }
        }
    }

    protected function removePublicAttachment()
    {
        if (request()->get('mode') == 'url') {
            $file = basename(request()->get('file'));
            if (method_exists($this, 'processRemovedFile')) {
                $this->processRemovedFile($file);
            }
            if (\Storage::disk('public')->exists('attachments'.DIRECTORY_SEPARATOR.$file)) {
                \Storage::disk('public')->delete('attachments'.DIRECTORY_SEPARATOR.$file);
            }
            return response('OK');
        } else {
            if (method_exists($this, 'processRemovedFileObject')) {
                return $this->processRemovedFileObject(request()->get('file'));
            }
            return response('OK');
        }
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
        return '/attachments/'.basename($filename);
//        return storage_path('app'
//            .DIRECTORY_SEPARATOR
//            .'public'
//            .DIRECTORY_SEPARATOR
//            .'attachments'
//            .DIRECTORY_SEPARATOR
//            .basename($filename));
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

    public static function cleanRandomizationStringFromUploadFilename($filename)
    {
        return Str::after($filename, '___');
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
        if (! $class::hasPositioningEnabled()) {
            return false;
        }
        $validNullResponses = [null, 0, -1, ''];
        $filters = $class::getVueCRUDIndexFilters();
        foreach ($class::getRestrictingFields() as $restrictingField) {
            if (! isset($filters[$restrictingField])) {
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
            $result = $class::SUBJECT_NAME;
        } else {
            $result = static::SUBJECT_NAME;
        }

        return config('vuecrud.translateConstants', false)
            ? __($result)
            : $result;
    }

    protected function getSubjectNamePlural()
    {
        $class = static::SUBJECT_CLASS;
        if (defined($class.'::SUBJECT_NAME_PLURAL')) {
            $result = $class::SUBJECT_NAME_PLURAL;
        } else {
            $result = static::SUBJECT_NAME;
        }

        return config('vuecrud.translateConstants', false)
            ? __($result)
            : $result;
    }

    protected function getModificationResponse($subject)
    {
        if (request('respondWith', 'url') == 'url') {
            return route($this->getRouteName('index'));
        }

        return $subject;
    }

    protected function exportCsv()
    {
        $class = static::SUBJECT_CLASS;
        if (defined($class.'::exportCsv')) {
            $content = $class::exportCsv();
        } else {
            $tableData = $this->generateTableFromModelList(
                $this->getExportData(),
                $class::getVueCRUDExportColumns(),
                true
            );
            $result = [];
            $csv = fopen('php://temp/maxmemory:'.(5 * 1024 * 1024), 'r+');
            foreach ($tableData as $row) {
                fputcsv($csv, $row, ';');
            }

            rewind($csv);
            $content = stream_get_contents($csv);
        }
        return response($content)->withHeaders([
            'Content-Type' => 'text/csv',
            'filename'     => utf8_decode($this->getSubjectNamePlural()).'-'.now()->format('Y-m-d_H-i-s').'.csv',
        ]);
    }

    protected function exportHTML()
    {
        $class = static::SUBJECT_CLASS;
        if (defined($class.'::exportHTML')) {
            $content = $class::exportHTML();
        } else {
            $tableData = $this->generateTableFromModelList(
                $this->getExportData(),
                $class::getVueCRUDExportColumns()
            );
            $styles = $class::getVueCRUDHTMLExportTableStyle();
            $result = ['<table style="'.$styles['table'].'">'];
            foreach ($tableData as $index => $row) {
                $transformedRow = collect($row)->map(function($item) {
                    return $this->createHTMLFromDataRowElement($item);
                })->all();
                if ($index == 0) {
                    $result[] = '<tr style="'.$styles['tr'].'"><th style="'.$styles['th'].'">'
                        .implode('</th style="'.$styles['th'].'"><th>', $transformedRow)
                        .'</th>';
                } else {
                    $result[] = '<tr style="'.$styles['tr'].'"><td style="'.$styles['td'].'">'
                        .implode('</td><td style="'.$styles['td'].'">', $transformedRow)
                        .'</td>';
                }
            }
            $result[] = '</table>';
            $content = implode("\n", $result);
        }
        return response($content)->withHeaders([
            'Content-Type' => 'text/html',
            'filename'     => utf8_decode($this->getSubjectNamePlural()).'-'.now()->format('Y-m-d_H-i-s').'.html',
        ]);
    }

    protected function createHTMLFromDataRowElement($element)
    {
        if ($this->isHTTPUrl($element)) {
            return '<a target="_blank" href="'.$element.'">'.$element.'</a>';
        }
        if ($this->isHyperlink($element)) {
            if (strpos($element, '_blank') === false) {
                $element = str_ireplace('<a ', '<a target="_blank" ', $element);
            }
            return $element;
        }
        return $element;
    }

    private function isHTTPUrl($string)
    {
        return (mb_substr($string, 0, 7) == 'http://')
            || (mb_substr($string, 0, 8) == 'https://');
    }

    private function isHyperlink($string)
    {
        return strpos($string, 'href="') !== false;
    }

    //this function requires the PhpSpreadsheet library
    protected function exportXlsx()
    {
        $class = static::SUBJECT_CLASS;
        if (defined($class.'::exportXlsx')) {
            $content = $class::exportXlsx();
        } else {
            $tableData = $this->generateTableFromModelList(
                $this->getExportData(),
                $class::getVueCRUDExportColumns()
            );
            $result = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $result->getActiveSheet();
            foreach ($tableData as $row => $rowData) {
                foreach ($rowData as $column => $columnData) {
                    $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
                    $sheet->getColumnDimensionByColumn($column + 1)->setAutoSize(true);
                    $sheet->setCellValueByColumnAndRow($column + 1, $row + 1, str_ireplace('<br>', ", ", $columnData));
                    if ($this->isHTTPUrl($columnData)) {
                        $sheet->getCellByColumnAndRow($column + 1, $row + 1)->getHyperlink()->setUrl($columnData);
                        $sheet->getStyleByColumnAndRow($column + 1, $row + 1)->getFont()->setColor(new Color(Color::COLOR_BLUE));
                    }
                    if ($this->isHyperlink($columnData)) {
                        $content = strip_tags(str_ireplace('<br>', ', ', $columnData));
                        $sheet->setCellValueByColumnAndRow($column + 1, $row + 1, $content);
                        $matches = [];
                        preg_match('/href\=\"(.*?)\"/miu', $columnData, $matches);
                        if (isset($matches[1])) {
                            $sheet->getCellByColumnAndRow($column + 1, $row + 1)->getHyperlink()->setUrl($matches[1]);
                            $sheet->getStyleByColumnAndRow($column + 1, $row + 1)->getFont()->setColor(new Color(Color::COLOR_BLUE));
                        }
                    }
                    if ($row == 0) {
                        $sheet->getCellByColumnAndRow($column + 1, $row + 1)->getStyle()->getFont()->setBold(true);
                    }
                }
            }
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($result);
            $tmpfile = storage_path('app'.DIRECTORY_SEPARATOR.now()->format('YmdHis')
                .\Illuminate\Support\Str::random(32));
            $writer->save($tmpfile);
            $content = file_get_contents($tmpfile);
            unlink($tmpfile);
        }
        return response($content)->withHeaders([
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'filename'     => utf8_decode($this->getSubjectNamePlural()).'-'.now()->format('Y-m-d_H-i-s').'.xlsx',
        ]);
    }

    protected function getExportData()
    {
        $dataproviderClass = 'App\\Dataproviders\\'.class_basename(static::SUBJECT_CLASS).'VueCRUDDataprovider';
        $dataprovider = new $dataproviderClass(collect(request()->all()));
        return $dataprovider->addSortingToQuery($dataprovider->getQuery())->get();
    }

    protected function generateTableFromModelList($list, $columns, $stripHTML = false)
    {
        $result = [];
        $result[] = array_values($columns);
        foreach ($list as $element) {
            $row = [];
            foreach ($columns as $field => $label) {
                if ($stripHTML) {
                    $content = strip_tags(str_ireplace('<br>', ', ', $element->$field));
                    $row[] = $content;
                } else {
                    $row[] = $element->$field;
                }
            }
            $result[] = $row;
        }

        return $result;
    }

}
