<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $quarded = [];
   
    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
