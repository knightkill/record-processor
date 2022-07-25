<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessRecords;
use App\Models\Record;
use App\Models\StagingRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class ProcessRecordsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

        $batch = Bus::batch([]);

        Record::whereNotNull('active')->orWhere('active',0)->chunk(200,function($records) use (&$batch) {
            foreach ($records as $record) {
                    $batch->add(new \App\Jobs\ProcessRecords($record));
            }
        });

        return response()->json([
            'status' => true,
            'details' => $batch->name('Process Records')->dispatch(),
        ]);
    }
}
