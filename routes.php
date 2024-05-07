<?php

use app\controllers\AuthController;
use app\controllers\OrdersController;

/**
 * Маршруты в формате: [uri, classController, classMethod, typeMethod]
 */
return [
	['/^auth\/login\/?$/', AuthController::class, 'view'],
	['/^auth\/login\/make?$/', AuthController::class, 'login', 'POST'],
	['/^auth\/logout\/?$/', AuthController::class, 'logout', 'DELETE'],
	['/^\/?$/', OrdersController::class, 'index'],
	['/^orders?$/', OrdersController::class, 'index'],
	['/^order\/state\/update?$/', OrdersController::class, 'updateState', 'POST'],
	['/^order\/get\/last-modified-date?$/', OrdersController::class, 'getLastModifiedDate', 'GET'],
];