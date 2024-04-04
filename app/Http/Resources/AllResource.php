<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AllResource extends ResourceCollection
{
    public $status;
    public $message;

    /**
     * __construct
     *
     * @param  mixed $status
     * @param  mixed $message
     * @param  mixed $resource
     * @return void
     */
    public function __construct($status, $message, $resource)
    {
        parent::__construct($resource);
        $this->status = $status;
        $this->message = $message;
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
        ];
    }
}
