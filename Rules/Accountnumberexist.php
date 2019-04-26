<?php

namespace App\Rules;

use App\User;
use App\Account;

use Illuminate\Contracts\Validation\Rule;

class Accountnumberexist implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {


        $account_id = Account::where('account_no', $value)->first()->id;
        $check = User::where('account_id', $account_id)->first();

        return $check == null;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The Account Number already exist';
    }
}