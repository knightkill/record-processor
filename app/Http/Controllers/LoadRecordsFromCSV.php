<?php

namespace App\Http\Controllers;

use App\Http\Requests\CSVRequest;
use App\Jobs\ProcessRecords;
use App\Models\Record;
use App\Models\StagingRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class LoadRecordsFromCSV extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param CSVRequest $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(CSVRequest $request)
    {
        if ($request->has('csv')) {

            $file = $request->file('csv');
            $file->move(storage_path('app/public/csv'), 'records.csv');
            $filepath = storage_path('app/public/csv/records.csv');
            file_put_contents($filepath,implode('', file($filepath, FILE_SKIP_EMPTY_LINES)));

            StagingRecord::truncate();
            Record::truncate();

            return response()->json([
                'status' => true,
                'message' => 'File uploaded successfull'
            ]);
        }
        return response()->json([
            'message' => 'Please Upload a CSV file'
        ]);
    }
}
