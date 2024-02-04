<?php

use Core\Router;
use Controllers\PaymentController;

Router::register('check', [PaymentController::class, 'check']);
Router::register('create', [PaymentController::class, 'create']);
Router::register('confirm', [PaymentController::class, 'confirm']);
Router::register('reverse', [PaymentController::class, 'reverse']);
Router::register('status', [PaymentController::class, 'status']);