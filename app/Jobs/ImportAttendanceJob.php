<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\SimpleExcel\SimpleExcelReader;
use App\Models\Employee;
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\Log;

class ImportAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $filePath;
    public $batchId;

    public function __construct($filePath, $batchId)
    {
        $this->filePath = $filePath;
        $this->batchId = $batchId;
    }

    public function handle(): void
    {
        $status = \Illuminate\Support\Facades\Cache::get('import_attendance_' . $this->batchId, [
            'status' => 'processing',
            'success' => 0,
            'errors' => [],
            'total' => 0
        ]);

        try {
            $reader = SimpleExcelReader::create($this->filePath);
            $rows = $reader->getRows();
            $status['total'] = $rows->count();
            
            $rows->each(function (array $rowProperties) use (&$status) {
                // Expecting headers: NIK, Tanggal, Jam Masuk, Jam Keluar, Status
                $nik = $rowProperties['NIK'] ?? null;
                $tanggal = $rowProperties['Tanggal'] ?? null;
                
                if (!$nik || !$tanggal) {
                    $status['errors'][] = "Missing NIK or Tanggal in row.";
                    Log::warning("Attendance Import: Missing NIK or Tanggal in row", $rowProperties);
                    return; // Skip invalid row
                }

                $employee = Employee::where('nik', $nik)->first();
                if (!$employee) {
                    $status['errors'][] = "Employee not found with NIK {$nik}.";
                    Log::warning("Attendance Import: Employee not found with NIK {$nik}");
                    return; // Skip if employee doesn't exist
                }

                // Parse dates and times
                $clockIn = $rowProperties['Jam Masuk'] ?? null;
                $clockOut = $rowProperties['Jam Keluar'] ?? null;
                $statusVal = strtolower($rowProperties['Status'] ?? 'hadir');
                if (!in_array($statusVal, ['hadir', 'alfa', 'izin', 'sakit', 'cuti'])) {
                    $statusVal = 'alfa';
                }

                AttendanceRecord::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'date' => $tanggal,
                    ],
                    [
                        'clock_in' => $clockIn ?: null,
                        'clock_out' => $clockOut ?: null,
                        'status' => $statusVal,
                        'remarks' => 'Imported via CSV/Excel',
                    ]
                );
                
                $status['success']++;
            });
            
            $status['status'] = 'completed';
        } catch (\Exception $e) {
            $status['status'] = 'failed';
            $status['errors'][] = "Exception: " . $e->getMessage();
            Log::error("Attendance Import failed: " . $e->getMessage());
        }
        
        \Illuminate\Support\Facades\Cache::put('import_attendance_' . $this->batchId, $status, now()->addHours(2));
    }
}
