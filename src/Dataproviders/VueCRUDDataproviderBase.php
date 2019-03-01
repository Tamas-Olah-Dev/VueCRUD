<?php
/**
 * Created by PhpStorm.
 * User: caleb
 * Date: 1/25/19
 * Time: 3:31 PM
 */

namespace OlahTamas\VueCRUD\Dataproviders;


use Illuminate\Support\Collection;

class VueCRUDDataproviderBase
{
    protected $filters;

    public function __construct(Collection $filters = null)
    {
        if ($filters == null) {
            $filters = request()->all();
        }
        $this->filters = $filters;
    }

    public static function createFromRequest()
    {
        $filter = collect(request()->all())->only(static::getFilterFields());

        return static::__construct($filter);
    }

    protected function addPaginationToQuery($query)
    {
        $page = $this->getPage();
        $itemsPerPage = $this->getItemsPerPage();

        return $query->skip(($page - 1) * $itemsPerPage )->take($itemsPerPage);
    }

    protected function getItemsPerPage($default = 20)
    {
        return request()->get('items_per_page', $default);
    }

    protected function getPage($default = 1)
    {
        return request()->get('page', $default);
    }

    public function getCounts()
    {
        $result = [
            'filtered' => $this->getQuery()->count(),
            'total' => $this->getBaseQuery()->count(),
            'start' => ($this->getPage() - 1) * $this->getItemsPerPage() + 1,
            'end' => $this->getPage() * $this->getItemsPerPage(),
        ];

        $result['pagesMax'] = ceil($result['total'] / $this->getItemsPerPage());

        return (object) $result;
    }

    function getElements()
    {
        return $this->addPaginationToQuery($this->getQuery())->get();
    }

    public function getElementsAndCounts()
    {
        return (object) [
            'counts'   => $this->getCounts(),
            'elements' => $this->getElements(),
        ];
    }

    protected function addQueryFilters($query, $subjectClass)
    {
        foreach ($subjectClass::getVueCRUDIndexFilters() as $property => $vueCRUDIndexFilter) {
            $query = $vueCRUDIndexFilter->addFilterToQuery($query, $property);
        }

        return $query;
    }
}