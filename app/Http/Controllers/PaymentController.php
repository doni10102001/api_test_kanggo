<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Master;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Protected Middleware Auth
        $this->middleware('auth:api');
    }

    public function results()
    {
        // Get User
        $get_user = Master::results_data('users');
        $user = [];
        foreach ($get_user as $value) {
            $user[$value->id] = [
                "id"    => $value->id,
                "name"  => $value->name,
                "email" => $value->email
            ];
        }

        // Get Product
        $get_product = Master::results_data('product');
        $product = [];
        foreach ($get_product as $value) {
            $product[$value->id] = [
                "id"    => $value->id,
                "name"  => $value->name,
                "price" => $value->price
            ];
        }

        // Get Transaction
        $get_transaction = Master::results_data('transaction');
        $transaction = [];
        foreach ($get_transaction as $value) {
            $transaction[$value->order_id] = $value;

            if (isset($user[$value->user_id])) {
                $transaction[$value->order_id]->user = $user[$value->user_id];
            } else {
                $transaction[$value->order_id]->user = $value->user_id;
            }
            unset($transaction[$value->order_id]->user_id);

            if (isset($product[$value->product_id])) {
                $transaction[$value->order_id]->product = $product[$value->product_id];
            } else {
                $transaction[$value->order_id]->product = $value->product_id;
            }
            unset($transaction[$value->order_id]->product_id);
        }

        // Get Payment Data
        $payment = Master::results_data('payment');
        $results = [];
        foreach ($payment as $key => $value) {
            $results[$key] = $value;

            if (isset($transaction[$value->order_id])) {
                $results[$key]->transaction = $transaction[$value->order_id];
            } else {
                $results[$key]->transaction = $value->order_id;
            }
        }

        return $this->response_data("Results Data Payment.", $results);
    }

    public function view($id)
    {
        // Get Payment Data
        $where['order_id'] = $id;
        $payment = Master::view_data('payment', $where);

        if ($payment) {
            // Get transaction
            $where_transaction['order_id'] = $payment->order_id;
            $get_transaction = Master::view_data('transaction', $where_transaction);

            if ($get_transaction) {
                // Get User
                $get_user = Master::view_data('users', ['id' => $get_transaction->user_id]);

                // Get Product
                $get_product = Master::view_data('product', ['id' => $get_transaction->product_id]);

                $payment->transaction = [
                    "order_id"      => $get_transaction->order_id,
                    "user"          => (($get_user) ? $get_user : $get_transaction->user_id),
                    "product"       => (($get_product) ? $get_product : $get_transaction->product_id),
                    "amount"        => $get_transaction->amount,
                    "status"        => $get_transaction->status
                ];
            } else {
                $payment->transaction = $payment->order_id;
            }

            return $this->response_data("Result Data Payment Success.", $payment);
        } else {
            return $this->response_message("Data Payment Not Found!.", 404);
        }
    }

    public function store(Request $request)
    {
        // Get Name Column in Table
        $get_column_table = Schema::getColumnListing('payment');
        array_unshift($get_column_table, 'payment');

        // Get All Request 
        $data = $request->all();

        // Check Name Request an with Name Column Table in DB
        $values_data = [];
        foreach ($data as $key => $item) {
            if (array_search($key, $get_column_table) != false) {
                $values_data += [$key => $item];
            }
        }

        if (isset($values_data['order_id'])) {
            // Get Transaction Data
            $get_transaction = Master::view_data('transaction', ["order_id" => $values_data['order_id']]);

            // Get Product Data
            $get_product = Master::view_data('product', ['id' => $get_transaction->product_id]);

            $calculate_amount = ((isset($get_product->price) ? $get_product->price : 0) * (isset($get_transaction->amount) ? $get_transaction->amount : 0)); 
        } else {
            $calculate_amount = 0;
        }

        $values_data['amount'] = $calculate_amount;

        $payment = Master::create('payment', $values_data);

        if ($payment == true) {
            // Update QTY Product
            if ($get_product) {
                $values['qty'] = ((($get_product->qty - $values_data['amount']) < 0) ? 0 : ($get_product->qty - $values_data['amount']));
                Master::updates('product', ['id' => $get_product->id], $values); 
            }

            // Update Status Transaction product
            if ($get_transaction) {
                $values_transaction['status'] = 'paid';
                Master::updates('transaction', ['order_id' => $get_transaction->order_id], $values_transaction); 
            }

            $response = $this->response_message("Payment Success");
        } else {
            $response = $this->response_message("Payment Failed!", 500);
        }

        return $response;
    }
}
