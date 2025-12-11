<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\ScheduledReport;

class ScheduledReportEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $report;
    public $pdfPath;
    public $config;

    /**
     * Create a new message instance.
     */
    public function __construct(ScheduledReport $report, $pdfPath, $config)
    {
        $this->report = $report;
        $this->pdfPath = $pdfPath;
        $this->config = $config;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->config['emailSubject'] ?? 'Scheduled Report - ' . $this->report->report_type;
        
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.scheduled-report',
            with: [
                'reportType' => $this->report->report_type,
                'frequency' => $this->report->frequency,
                'scheduledAt' => $this->report->scheduled_at,
                'customMessage' => $this->config['emailMessage'] ?? '',
                'reportPeriod' => $this->config['reportPeriod'] ?? 'monthly',
                'startDate' => $this->config['startDate'] ?? '',
                'endDate' => $this->config['endDate'] ?? '',
                'currency' => $this->config['currency'] ?? 'TZS',
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];
        
        if ($this->pdfPath && Storage::exists($this->pdfPath)) {
            $filename = 'Report-' . now()->format('Y-m-d') . '.pdf';
            
            $attachments[] = Attachment::fromStorage($this->pdfPath)
                ->as($filename)
                ->withMime('application/pdf');
        }
        
        return $attachments;
    }
}
