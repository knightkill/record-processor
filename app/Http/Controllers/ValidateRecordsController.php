<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessRecords;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class ValidateRecordsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $filepath = storage_path('app/public/csv/records.csv');
        $file = fopen($filepath, 'r');
        $records = [];
        while ( ($data = fgetcsv($file, 200, ",")) !==FALSE ) {
            $records[] = $data;
        }
        $header = $records[0];
        unset($records[0]);

        $batch = Bus::batch([]);

        foreach ($records as $record) {
            $record = array_combine($header,$record);
            if(!empty($record['active'])) {
                $record['active'] = $record['active'] == 'true' ? 1 : 0;
            }
            $batch->add(new \App\Jobs\ValidateRecords($record));
        }





        return response()->json([
            'status' => true,
            'details' => $batch->name('Validate Records')->dispatch(),
        ]);
    }
}
