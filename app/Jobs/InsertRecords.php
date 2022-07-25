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

class InsertRecords implements ShouldQueue
{
    use Batchable,Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private StagingRecord $record;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(StagingRecord $record)
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
        $record = $this->record->jsonSerialize();
        unset($record['is_valid']);
        unset($record['id']);
        if(empty($record['active'])) unset($record['active']);
        Record::create($record);
    }
}
