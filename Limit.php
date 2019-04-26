<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Limit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'start_date', 'end_date', 'amount', 'user_id'
    ];
}