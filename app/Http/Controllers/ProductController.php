<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Master;

class ProductController extends Controller
{
    public function __construct()
    {
        // Protected Middleware Auth
        $this->middleware('auth:api');
    }

    public function results() {
        $product = Master::results_data('product');

        return $this->response_data("Results Data Product.", $product);
    }

    public function view($id) {
        $where['id'] = $id;
        $product = Master::view_data('product', $where);

        return $this->response_data("Results Data Product.", $product);
    }

    public function store(Request $request) {
        // Get Name Column in Table
        $get_column_table = Schema::getColumnListing('product');
        array_unshift($get_column_table, 'product');
        
        // Get All Request 
        $data = $request->all();

        // Check Name Request an with Name Column Table in DB
        $values_data = [];
        foreach ($data as $key => $item) {
            if (array_search($key, $get_column_table) != false) {
                $values_data += [$key => $item];
            }
        }

        // Check type store data
        if (isset($data['action']) && $data['action'] == 'add') {
            $store = Master::create('product', $values_data);

            if ($store == true) {
                $response = $this->response_message("Created Data Product Success");
            } else {
                $response = $this->response_message("Created Data Product Failed!", 500);
            }
        } elseif (isset($data['action']) && $data['action'] == 'edit') {
            $where['id'] = $data['id'];
            unset($data['id']);

            $store = Master::updates('product', $where, $values_data);

            if ($store == true) {
                $response = $this->response_message("Update Data Product Success");
            } else {
                $response = $this->response_message("Update Data Product Failed!", 500);
            }
        } else {
            $response = $this->response_message("Action Form Not Found!", 409);    
        }

        return $response;
    }

    public function delete($id) {
        $where['id'] = $id;
        $delete_product = Master::remove('product', $where);

        if ($delete_product == true) {
            $response = $this->response_message("Deleted Data Product Success.");
        } else {
            $response = $this->response_message("Deleted Data Product Failed!.");
        }

        return $response;
    }
}
