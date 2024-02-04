<?php

namespace Controllers;

use Core\Request;
use Core\Response;
use Models\Order;
use Config;
use Models\UzumBankTransaction;

class PaymentController
{
    public function check(Request $request): Response
    {
        $order_id = $request->get('params')['account'];
        $order = new Order();
        $order = $order->selectOne(['id' => $order_id]);
        if(!$order) {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10007"
            ], 400);
        }

        if($order['status'] == 1) {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10008"
            ], 400);
        }

        if($order['status'] == -1) {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10009"
            ], 400);
        }

        return new Response([
            'serviceId' => $request->get('serviceId'),
            'timestamp' => $request->get('timestamp'),
            'status' => "OK",
            'data' => [
                'account' => [
                    'value' => $order['id']
                ],
                'order' => [
                    'amount' => $order['amount'],
                    'status' => $order['status']
                ]
            ]
        ], 200);
    }

    public function create(Request $request): Response
    {
        if(!$request->exists(['transId', 'params', 'amount'])) {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10005"
            ], 400);
        }

        $transId = $request->get('transId');
        $account = $request->get('params')['account'];
        $amount = $request->get('amount') / 100;

        $transaction = new UzumBankTransaction();
        $transaction = $transaction->selectOne(['transaction_id' => $transId]);
        if($transaction) {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10010"
            ], 400);
        }
        $order = new Order();
        $order = $order->selectOne(['id' => $account]);
        if(!$order) {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10007"
            ], 400);
        }

        if($order['amount'] != $amount) {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10011"
            ], 400);
        }

        if($amount < Config::MIN_AMOUNT) {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10012"
            ], 400);
        }

        if($amount > Config::MAX_AMOUNT) {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10013"
            ], 400);
        }

        if($order['status'] == -1)
        {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10009"
            ], 400);
        }

        if($order['status'] == 1)
        {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10008"
            ], 400);
        }

        $transaction = new UzumBankTransaction();
        $time = $request->get('timestamp');
        $transaction->add([
            'transaction_id' => $transId,
            'amount' => $amount,
            'order_id' => $account,
            'status' => 'CREATED',
            'created_at' => $time,
        ]);

        return new Response([
           'serviceId' => $request->get('serviceId'),
           'transId' => $transId,
           'status' => 'CREATED',
           'transTime' =>  $time,
            'data' => [
                'account' => [
                    'value' => $order['id']
                ],
                'order' => [
                    'amount' => $order['amount'],
                    'status' => $order['status']
                ]
            ],
            'amount' => $amount * 100,
        ]);
    }

    public function confirm(Request $request): Response
    {
        if(!$request->exists(['transId'])) {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10005"
            ], 400);
        }

        $transId = $request->get('transId');
        $transaction = new UzumBankTransaction();
        $transactionData = $transaction->selectOne(['transaction_id' => $transId]);
        if(!$transactionData) {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10014"
            ], 400);
        }

        $order = new Order();
        $orderData = $order->selectOne(['id' => $transactionData['order_id']]);
        if(!$orderData) {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10007"
            ], 400);
        }

        if($orderData['status'] == 1) {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10016"
            ], 400);
        }

        if($orderData['status'] == -1) {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10015"
            ], 400);
        }
        $time = $request->get('timestamp');
        $transaction = new UzumBankTransaction();
        $transaction->update([
            'status' => 'CONFIRMED',
            'confirmed_at' => $time
        ], ['transaction_id' => $transId]);
        $order->update([
            'status' => 1
        ], ['id' => $transactionData['order_id']]);

        return new Response([
            'serviceId' => $request->get('serviceId'),
            'timestamp' => $request->get('timestamp'),
            'status' => "CONFIRMED",
            'confirmTime' => $time,
            'data' => [
                'account' => [
                    'value' => $orderData['id']
                ],
                'order' => [
                    'amount' => $orderData['amount'],
                    'status' => $orderData['status']
                ]
            ]
        ], 200);
    }

    public function reverse(Request $request): Response
    {
        return new Response(['status' => 'ok'], 200);
    }

    public function status(Request $request): Response
    {
        if(!$request->exists(['transId'])) {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10005"
            ], 400);
        }

        $transId = $request->get('transId');

        $transaction = new UzumBankTransaction();
        $transactionData = $transaction->selectOne(['transaction_id' => $transId]);

        if(!$transactionData) {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10014"
            ], 400);
        }

        $order = new Order();
        $orderData = $order->selectOne(['id' => $transactionData['order_id']]);

        if(!$orderData) {
            return new Response([
                'serviceId' => $request->get('serviceId'),
                'timestamp' => $request->get('timestamp'),
                'status' => "FAILED",
                'errorCode' => "10007"
            ], 400);
        }

        return new Response([
            'serviceId' => $request->get('serviceId'),
            'timestamp' => $request->get('timestamp'),
            'status' => $transactionData['status'],
            'data' => [
                'account' => [
                    'value' => $orderData['id']
                ],
                'order' => [
                    'amount' => $orderData['amount'],
                    'status' => $orderData['status']
                ]
            ]
        ], 200);
    }
}