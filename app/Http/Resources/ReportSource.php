<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ReportSource extends ResourceCollection
{
    public $status;
    public $message;
    public $sum;
    public $filter;

    /**
     * __construct
     *
     * @param  mixed $status
     * @param  mixed $message
     * @param  mixed $resource
     * @param  mixed $sum
     * @return void
     */
    public function __construct($status, $message, $resource, $sum,$filter)
    {
        parent::__construct($resource);
        $this->status = $status;
        $this->message = $message;
        $this->sum = $sum;
        $this->filter = $filter;

    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = $this->collection instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? $this->collection->items()
            : $this->collection->all();

        return [
            'success' => $this->status,
            'message' => $this->message,
            'data' => $data,
            'pagination' => $this->collection instanceof \Illuminate\Pagination\LengthAwarePaginator
                ? [
                    'current_page' => $this->collection->currentPage(),
                    'last_page' => $this->collection->lastPage(),
                    'total' => $this->collection->total(),
                ]
                : null,
            'sum' => $this->sum,
            'filters' => $this->filter,
        ];
    }
}
