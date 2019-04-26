<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public function account()
    {
        return $this->belongsTo('App\Account');
    }

    protected $fillable = [
        'account_id', 'initial_balance', 'final_balance', 'type', 'reason', 'amount'
    ];

    public function amount()
    {
        return abs($this->final_balance - $this->initial_balance);
    }
}