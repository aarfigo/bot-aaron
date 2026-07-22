<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $table = 'exchange_rates';
    protected $fillable = ['date','rate'];

    /**
     * Return the exchange rate (BS per USD) for a given date.
     * If there's no rate for that exact date, returns the most recent rate before the date.
     * Returns null if no rate available.
     */
    public static function forDate($date)
    {
        $d = is_string($date) ? \Carbon\Carbon::parse($date)->toDateString() : ($date instanceof \DateTime ? \Carbon\Carbon::instance($date)->toDateString() : null);
        if (!$d) return null;
        $r = static::where('date', '<=', $d)->orderBy('date','desc')->first();
        return $r ? (float) $r->rate : null;
    }
}
