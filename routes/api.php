<?php

use Illuminate\Support\Facades\Route;
use Modules\Ifulfillment\Http\Controllers\Api\OrderApiController;
use Modules\Ifulfillment\Http\Controllers\Api\OrderItemApiController;
use Modules\Ifulfillment\Http\Controllers\Api\ShipmentApiController;
use Modules\Ifulfillment\Http\Controllers\Api\ShipmentItemApiController;

use Modules\Ifulfillment\Http\Controllers\Api\DynamicOptionsApiController;

use Modules\Ifulfillment\Http\Controllers\Api\TraceApiController;
// add-use-controller



Route::prefix('/ifulfillment/v1')->group(function () {
  Route::apiCrud([
    'module' => 'ifulfillment',
    'prefix' => 'orders',
    'controller' => OrderApiController::class,
    'permission' => 'ifulfillment.orders',
    //'middleware' => ['create' => [], 'index' => [], 'show' => [], 'update' => [], 'delete' => [], 'restore' => []],
    'customRoutes' => [ // Include custom routes if needed
      [
        'method' => 'get', // get,post,put....
        'path' => '/group/filter-data', // Route Path
        'uses' => 'getGroupData', //Name of the controller method to use
        'middleware' => [] // if not set up middleware, auth:api will be the default
      ]
    ]
  ]);
  Route::apiCrud([
    'module' => 'ifulfillment',
    'prefix' => 'order-items',
    'controller' => OrderItemApiController::class,
    'permission' => 'ifulfillment.orderitems',
    //'middleware' => ['create' => [], 'index' => [], 'show' => [], 'update' => [], 'delete' => [], 'restore' => []],
    // 'customRoutes' => [ // Include custom routes if needed
    //  [
    //    'method' => 'post', // get,post,put....
    //    'path' => '/some-path', // Route Path
    //    'uses' => 'ControllerMethodName', //Name of the controller method to use
    //    'middleware' => [] // if not set up middleware, auth:api will be the default
    //  ]
    // ]
  ]);
  Route::apiCrud([
    'module' => 'ifulfillment',
    'prefix' => 'shipments',
    'controller' => ShipmentApiController::class,
    'permission' => 'ifulfillment.shipments',
    //'middleware' => ['create' => [], 'index' => [], 'show' => [], 'update' => [], 'delete' => [], 'restore' => []],
    'customRoutes' => [ // Include custom routes if needed
      [
        'method' => 'get', // get,post,put....
        'path' => '/group/filter-data', // Route Path
        'uses' => 'getGroupData', //Name of the controller method to use
        'middleware' => [] // if not set up middleware, auth:api will be the default
      ]
    ]
  ]);
  Route::apiCrud([
    'module' => 'ifulfillment',
    'prefix' => 'shipment-items',
    'controller' => ShipmentItemApiController::class,
    'permission' => 'ifulfillment.shipmentitems',
    //'middleware' => ['create' => [], 'index' => [], 'show' => [], 'update' => [], 'delete' => [], 'restore' => []],
    'customRoutes' => [ // Include custom routes if needed
      [
        'method' => 'get', // get,post,put....
        'path' => '/group/filter-data', // Route Path
        'uses' => 'getGroupData', //Name of the controller method to use
        'middleware' => [] // if not set up middleware, auth:api will be the default
      ]
    ]
  ]);
  Route::apiCrud([
    'module' => 'ifulfillment',
    'prefix' => 'supplier-types',
    'staticEntity' => 'Modules\Ifulfillment\Models\SupplierType',
    'middleware' => ['index' => [], 'show' => []]
  ]);
  Route::apiCrud([
    'module' => 'ifulfillment',
    'prefix' => 'shipment-item-stages',
    'staticEntity' => 'Modules\Ifulfillment\Models\ShipmentItemStage',
    'middleware' => ['index' => [], 'show' => []]
  ]);
  Route::apiCrud([
    'module' => 'ifulfillment',
    'prefix' => 'dynamic-options',
    'controller' => DynamicOptionsApiController::class,
    'permission' => 'ifulfillment.dynamic-options',
    //'middleware' => ['create' => [], 'index' => [], 'show' => [], 'update' => [], 'delete' => [], 'restore' => []],
    // 'customRoutes' => [ // Include custom routes if needed
    //  [
    //    'method' => 'post', // get,post,put....
    //    'path' => '/some-path', // Route Path
    //    'uses' => 'ControllerMethodName', //Name of the controller method to use
    //    'middleware' => [] // if not set up middleware, auth:api will be the default
    //  ]
    // ]
  ]);
    Route::apiCrud([
      'module' => 'ifulfillment',
      'prefix' => 'traces',
      'controller' => TraceApiController::class,
      'permission' => 'ifulfillment.traces',
      //'middleware' => ['create' => [], 'index' => [], 'show' => [], 'update' => [], 'delete' => [], 'restore' => []],
      // 'customRoutes' => [ // Include custom routes if needed
      //  [
      //    'method' => 'post', // get,post,put....
      //    'path' => '/some-path', // Route Path
      //    'uses' => 'ControllerMethodName', //Name of the controller method to use
      //    'middleware' => [] // if not set up middleware, auth:api will be the default
      //  ]
      // ]
    ]);
// append



});
