<?php

namespace App\Jobs;

use App\Models\Record;
use App\Models\StagingRecord;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;

class ValidateRecords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $tries = 1;


    private $record;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($record)
    {

        $this->record = $record;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $validator = Validator::make($this->record, [
            'prefix' => 'string|max:10',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:records,email',
            'active' => 'boolean',
        ]);
        if ($validator->fails()) {
            $this->record['is_valid'] = false;
        } else {
            $this->record['is_valid'] = true;
        }

        StagingRecord::create($this->record);

    }
}
