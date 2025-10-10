<?php

namespace Modules\Ifulfillment\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Imagina\Icore\Http\Controllers\CoreApiController;
//Model
use Modules\Ifulfillment\Models\Shipment;
use Modules\Ifulfillment\Repositories\ShipmentRepository;
use Symfony\Component\HttpFoundation\Response;

class ShipmentApiController extends CoreApiController
{
  public function __construct(Shipment $model, ShipmentRepository $modelRepository)
  {
    parent::__construct($model, $modelRepository);
  }

  public function getGroupData(Request $request): JsonResponse
  {
    try {
      //Get Parameters from request
      $params = $this->getParamsRequest($request);

      //Request data to Repository
      $models = $this->modelRepository->getGroupData($params);

      //Response
      $response = ['data' => $models];

      if ($params->page) $response['meta'] = ['page' => $this->pageTransformer($models)];
    } catch (Exception $e) {
      [$status, $response] = $this->getErrorResponse($e);
    }

    //Return response
    return response()->json($response, $status ?? Response::HTTP_OK);
  }
}
