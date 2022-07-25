<?php

namespace App\Http\Controllers;

use App\Models\StagingRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class InsertRecordsController extends Controller
{

    /**
     * Add Jobs for adding valid staging records to records table
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

        $batch = Bus::batch([]);

        StagingRecord::chunk(200,function($records) use (&$batch) {
            foreach ($records as $record) {
                if($record->is_valid){
                    $batch->add(new \App\Jobs\InsertRecords($record));
                }
            }
        });

        return response()->json([
            'status' => true,
            'details' => $batch->name('Insert Records')->dispatch(),
        ]);
    }
}
