<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Illuminate\Http\Request;

class DownloadProcessedController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $fileName = 'processed.csv';
        $records = Record::all();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            'Cache-Control' => 'no-store, no-cache',
            "Expires"             => "0"
        );

        $columns = array('email', 'first_name', 'last_name', 'prefix', 'active');

        $callback = function() use($records, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($records as $record) {
                $row['email']  = $record->email;
                $row['first_name']    = $record->first_name;
                $row['last_name']    = $record->description;
                $row['prefix']  = $record->prefix;
                $row['active']  = $record->active;

                fputcsv($file, array($row['email'], $row['first_name'], $row['last_name'], $row['prefix'], $row['active']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
