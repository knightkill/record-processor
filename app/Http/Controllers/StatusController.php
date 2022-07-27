<?php

namespace App\Http\Controllers;

use App\Models\StagingRecord;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatusController extends Controller
{

    public function validateStatusSSE(Request $request) {
        $batch_id = $request->batch_id;
        if(empty($batch_id)) return null;
        $response = new StreamedResponse(function() use ($batch_id) {
            while(true) {

                $batch = Bus::findBatch($batch_id);
                if(empty($batch)) {
                    echo "event: error\n";
                    echo "data: \n\n";
                    ob_flush();
                    flush();
                    break;
                }
                $completed = $batch->processedJobs();
                $message = "$completed/$batch->totalJobs of records validated";
                Log::debug("$completed $message");
                echo "data: {\n";
                echo "data: \"message\":\"$message\", \n";
                echo "data: \"progress\":\"{$batch->progress()}\", \n";
                echo "data: \"completed\":\"$completed\", \n";
                echo "data: \"total\":\"{$batch->totalJobs}\" \n";
                echo "data: }\n\n";

                ob_flush();
                flush();
                if(connection_aborted()){
                    break;
                }
                if($batch->progress() == 100 || $batch->processedJobs() === $batch->totalJobs){
                    usleep(500000);
                    $batch->delete();
                    $failedRecords = StagingRecord::where('is_valid', 0)->count();
                    $successRecords = StagingRecord::where('is_valid', 1)->count();
                    echo "event: close\n";
                    echo "data: {\n";
                    echo "data: \"message\":\"Batch Processed Successfully\", \n";
                    echo "data: \"failed_records\":\"$failedRecords\", \n";
                    echo "data: \"success_records\":\"$successRecords\" \n";
                    echo "data: }\n\n";
                    ob_flush();
                    flush();
                    break;
                }
                usleep(200000);
            }
        });
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no'); // Nginx: unbuffered responses suitable for Comet and HTTP streaming applications
        return $response;
    }

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
