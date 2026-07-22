<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'tbl_order';
    protected $primaryKey = 'orderID';
    public $timestamps = false;
    protected $fillable = ['status','total','order_date','customer_table','attended_by'];

    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'orderID', 'orderID');
    }

    public function table()
    {
        // customer_table stores the table number (not the tables.id)
        return $this->belongsTo(Table::class, 'customer_table', 'number');
    }

}
