<?php

namespace App\Rules;

use App\Account;

use Illuminate\Contracts\Validation\Rule;

class ValidAccountNumber implements Rule
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
        $check = Account::where('account_no', $value)->first();

        return $check !== null;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The Account Number is not associated with any account';
    }
}