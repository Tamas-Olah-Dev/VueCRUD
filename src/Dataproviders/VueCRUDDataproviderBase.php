<?php
/**
 * Created by PhpStorm.
 * User: caleb
 * Date: 1/25/19
 * Time: 3:31 PM
 */

namespace Datalytix\VueCRUD\Dataproviders;


use Illuminate\Support\Collection;

class VueCRUDDataproviderBase
{
    protected $filters;

    public function __construct(Collection $filters = null)
    {
        if ($filters == null) {
            $filters = collect(request()->all());
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

    public function addSortingToQuery($query)
    {
        return $query->orderBy($this->getSortingField(), $this->getSortingDirection());
    }

    protected function getSortingField()
    {
        return request()->get('sorting_field', 'id');
    }


    protected function getSortingDirection($default = 'asc')
    {
        return request()->get('sorting_direction', $default);
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
            'filtered' => \DB::query()->fromSub($this->getQuery(), 'vuecrudfiltered')->count(),
            'total' => \DB::query()->fromSub($this->getBaseQuery(), 'vuecrudtotal')->count(),
            'start' => ($this->getPage() - 1) * $this->getItemsPerPage() + 1,
        ];
        $result['start'] = $result['filtered'] > 0 ? $result['start'] : 0;
        if ($result['filtered'] == 0) {
            $result['end'] = 0;
        } else {
            $result['end'] = $result['filtered'] > ($this->getPage() - 1) * $this->getItemsPerPage()
                ? $this->getPage() * $this->getItemsPerPage()
                : $result['filtered'];
            if ($result['end'] > $result['filtered']) {
                $result['end'] = $result['filtered'];
            }
        }

        $result['pagesMax'] = ceil($result['filtered'] / $this->getItemsPerPage());

        return (object) $result;
    }

    function getElements()
    {
        $query = $this->addPaginationToQuery($this->getQuery());
        $query = $this->addSortingToQuery($query);

        return $query->get();
    }

    public function getElementsAndCounts()
    {
        return (object) [
            'counts'   => $this->getCounts(),
            'elements' => $this->getElements(),
            'sortingField' => $this->getSortingField(),
            'sortingDirection' => $this->getSortingDirection()
        ];
    }

    protected function addQueryFilters($query, $subjectClass)
    {
        foreach ($subjectClass::getVueCRUDIndexFilters() as $property => $vueCRUDIndexFilter) {
            if ($this->filters->has($property)) {
                $query = $vueCRUDIndexFilter->addFilterToQuery($query, $property);
            } else {
                $query = $vueCRUDIndexFilter->addFilterToQuery($query);
            }
        }

        return $query;
    }

    public function getCountedColumn()
    {
        //override this if count fails with '*', like for joined queries
        return '*';
    }
}
