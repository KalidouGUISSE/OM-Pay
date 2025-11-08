<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ResponseTraits{

    public function successResponse(string $message, array $data, int $status = Response::HTTP_CREATED ){
          return response()->json([
            "success"=>true ,
            "message"=> $message,
            "data" => $data
        ], $status);
    }

    // public function errorResponse(string $message, string $erreur, $status){
    //     return response()->json(
    // [
    //         'success'=>false,
    //         'message'=>$message,
    //         'errors'=> $erreur,
    //     ], $status);
    // }

    public function errorResponse(string $message, string $erreur = '', int $status = 400)
{
    return response()->json(
        [
            'success' => false,
            'message' => $message,
            'errors' => $erreur,
        ], 
        $status
    );
}


      public function paginatedResponse(
        $data,
        int $currentPage,
        int $totalPages,
        int $totalItems,
        int $itemsPerPage,
        bool $hasNext,
        bool $hasPrevious,
        array $links = [],
        string $message = 'Données récupérées avec succès'
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
                'itemsPerPage' => $itemsPerPage,
                'hasNext' => $hasNext,
                'hasPrevious' => $hasPrevious,
            ],
            'links' => $links,
        ], Response::HTTP_OK);
    }
}