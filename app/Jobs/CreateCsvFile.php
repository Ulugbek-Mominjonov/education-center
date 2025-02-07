<?php

namespace App\Jobs;

use App\Exports\StudentsExport;
use App\Models\Student;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel as FacadesExcel;
use Vtiful\Kernel\Excel;

class CreateCsvFile implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public $students;
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $uniqueId = uniqid('students_', true);
        $filePath = 'files/' . $uniqueId . '.xlsx';
        FacadesExcel::store(new StudentsExport, $filePath, 'local');
    }
}
