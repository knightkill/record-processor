<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\StagingRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProcessStageController extends Controller
{
    //Check if no fileexists
    //Check if file exists
    //Check if staging records are same as rows in file
    //Check if records are same as valid staging records
    //Check if active records are all converted to 1
    public function __invoke(Request $request)
    {
        $stage = 0;

        if($this->stageOne()) $stage = 1;

        if($this->stageTwo()) $stage = 2;

        if($this->stageThree()) $stage = 3;

        if($this->stageFour()) $stage = 4;


        return response()->json([
            'status' => true,
            'stage' => $stage
        ]);
    }

    private function stageOne() {
        $filepath = storage_path('app/public/csv/records.csv');
        return file_exists($filepath);
    }

    private function stageTwo() {
        try {

        $filepath = storage_path('app/public/csv/records.csv');
        $file = new \SplFileObject($filepath,'r');
        $file->seek(PHP_INT_MAX);
        $count_line = $file->key();
        } catch (\Exception $e) {
            return false;
        }
        return StagingRecord::all()->count() !== 0 && StagingRecord::all()->count() == $count_line;
    }

    private function stageThree()
    {
        return Record::all()->count() > 0 && Record::all()->count() == StagingRecord::where('is_valid',1)->count();
    }

    private function stageFour()
    {
        return Record::all()->count() > 0 && Record::where('active', 0)->count() == 0;
    }
}
