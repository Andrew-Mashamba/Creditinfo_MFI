<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use App\Http\Livewire\Email\Email;

class EmailSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $emailService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->emailService = new EmailService();
    }

    /** @test */
    public function user_can_compose_and_send_email()
    {
        $this->actingAs($this->user);

        $emailData = [
            'to' => 'recipient@example.com',
            'subject' => 'Test Email',
            'body' => 'This is a test email body',
            'cc' => 'cc@example.com',
            'bcc' => 'bcc@example.com'
        ];

        Mail::fake();

        $result = $this->emailService->sendEmail($emailData);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('emails', [
            'sender_id' => $this->user->id,
            'recipient_email' => 'recipient@example.com',
            'subject' => 'Test Email',
            'folder' => 'sent'
        ]);
    }

    /** @test */
    public function email_rate_limiting_works()
    {
        $this->actingAs($this->user);

        Mail::fake();

        // Send emails up to the rate limit
        for ($i = 0; $i < 5; $i++) {
            $this->emailService->sendEmail([
                'to' => "recipient{$i}@example.com",
                'subject' => "Test Email {$i}",
                'body' => 'Test body'
            ]);
        }

        // The 6th email should fail
        $result = $this->emailService->sendEmail([
            'to' => 'recipient6@example.com',
            'subject' => 'Test Email 6',
            'body' => 'Test body'
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Rate limit exceeded', $result['message']);
    }

    /** @test */
    public function spam_detection_identifies_spam_emails()
    {
        $spamEmail = [
            'subject' => 'Win FREE MONEY NOW!!!',
            'body' => 'CLICK HERE to win lottery prize! Act now! Limited time offer!',
            'sender_email' => 'spammer12345@example.com'
        ];

        $isSpam = $this->emailService->detectSpam($spamEmail);
        $this->assertTrue($isSpam);

        $legitimateEmail = [
            'subject' => 'Project Update',
            'body' => 'Here is the status update for our project.',
            'sender_email' => 'colleague@company.com'
        ];

        $isSpam = $this->emailService->detectSpam($legitimateEmail);
        $this->assertFalse($isSpam);
    }

    /** @test */
    public function user_can_search_emails_with_filters()
    {
        $this->actingAs($this->user);

        // Create test emails
        DB::table('emails')->insert([
            [
                'sender_id' => $this->user->id,
                'recipient_email' => 'test@example.com',
                'subject' => 'Important Meeting',
                'body' => 'Meeting tomorrow at 10 AM',
                'folder' => 'sent',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2)
            ],
            [
                'sender_id' => null,
                'recipient_id' => $this->user->id,
                'recipient_email' => $this->user->email,
                'subject' => 'Project Update',
                'body' => 'The project is on track',
                'folder' => 'inbox',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay()
            ]
        ]);

        Livewire::test(Email::class)
            ->set('search', 'Meeting')
            ->assertSee('Important Meeting')
            ->assertDontSee('Project Update');
    }

    /** @test */
    public function user_can_mark_emails_as_read_unread()
    {
        $this->actingAs($this->user);

        $emailId = DB::table('emails')->insertGetId([
            'recipient_id' => $this->user->id,
            'recipient_email' => $this->user->email,
            'subject' => 'Test Email',
            'body' => 'Test body',
            'folder' => 'inbox',
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Livewire::test(Email::class)
            ->call('markAsRead', $emailId);

        $this->assertDatabaseHas('emails', [
            'id' => $emailId,
            'is_read' => true
        ]);

        Livewire::test(Email::class)
            ->call('markAsUnread', $emailId);

        $this->assertDatabaseHas('emails', [
            'id' => $emailId,
            'is_read' => false
        ]);
    }

    /** @test */
    public function user_can_move_email_to_trash()
    {
        $this->actingAs($this->user);

        $emailId = DB::table('emails')->insertGetId([
            'recipient_id' => $this->user->id,
            'recipient_email' => $this->user->email,
            'subject' => 'Test Email',
            'body' => 'Test body',
            'folder' => 'inbox',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Livewire::test(Email::class)
            ->call('deleteEmail', $emailId);

        $this->assertDatabaseHas('emails', [
            'id' => $emailId,
            'folder' => 'trash'
        ]);
        
        $this->assertNotNull(DB::table('emails')->where('id', $emailId)->value('deleted_at'));
    }

    /** @test */
    public function user_can_save_draft_email()
    {
        $this->actingAs($this->user);

        Livewire::test(Email::class)
            ->set('to', 'draft@example.com')
            ->set('subject', 'Draft Email')
            ->set('body', 'This is a draft')
            ->call('saveDraft');

        $this->assertDatabaseHas('emails', [
            'sender_id' => $this->user->id,
            'recipient_email' => 'draft@example.com',
            'subject' => 'Draft Email',
            'folder' => 'drafts',
            'is_draft' => true
        ]);
    }

    /** @test */
    public function email_statistics_are_calculated_correctly()
    {
        $this->actingAs($this->user);

        // Create test emails
        DB::table('emails')->insert([
            [
                'sender_id' => $this->user->id,
                'recipient_email' => 'test@example.com',
                'subject' => 'Sent Email',
                'body' => 'Test body',
                'folder' => 'sent',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'recipient_id' => $this->user->id,
                'recipient_email' => $this->user->email,
                'subject' => 'Received Email',
                'body' => 'Test body',
                'folder' => 'inbox',
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        $stats = $this->emailService->getEmailStats($this->user->id);

        $this->assertEquals(1, $stats['total_sent']);
        $this->assertEquals(1, $stats['total_received']);
        $this->assertEquals(1, $stats['unread_count']);
        $this->assertEquals(2, $stats['emails_today']);
    }

    /** @test */
    public function old_emails_can_be_archived()
    {
        $this->actingAs($this->user);

        // Create an old email
        DB::table('emails')->insert([
            'sender_id' => $this->user->id,
            'recipient_email' => 'old@example.com',
            'subject' => 'Old Email',
            'body' => 'This is an old email',
            'folder' => 'sent',
            'created_at' => now()->subDays(100),
            'updated_at' => now()->subDays(100)
        ]);

        $archivedCount = $this->emailService->backupOldEmails(90);

        $this->assertEquals(1, $archivedCount);
        $this->assertDatabaseHas('email_archives', [
            'subject' => 'Old Email'
        ]);
        $this->assertDatabaseMissing('emails', [
            'subject' => 'Old Email'
        ]);
    }
}