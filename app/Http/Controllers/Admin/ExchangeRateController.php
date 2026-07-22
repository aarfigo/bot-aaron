<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExchangeRate;

class ExchangeRateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\EnsureUserHasRole::class . ':admin']);
    }

    /** Show current rate and small admin form (not used directly - view partial used) */
    public function index()
    {
        $today = now()->toDateString();
        $rate = ExchangeRate::where('date', $today)->first();
        // recent rates for history (last 30 days)
        $rates = ExchangeRate::orderBy('date','desc')->limit(30)->get();
        return view('admin.exchange_rate.index', ['rate' => $rate, 'rates' => $rates]);
    }

    /** Store or update today's rate */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'rate' => 'required|numeric|min:0',
        ]);

        ExchangeRate::updateOrCreate(
            ['date' => $validated['date']],
            ['rate' => $validated['rate']]
        );

        return redirect()->back()->with('success','Tipo de cambio actualizado');
    }
}
