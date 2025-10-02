<?php

namespace Modules\Ifulfillment\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Imagina\Icore\Http\Controllers\CoreApiController;
//Model
use Modules\Ifulfillment\Models\ShipmentItem;
use Modules\Ifulfillment\Repositories\ShipmentItemRepository;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class ShipmentItemApiController extends CoreApiController
{
  public function __construct(ShipmentItem $model, ShipmentItemRepository $modelRepository)
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
