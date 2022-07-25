<?php

namespace App\Http\Controllers;

use App\Models\StagingRecord;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function validateStatus(Request $request) {
        $batch = \Illuminate\Support\Facades\Bus::findBatch($request->id);
        if($batch){
            if($batch->totalJobs === $batch->processedJobs()){
                //$batch->delete();

                return response()->json([
                    'status' => 'completed',
                    'message' => 'Batch Processed Successfully',
                    'failed_records' => StagingRecord::where('is_valid', 0)->count(),
                    'success_records' => StagingRecord::where('is_valid', 1)->count(),
                ]);
            }else if($batch->hasFailures()) {
                //$batch->delete();
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Batch Processed with Errors'
                ]);
            } else {
                $completed = $batch->processedJobs();
                return response()->json([
                    'status' => 'in-progress',
                    'message' => "$completed/$batch->totalJobs of records validated",
                    'progress' => $batch->progress(),
                    'completed' => $completed,
                    'total' => $batch->totalJobs
                ]);
            }
        }
    }

    public function processStatus(Request $request) {
        $batch = \Illuminate\Support\Facades\Bus::findBatch($request->id);
        if($batch){
            if($batch->totalJobs === $batch->processedJobs()){
                //$batch->delete();
                return response()->json([
                    'status' => 'completed',
                    'message' => 'Batch Processed Successfully'
                ]);
            }else if($batch->hasFailures()) {
                //$batch->delete();
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Batch Processed with Errors'
                ]);
            } else {
                $completed = $batch->processedJobs();
                return response()->json([
                    'status' => 'in-progress',
                    'message' => "$completed/$batch->totalJobs of records processed",
                    'completed' => $completed,
                    'total' => $batch->totalJobs
                ]);
            }
        }
    }

    public function insertStatus(Request $request) {
        $batch = \Illuminate\Support\Facades\Bus::findBatch($request->id);
        if($batch){
            if($batch->totalJobs === $batch->processedJobs()){
                //$batch->delete();
                return response()->json([
                    'status' => 'completed',
                    'message' => 'Batch Processed Successfully'
                ]);
            }else if($batch->hasFailures()) {
                //$batch->delete();
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Batch Processed with Errors'
                ]);
            } else {
                $completed = $batch->processedJobs();
                return response()->json([
                    'status' => 'in-progress',
                    'message' => "$completed/$batch->totalJobs of records inserted",
                    'completed' => $completed,
                    'total' => $batch->totalJobs
                ]);
            }
        }
    }
}
