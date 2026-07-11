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

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function handle(): void
    {
        try {
            $reader = SimpleExcelReader::create($this->filePath);
            
            $reader->getRows()->each(function (array $rowProperties) {
                // Expecting headers: NIK, Tanggal, Jam Masuk, Jam Keluar, Status
                $nik = $rowProperties['NIK'] ?? null;
                $tanggal = $rowProperties['Tanggal'] ?? null;
                
                if (!$nik || !$tanggal) {
                    Log::warning("Attendance Import: Missing NIK or Tanggal in row", $rowProperties);
                    return; // Skip invalid row
                }

                $employee = Employee::where('nik', $nik)->first();
                if (!$employee) {
                    Log::warning("Attendance Import: Employee not found with NIK {$nik}");
                    return; // Skip if employee doesn't exist
                }

                // Parse dates and times
                $clockIn = $rowProperties['Jam Masuk'] ?? null;
                $clockOut = $rowProperties['Jam Keluar'] ?? null;
                $status = strtolower($rowProperties['Status'] ?? 'hadir');
                if (!in_array($status, ['hadir', 'alfa', 'izin', 'sakit', 'cuti'])) {
                    $status = 'alfa';
                }

                AttendanceRecord::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'date' => $tanggal,
                    ],
                    [
                        'clock_in' => $clockIn ?: null,
                        'clock_out' => $clockOut ?: null,
                        'status' => $status,
                        'remarks' => 'Imported via CSV/Excel',
                    ]
                );
            });
            
        } catch (\Exception $e) {
            Log::error("Attendance Import failed: " . $e->getMessage());
        }
    }
}
