<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Master;

class TransactionController extends Controller
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

        // Get Transaction Data
        $transaction = Master::results_data('transaction');
        $results = [];
        foreach ($transaction as $key => $value) {
            $results[$key] = $value;

            if (isset($user[$value->user_id])) {
                $results[$key]->user = $user[$value->user_id];
            } else {
                $results[$key]->user = $value->user_id;
            }
            unset($results[$key]->user_id);

            if (isset($product[$value->product_id])) {
                $results[$key]->product = $product[$value->product_id];
            } else {
                $results[$key]->product = $value->product_id;
            }
            unset($results[$key]->product_id);
        }

        return $this->response_data("Results Data Transaction.", $transaction);
    }

    public function view($id)
    {
        // Get Transaction Data
        $where['order_id'] = $id;
        $transaction = Master::view_data('transaction', $where);

        if ($transaction) {
            // Get User
            $where_user['id'] = $transaction->user_id;
            $get_user = Master::view_data('users', $where_user);

            if ($get_user) {
                $transaction->user = [
                    "id"    => $get_user->id,
                    "name"  => $get_user->name,
                    "email" => $get_user->email
                ];
            } else {
                $transaction->user = $transaction->user_id;
            }

             // Get Product
             $where_product['id'] = $transaction->product_id;
             $get_product = Master::view_data('product', $where_product);
 
             if ($get_product) {
                 $transaction->product = [
                     "id"    => $get_product->id,
                     "name"  => $get_product->name,
                     "price" => $get_product->price
                 ];
             } else {
                 $transaction->product = $transaction->product_id;
             }
            unset($transaction->product_id);
            return $this->response_data("Result Data Transaction Success.", $transaction);
        } else {
            return $this->response_message("Data Transaction Not Found!.", 404);
        }
    }

    public function store(Request $request)
    {
        // Get Name Column in Table
        $get_column_table = Schema::getColumnListing('transaction');
        array_unshift($get_column_table, 'transaction');

        // Get All Request 
        $data = $request->all();

        // Check Name Request an with Name Column Table in DB
        $values_data = [];
        foreach ($data as $key => $item) {
            if (array_search($key, $get_column_table) != false) {
                $values_data += [$key => $item];
            }
        }
        $values_data['user_id'] = Auth::user()->id;
        $values_data['status'] = 'pending';

        $transaction = Master::createGetId('transaction', $values_data);

        if ($transaction) {
            // Get Data Transaction
            $where['order_id'] = $transaction;
            $get_transaction = Master::view_data('transaction', $where);

            $response = $this->response_data("Transaction Order Success", $get_transaction);
        } else {
            $response = $this->response_message("Transaction Order Failed!", 500);
        }

        return $response;
    }

    public function delete($id)
    {
        $delete_transaction = Master::remove('transaction', ['order_id' => $id]);

        // Delete Payment Data with order_id
        Master::remove('payment', ['order_id' => $id]);

        if ($delete_transaction == true) {
            $response = $this->response_message("Deleted Data Transaction Success.");
        } else {
            $response = $this->response_message("Deleted Data Transaction Failed!.");
        }

        return $response;
    }
}
