<?php
namespace App\Traits;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

trait ResponseTrait
{
    protected function successResponse($message = 'Success', $statusCode = 200, $data = null, $extraParams = [])
    {
        $response = [
            'status' => true,
            'message' => $message,
        ];

        if (is_array($extraParams) && !empty($extraParams)) {
            $response = array_merge($response, $extraParams);
        }

        // Detecta o tipo de dados
        switch (true) {
            case $data instanceof AnonymousResourceCollection:
            case $data instanceof ResourceCollection:
                $response = $this->handleResourceCollection($data, $response);
                break;
                
            case $data instanceof LengthAwarePaginator:
                $response = $this->handlePaginator($data, $response);
                break;
                
            default:
                if (!is_null($data)) {
                    $response['data'] = $data;
                }

        }

        return response()->json($response, $statusCode);
    }

    private function handleResourceCollection($collection, $response)
    {
        // Pega o paginator original
        $paginator = $collection->resource;
        
        if ($paginator instanceof LengthAwarePaginator) {
            $response['data'] = $collection->resolve();
            $response['meta'] = $this->getPaginationMeta($paginator);
            $response = $this->removePaginationLinks($response);
        } else {
            $response['data'] = $collection;
        }
        
        return $response;
    }

    private function handlePaginator($paginator, $response)
    {
        $response['data'] = $paginator->items();
        $response['meta'] = $this->getPaginationMeta($paginator);
        return $this->removePaginationLinks($response);
    }

    private function getPaginationMeta($paginator)
    {
        return [
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    // private function getPaginationLinks($paginator)
    // {
    //     return [
    //         'first' => $paginator->url(1),
    //         'last' => $paginator->url($paginator->lastPage()),
    //         'prev' => $paginator->previousPageUrl(),
    //         'next' => $paginator->nextPageUrl(),
    //     ];
    // }

    private function removePaginationLinks($response)
    {
        if (isset($response['data']['links'])) {
            unset($response['data']['links']);
        }

        if (isset($response['links'])) {
            unset($response['links']);
        }
        
        return $response;
    }

    protected function errorResponse($message, $statusCode = 400, $errors = null,  $extraParams = [])
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];
        
        if ($errors) {
            $response['errors'] = $errors;
        }

        if (is_array($extraParams) && !empty($extraParams)) {
            $response = array_merge($response, $extraParams);
        }

        return response()->json($response, $statusCode);
    }
}