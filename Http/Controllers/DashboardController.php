<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Rules\ValidRange;
use App\Rules\LimitBalance;
use App\Limit;


class DashboardController extends Controller
{
    public function index()
    {
        $limits = Limit::latest()->paginate(5);
        return view('dashboard.index', ['limits' => $limits]);
    }

    public function limitIndex()
    {
        return view('dashboard.limit');
    }

    public function setLimit(Request $request)
    {
        $this->validate($request, [
            'amount' => ['required', 'numeric', new LimitBalance],
            'range' => [
                'required', new ValidRange
            ]
        ]);
        $data = $this->formatData($request);
        $today = Carbon::today();
        if ($today->diffInDays($data['start_date']) >= 0  && $today->diffInDays($data['end_date']) >= 1) {
            Limit::create($data);
            return redirect()->back()->with(['success' => true,  'message' => 'A fundwithdrawal limit of ' . $data['amount'] . ' has been set between ' . Carbon::parse($data['start_date'])->toFormattedDateString() . " to " . Carbon::parse($data['end_date'])->toFormattedDateString()]);
        }
        return redirect()->back()->with(['error' => true, 'message' => 'Invalid Limit Range, Starting/Ending Date should not be earlier than today']);
    }

    public function deactivate($id)
    {
        $limit = Limit::find($id);
        if ($limit !== null) {
            $limit->delete();
            $limit->update();
            return redirect()->back()->with(['success' => true, 'message' => 'Limit deleted']);
        }
        return redirect()->back()->with(['error' => true, 'message' => 'Limit not found']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->to('/login');
    }

    protected function formatData(Request $request): array
    {
        $data = $request->all();
        $date = explode(' to', $data['range']);
        $data['start_date'] = $date[0];
        $data['end_date'] = $date[1];
        $data['user_id'] = Auth::id();
        unset($data['range']);
        return $data;
    }
}