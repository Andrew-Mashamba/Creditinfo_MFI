<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class SystemReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reportData;
    public $reportName;
    public $recipientName;
    public $reportDescription;
    public $generatedAt;

    /**
     * Create a new message instance.
     *
     * @param array $reportData - The report data array
     * @param string $reportName - Name of the report
     * @param string $recipientName - Name of the recipient
     * @param string $reportDescription - Description of the report
     */
    public function __construct(
        array $reportData,
        string $reportName,
        string $recipientName = 'User',
        string $reportDescription = ''
    ) {
        $this->reportData = $reportData;
        $this->reportName = $reportName;
        $this->recipientName = $recipientName;
        $this->reportDescription = $reportDescription;
        $this->generatedAt = Carbon::now();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $reportType = $this->reportData['report_type'] ?? $this->reportName;
        $subject = $this->getSubjectLine($reportType);

        return $this->view('emails.system-report')
                    ->subject($subject)
                    ->with([
                        'reportData' => $this->reportData,
                        'reportName' => $this->reportName,
                        'recipientName' => $this->recipientName,
                        'reportDescription' => $this->reportDescription,
                        'generatedAt' => $this->generatedAt,
                        'reportType' => $reportType,
                    ]);
    }

    /**
     * Get appropriate subject line based on report type
     */
    protected function getSubjectLine(string $reportType): string
    {
        $date = $this->generatedAt->format('Y-m-d');

        return match (true) {
            str_contains(strtolower($reportType), 'daily') => "[Daily Report] {$this->reportName} - {$date}",
            str_contains(strtolower($reportType), 'weekly') => "[Weekly Report] {$this->reportName} - Week of {$date}",
            str_contains(strtolower($reportType), 'monthly') => "[Monthly Report] {$this->reportName} - " . $this->generatedAt->format('F Y'),
            str_contains(strtolower($reportType), 'quarterly') => "[Quarterly Report] {$this->reportName} - " . $this->getQuarterString(),
            str_contains(strtolower($reportType), 'regulatory') => "[Regulatory] {$this->reportName} - " . $this->getQuarterString(),
            default => "[Report] {$this->reportName} - {$date}",
        };
    }

    /**
     * Get quarter string representation
     */
    protected function getQuarterString(): string
    {
        $quarter = ceil($this->generatedAt->month / 3);
        return "Q{$quarter} " . $this->generatedAt->year;
    }
}
