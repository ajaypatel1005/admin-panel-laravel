<?php

namespace App\Http\Controllers\Helpers;

use Carbon\Carbon;
use Response;

class SearchSortPaginationHelper
{

    /**
     * @param $request
     * @param $query
     * @return \Illuminate\Http\JsonResponse|string
     */
    public static function applySortSearchPagination($request, $query, $searchCols, $with = null)
    {
        $query = new $query;
        $query = $query::query();
        if (count($with) > 0) {
            $query->with($with);
        }
        $searchChars = $request->get('search') ?? null;
        if ($searchChars) {
            for ($i = 0; $i < count($searchCols); $i++) {
                if ($i > 0)
                    $query->orWhere($searchCols[$i], 'like', '%' . $searchChars . '%');
                else
                    $query->where($searchCols[$i], 'like', '%' . $searchChars . '%');
            }
        }
        $take = $request->get('take') ?? 10;
        $pageNumber = $request->get('page_number') ?? 1;
        $sort = explode(',', $request->get('sort') ?? 'id,asc');
        $sortByColumn = $sort[0];
        $sortByDirection = array_key_exists(1, $sort) ? $sort[1] : 'asc';
        $query->orderBy($sortByColumn, $sortByDirection);
        $records = $query->paginate($take, ['*'], 'page', $pageNumber);

        $data = $records->map(function ($item) {
            $item->created_at_formate = $item->created_at ? Carbon::parse($item->created_at)->format('d-m-Y H:i:s') : null;
            $item->updated_at_formate = $item->updated_at ? Carbon::parse($item->updated_at)->format('d-m-Y H:i:s') : null;
            return $item;
        });

        $total = $records->total();
       
        return [
            'data' => $data->values()->all(),
            'total_record' => $total
        ];
    }
}
