<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class ScheduledReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $report;
    public $reportFile;
    public $reportData;
    public $generatedAt;

    public function __construct($report, $reportFile, $reportData)
    {
        $this->report = $report;
        $this->reportFile = $reportFile;
        $this->reportData = $reportData;
        $this->generatedAt = Carbon::now();
    }

    public function build()
    {
        $subject = $this->report->email_subject ?: "Scheduled Report: {$this->report->report_type}";
        
        $mail = $this->subject($subject)
                    ->view('emails.scheduled-report')
                    ->with([
                        'report' => $this->report,
                        'reportData' => $this->reportData,
                        'generatedAt' => $this->generatedAt
                    ]);

        // Attach the report file
        if (file_exists($this->reportFile)) {
            $filename = basename($this->reportFile);
            $mail->attach($this->reportFile, [
                'as' => $filename,
                'mime' => $this->getMimeType($this->reportFile)
            ]);
        }

        return $mail;
    }

    private function getMimeType($filepath)
    {
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);
        
        switch (strtolower($extension)) {
            case 'pdf':
                return 'application/pdf';
            case 'xlsx':
                return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            case 'xls':
                return 'application/vnd.ms-excel';
            case 'csv':
                return 'text/csv';
            default:
                return 'application/octet-stream';
        }
    }
}
