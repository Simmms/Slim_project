<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Account;
use Hash;
use Session;
use App\Rules\BankOperations;
use App\Transaction;
use Carbon\Carbon;

class BankController extends Controller
{

    public function getCreateAccount()
    {
        return view('bank.create-account');
    }
    public function getLogin()
    {
        return view('bank.login');
    }

    public function postCreateAccount(Request $request)
    {
        $this->validate($request, [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|unique:accounts|email',
            'four_digit_pin' => 'required|digits:4| confirmed |numeric'
        ]);
        $data = $request->all();
        $data['account_no'] = explode('.', microtime(true))[0];
        $data['four_digit_pin'] = Hash::make($data['four_digit_pin']);
        $data['balance'] = 5000;
        $account = Account::create($data);
        return redirect()->back()->with(['account' => $account]);
    }
    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'account_no' => 'required|digits:10|numeric',
            'four_digit_pin' => 'required|digits:4|numeric'
        ]);
        $account = Account::where('account_no', $request['account_no'])->first();
        if ($account !== null) {
            $check = Hash::check($request['four_digit_pin'], $account->four_digit_pin);
            if (!$check) return redirect()->back()->with(['invalid_login' => true, 'message' => 'Invalid Authentication Pin']);
            Session::put(['bank_login' => true, 'customer' => $account->id]);
            return redirect()->to('/bank/portal');
        }
        return redirect()->back()->with(['invalid_login' => true, 'message' => 'This account number does not exist']);
    }

    public function loadPortal()
    {
        return view('bank.portal');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('bank_login');
        $request->session()->forget('customer');
        return redirect()->to('/bank/login');
    }

    public function withdrawOrDeposit(Request $request)
    {
        $this->validate($request, [
            'type' => ['required', new BankOperations],
            'amount' => 'required|numeric',
            'reason' => 'required'
        ]);
        $data = $request->all();
        $data['account_id'] = Account::holder()->id;
        if ($request->type !== 'deposit') {
            // Check the amount the User has first
            if ($this->isEligible($request->amount)) {
                $exceeded = $this->exceededLimit($data['amount']);
                if ($exceeded) {
                    return redirect()->back()->with(
                        ['ineligible' => true, 'message' => "Fund Withdrawal paused! You have withdrawn "
                            . $exceeded['amount'] . " between the period of " .
                            Carbon::parse($exceeded['limit']['start_date'])->toFormattedDateString() . " to "
                            . Carbon::parse($exceeded['limit']['end_date'])->toFormattedDateString() .
                            "You set a limit of " . $exceeded['limit']['amount']]
                    );
                }
                $this->uniformTransaction($data, 'minus');
                return redirect()->back()->with(['success' => true, 'message' => 'Transaction Successful']);
            }
            return redirect()->back()->with(['ineligible' => true, 'message' => 'Insufficient Funds']);
        }
        $this->uniformTransaction($data, 'add');
        return redirect()->back()->with(['success' => true, 'message' => 'Transaction Successful']);
    }

    protected function isEligible($amount)
    {
        return Account::holder()->balance > $amount;
    }

    protected function uniformTransaction($data, $type)
    {
        $account = Account::holder();
        $data['initial_balance'] = $account->balance;

        $type == 'add' ? $account->balance += $data['amount'] : $account->balance -= $data['amount'];
        $data['final_balance'] = $account->balance;
        $data['amount'] = abs($data['final_balance'] - $data['initial_balance']);
        $account->update();
        Transaction::create($data);
    }

    protected function exceededLimit($amount)
    {
        $exceeded = false;
        $limits = Account::holder()->user->limits;
       
        if ($limits !== null) {
            // Check if the Limit within the range and pick all
            $limitsInRange =  $limits->filter(function ($item) {
                if (Carbon::now()->between(Carbon::parse($item->start_date), Carbon::parse($item->end_date))) {
                    return $item;
                }
            });
            foreach ($limitsInRange as $limit) {
                $withdrawalSum = Account::holder()->transactions->where('type', 'withdraw')
                    ->whereBetween('created_at', [Carbon::parse($limit->start_date), Carbon::parse($limit->end_date)])
                    ->sum('amount');
                if ($withdrawalSum  + $amount >= $limit->amount) {
                    $exceeded['amount'] = $withdrawalSum;
                    $exceeded['limit'] = $limit;
                    break;
                }
            }
        }

        return $exceeded;
    }
}