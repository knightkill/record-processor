<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessRecords;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class ValidateRecordsSSE extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function __invoke(Request $request)
    {
        return response()->stream(function () {

            $filepath = storage_path('app/public/csv/records.csv');
            $file = fopen($filepath, 'r');
            $records = [];
            while (($data = fgetcsv($file, 200, ",")) !== FALSE && count($records) < 50) {
                $records[] = $data;
            }
            $header = $records[0];
            unset($records[0]);

            $batch = Bus::batch([]);

            foreach ($records as $record) {
                $record = array_combine($header, $record);
                if (!empty($record['active'])) {
                    $record['active'] = $record['active'] == 'true' ? 1 : 0;
                }
                $batch->add(new \App\Jobs\ValidateRecords($record));
            }
            $loop = true;
            $batch->finally(function(Batch $batch) use (&$loop) {
                $loop = false;
            });
            $batch = $batch->name('Validate Records')->dispatch();

            while($loop) {
                echo "date: {$batch->failedJobs}\n\n";

                ob_flush();
                flush();

                // Break the loop if the client aborted the connection (closed the page)
                if (connection_aborted()) {break;}
                sleep(1); // 500ms
            }
        },200,[
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
        ]);
    }
}
