<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailReceiptService
{
    /**
     * Process read receipt for an email
     */
    public function processReadReceipt($emailId, $readerId)
    {
        try {
            $email = DB::table('emails')
                ->where('id', $emailId)
                ->first();
                
            if (!$email || !$email->request_read_receipt) {
                return ['success' => false, 'message' => 'Email not found or read receipt not requested'];
            }
            
            // Check if receipt already sent
            if ($email->read_receipt_sent_at) {
                return ['success' => false, 'message' => 'Read receipt already sent'];
            }
            
            // Send receipt notification
            $this->sendReadReceiptNotification($email, $readerId);
            
            // Update email record
            DB::table('emails')
                ->where('id', $emailId)
                ->update([
                    'read_receipt_sent_at' => now(),
                    'is_read' => true
                ]);
                
            // Log the event
            DB::table('email_receipts')->insert([
                'email_id' => $emailId,
                'type' => 'read',
                'reader_id' => $readerId,
                'reader_email' => DB::table('users')->where('id', $readerId)->value('email'),
                'created_at' => now()
            ]);
            
            return ['success' => true, 'message' => 'Read receipt processed'];
        } catch (\Exception $e) {
            Log::error('Failed to process read receipt: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to process read receipt'];
        }
    }
    
    /**
     * Process delivery receipt for an email
     */
    public function processDeliveryReceipt($emailId)
    {
        try {
            $email = DB::table('emails')
                ->where('id', $emailId)
                ->first();
                
            if (!$email || !$email->request_delivery_receipt) {
                return ['success' => false, 'message' => 'Email not found or delivery receipt not requested'];
            }
            
            // Check if receipt already sent
            if ($email->delivery_receipt_sent_at) {
                return ['success' => false, 'message' => 'Delivery receipt already sent'];
            }
            
            // Send receipt notification
            $this->sendDeliveryReceiptNotification($email);
            
            // Update email record
            DB::table('emails')
                ->where('id', $emailId)
                ->update([
                    'delivery_receipt_sent_at' => now()
                ]);
                
            // Log the event
            DB::table('email_receipts')->insert([
                'email_id' => $emailId,
                'type' => 'delivery',
                'created_at' => now()
            ]);
            
            return ['success' => true, 'message' => 'Delivery receipt processed'];
        } catch (\Exception $e) {
            Log::error('Failed to process delivery receipt: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to process delivery receipt'];
        }
    }
    
    /**
     * Send read receipt notification to sender
     */
    protected function sendReadReceiptNotification($email, $readerId)
    {
        $reader = DB::table('users')->where('id', $readerId)->first();
        $sender = DB::table('users')->where('id', $email->sender_id)->first();
        
        if (!$sender) {
            return;
        }
        
        // Create notification in database
        DB::table('notifications')->insert([
            'id' => Str::uuid(),
            'type' => 'EmailReadReceipt',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $email->sender_id,
            'data' => json_encode([
                'email_id' => $email->id,
                'subject' => $email->subject,
                'reader_name' => $reader->name ?? 'Unknown',
                'reader_email' => $reader->email ?? $email->recipient_email,
                'read_at' => now()->toDateTimeString()
            ]),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Optionally send email notification
        if ($sender->email_notifications ?? true) {
            // Mail::to($sender->email)->send(new ReadReceiptNotification($email, $reader));
        }
    }
    
    /**
     * Send delivery receipt notification to sender
     */
    protected function sendDeliveryReceiptNotification($email)
    {
        $sender = DB::table('users')->where('id', $email->sender_id)->first();
        
        if (!$sender) {
            return;
        }
        
        // Create notification in database
        DB::table('notifications')->insert([
            'id' => Str::uuid(),
            'type' => 'EmailDeliveryReceipt',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $email->sender_id,
            'data' => json_encode([
                'email_id' => $email->id,
                'subject' => $email->subject,
                'recipient_email' => $email->recipient_email,
                'delivered_at' => now()->toDateTimeString()
            ]),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    /**
     * Get receipt status for an email
     */
    public function getReceiptStatus($emailId)
    {
        $email = DB::table('emails')
            ->where('id', $emailId)
            ->first();
            
        if (!$email) {
            return null;
        }
        
        $receipts = DB::table('email_receipts')
            ->where('email_id', $emailId)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return [
            'request_read_receipt' => $email->request_read_receipt,
            'request_delivery_receipt' => $email->request_delivery_receipt,
            'read_receipt_sent' => !is_null($email->read_receipt_sent_at),
            'delivery_receipt_sent' => !is_null($email->delivery_receipt_sent_at),
            'receipts' => $receipts
        ];
    }
    
    /**
     * Handle incoming receipt request
     */
    public function handleReceiptRequest($emailId, $type)
    {
        if ($type === 'read') {
            // Show confirmation dialog to user
            return [
                'type' => 'read_receipt_request',
                'email_id' => $emailId,
                'message' => 'The sender has requested a read receipt. Would you like to send it?'
            ];
        }
        
        if ($type === 'delivery') {
            // Automatically send delivery receipt
            return $this->processDeliveryReceipt($emailId);
        }
        
        return ['success' => false, 'message' => 'Invalid receipt type'];
    }
    
    /**
     * Add receipt headers to outgoing email
     */
    public function addReceiptHeaders($message, $email)
    {
        if ($email['request_read_receipt'] ?? false) {
            $message->getHeaders()->addTextHeader('Disposition-Notification-To', $email['sender_email']);
            $message->getHeaders()->addTextHeader('X-Confirm-Reading-To', $email['sender_email']);
        }
        
        if ($email['request_delivery_receipt'] ?? false) {
            $message->getHeaders()->addTextHeader('Return-Receipt-To', $email['sender_email']);
        }
        
        return $message;
    }
}