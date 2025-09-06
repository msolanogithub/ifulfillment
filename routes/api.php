<?php

use Illuminate\Support\Facades\Route;
use Modules\Ifulfillment\Http\Controllers\Api\OrderApiController;
use Modules\Ifulfillment\Http\Controllers\Api\OrderItemApiController;
use Modules\Ifulfillment\Http\Controllers\Api\ShipmentApiController;
use Modules\Ifulfillment\Http\Controllers\Api\ShipmentItemApiController;
// add-use-controller





Route::prefix('/ifulfillment/v1')->group(function () {
    Route::apiCrud([
      'module' => 'ifulfillment',
      'prefix' => 'orders',
      'controller' => OrderApiController::class,
      'permission' => 'ifulfillment.orders',
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
      'prefix' => 'orderitems',
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
      'prefix' => 'shipmentitems',
      'controller' => ShipmentItemApiController::class,
      'permission' => 'ifulfillment.shipmentitems',
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
