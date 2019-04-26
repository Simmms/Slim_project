<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Session;

class Account extends Model
{
    protected $hidden =
    [
        'four_digit_pin'
    ];
    protected $fillable = [
        'firstname', 'lastname', 'account_no', 'email', 'four_digit_pin', 'balance'
    ];

    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }

    public static function holder()
    {
        return SELF::find(Session::get('customer'));
    }

    // More Like an Api call from the Bank to get the User and all his Limits shaa
    public function user()
    {
        return $this->hasOne('App\User');
    }
}