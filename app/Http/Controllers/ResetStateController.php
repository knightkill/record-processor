<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\StagingRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;

class ResetStateController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        if(file_exists(storage_path('app/public/csv/records.csv')))
            unlink(storage_path('app/public/csv/records.csv'));
        StagingRecord::truncate();
        Record::truncate();
        Redis::command('flushdb');
        return response()->json([
            'success' => true
        ]);
    }
}
