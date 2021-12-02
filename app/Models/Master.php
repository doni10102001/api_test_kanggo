<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Master extends Model
{
    public static function results_data($table, $where=[]) {
        $data = DB::table($table);
        
        if (!empty($where)) {
            $results = $data->where($where)->get();
        } else {
            $results = $data->get();
        }

        return $results;
    }

    public static function view_data($table, $where) {
        $data = DB::table($table)->where($where)->first();
        
        return $data;
    }

    public static function create($table, $value) {
        $create = DB::table($table)->insert($value);

        return $create;
    }

    public static function createGetId($table, $value) {
        $create = DB::table($table)->insertGetId($value);

        return $create;
    }

    public static function updates($table, $where, $value) {
        $update = DB::table($table)->where($where)->update($value);

        return $update;
    }

    public static function remove($table, $where) {
        $delete = DB::table($table)->where($where)->delete();

        return $delete;
    }
}
