<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'tbl_orderdetail';
    protected $primaryKey = 'orderDetailID';
    public $timestamps = false;
    protected $fillable = ['orderID','itemID','quantity','comment'];

    public function item()
    {
        return $this->belongsTo(MenuItem::class, 'itemID');
    }
}
