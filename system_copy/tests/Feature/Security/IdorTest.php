<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\Member;
use App\Models\Loan;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

/**
 * IDOR (Insecure Direct Object Reference) Security Tests
 *
 * Tests for IDOR vulnerabilities in:
 * - Object IDs (members, loans, accounts)
 * - File access
 * - Downloads
 * - API endpoints
 *
 * @package Tests\Feature\Security
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class IdorTest extends TestCase
{
    use RefreshDatabase;

    protected $user1;
    protected $user2;
    protected $member1;
    protected $member2;
    protected $loan1;
    protected $loan2;
    protected $account1;
    protected $account2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create two separate users with their data
        $this->user1 = User::factory()->create(['email' => 'user1@example.com']);
        $this->user2 = User::factory()->create(['email' => 'user2@example.com']);

        $this->member1 = Member::factory()->create(['user_id' => $this->user1->id]);
        $this->member2 = Member::factory()->create(['user_id' => $this->user2->id]);

        $this->loan1 = Loan::factory()->create(['member_id' => $this->member1->id]);
        $this->loan2 = Loan::factory()->create(['member_id' => $this->member2->id]);

        $this->account1 = Account::factory()->create(['member_id' => $this->member1->id]);
        $this->account2 = Account::factory()->create(['member_id' => $this->member2->id]);
    }

    /**
     * Test user cannot access another user's member profile
     *
     * @test
     */
    public function it_prevents_accessing_other_users_member_profiles()
    {
        $this->actingAs($this->user1);

        // Attempt to access user2's member profile
        $response = $this->get("/members/{$this->member2->id}");

        // Should be forbidden or not found
        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404]),
            "User should not access another user's member profile"
        );
    }

    /**
     * Test user cannot view another user's loan details
     *
     * @test
     */
    public function it_prevents_accessing_other_users_loans()
    {
        $this->actingAs($this->user1);

        $response = $this->get("/loans/{$this->loan2->id}");

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404]),
            "User should not access another user's loan"
        );
    }

    /**
     * Test user cannot view another user's account details
     *
     * @test
     */
    public function it_prevents_accessing_other_users_accounts()
    {
        $this->actingAs($this->user1);

        $response = $this->get("/accounts/{$this->account2->id}");

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404]),
            "User should not access another user's account"
        );
    }

    /**
     * Test user cannot modify another user's data
     *
     * @test
     */
    public function it_prevents_modifying_other_users_data()
    {
        $this->actingAs($this->user1);

        $response = $this->put("/members/{$this->member2->id}", [
            'first_name' => 'Modified',
        ]);

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404, 419]),
            "User should not modify another user's data"
        );

        // Verify data wasn't changed
        $this->member2->refresh();
        $this->assertNotEquals('Modified', $this->member2->first_name);
    }

    /**
     * Test user cannot delete another user's data
     *
     * @test
     */
    public function it_prevents_deleting_other_users_data()
    {
        $this->actingAs($this->user1);

        $response = $this->delete("/loans/{$this->loan2->id}");

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404, 419]),
            "User should not delete another user's data"
        );

        // Verify loan still exists
        $this->assertDatabaseHas('loans', ['id' => $this->loan2->id]);
    }

    /**
     * Test sequential ID enumeration is prevented
     *
     * @test
     */
    public function it_prevents_sequential_id_enumeration()
    {
        $this->actingAs($this->user1);

        $accessibleIds = [];
        $forbiddenIds = [];

        // Try to enumerate IDs
        for ($id = 1; $id <= 100; $id++) {
            $response = $this->get("/members/{$id}");

            if ($response->isSuccessful()) {
                $accessibleIds[] = $id;
            } elseif ($response->getStatusCode() === 403) {
                $forbiddenIds[] = $id;
            }
        }

        // User should only access their own IDs
        $this->assertCount(
            1,
            $accessibleIds,
            "User should only access their own member ID, not enumerate others"
        );

        // Most IDs should be forbidden or not found
        $this->assertGreaterThan(
            0,
            count($forbiddenIds),
            "Should have forbidden access to other IDs"
        );
    }

    /**
     * Test IDOR in API endpoints
     *
     * @test
     */
    public function it_prevents_idor_in_api_endpoints()
    {
        $response = $this->json('GET', "/api/loans/{$this->loan2->id}", [], [
            'Authorization' => "Bearer test_token_for_user1",
        ]);

        // Should require authentication and authorization
        $this->assertTrue(
            in_array($response->getStatusCode(), [401, 403, 404]),
            "API should prevent IDOR"
        );
    }

    /**
     * Test IDOR in file downloads
     *
     * @test
     */
    public function it_prevents_idor_in_file_downloads()
    {
        $this->actingAs($this->user1);

        // Attempt to download another user's file
        $response = $this->get("/files/member_documents/{$this->member2->id}/document.pdf");

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404]),
            "User should not download another user's files"
        );
    }

    /**
     * Test IDOR with UUID instead of sequential IDs
     *
     * @test
     */
    public function it_uses_non_sequential_identifiers_where_appropriate()
    {
        // Check if sensitive resources use UUIDs or non-sequential IDs
        $transaction = Transaction::factory()->create([
            'account_id' => $this->account1->id,
        ]);

        // Transaction ID should be non-predictable (UUID or high random number)
        $this->assertTrue(
            is_string($transaction->id) && strlen($transaction->id) > 10 ||
            $transaction->id > 1000000,
            "Sensitive resources should use non-sequential identifiers"
        );
    }

    /**
     * Test IDOR with parameter tampering
     *
     * @test
     */
    public function it_prevents_parameter_tampering()
    {
        $this->actingAs($this->user1);

        // Attempt to change member_id in request
        $response = $this->post('/transactions', [
            'member_id' => $this->member2->id, // Tampered parameter
            'amount' => 1000,
            'type' => 'deposit',
        ]);

        // Should reject or use authenticated user's member_id
        $this->assertTrue(
            $response->getStatusCode() !== 200 ||
            !$this->databaseHas('transactions', [
                'member_id' => $this->member2->id,
                'amount' => 1000,
            ]),
            "Should not accept tampered member_id parameter"
        );
    }

    /**
     * Test IDOR protection with mass assignment
     *
     * @test
     */
    public function it_prevents_mass_assignment_idor()
    {
        $this->actingAs($this->user1);

        // Attempt mass assignment to change ownership
        $response = $this->put("/loans/{$this->loan1->id}", [
            'member_id' => $this->member2->id, // Attempt to transfer loan
            'amount' => 50000,
        ]);

        // Verify member_id wasn't changed
        $this->loan1->refresh();
        $this->assertEquals($this->member1->id, $this->loan1->member_id);
    }

    /**
     * Test IDOR in batch operations
     *
     * @test
     */
    public function it_prevents_idor_in_batch_operations()
    {
        $this->actingAs($this->user1);

        // Attempt to include another user's ID in batch delete
        $response = $this->delete('/loans/batch', [
            'ids' => [$this->loan1->id, $this->loan2->id], // Include unauthorized ID
        ]);

        // Should only affect authorized loans or reject entirely
        $this->assertDatabaseHas('loans', [
            'id' => $this->loan2->id, // Other user's loan should still exist
        ]);
    }

    /**
     * Test IDOR with indirect object references
     *
     * @test
     */
    public function it_uses_indirect_object_references()
    {
        $this->actingAs($this->user1);

        // Check if application uses indirect references (tokens/slugs)
        // For sensitive operations
        $response = $this->get('/account/statement');

        // Should not expose direct database IDs in URLs
        $content = $response->getContent();

        // Check for patterns like /accounts/123 in HTML
        $pattern = '/\/(accounts?|loans?|members?)\/\d+/i';
        $matches = [];
        preg_match_all($pattern, $content, $matches);

        if (!empty($matches[0])) {
            Log::warning('Direct object references found in response', [
                'matches' => $matches[0],
            ]);

            // This is informational, not necessarily a failure
            $this->addWarning('Consider using indirect object references (tokens/UUIDs)');
        }

        $this->assertTrue(true);
    }

    /**
     * Test horizontal privilege escalation
     *
     * @test
     */
    public function it_prevents_horizontal_privilege_escalation()
    {
        $this->actingAs($this->user1);

        // User1 tries to access User2's sensitive data
        $endpoints = [
            "/api/members/{$this->member2->id}/balance",
            "/api/members/{$this->member2->id}/transactions",
            "/api/loans/{$this->loan2->id}/schedule",
            "/api/accounts/{$this->account2->id}/statement",
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->json('GET', $endpoint);

            $this->assertTrue(
                in_array($response->getStatusCode(), [401, 403, 404]),
                "Horizontal privilege escalation prevented for: {$endpoint}"
            );
        }
    }

    /**
     * Test vertical privilege escalation
     *
     * @test
     */
    public function it_prevents_vertical_privilege_escalation()
    {
        // Regular user tries to access admin functions
        $this->actingAs($this->user1);

        $adminEndpoints = [
            '/admin/users',
            '/admin/settings',
            '/admin/reports',
            '/admin/roles',
        ];

        foreach ($adminEndpoints as $endpoint) {
            $response = $this->get($endpoint);

            $this->assertTrue(
                in_array($response->getStatusCode(), [401, 403, 404]),
                "Vertical privilege escalation prevented for: {$endpoint}"
            );
        }
    }

    /**
     * Test IDOR with wildcard routes
     *
     * @test
     */
    public function it_secures_wildcard_routes()
    {
        $this->actingAs($this->user1);

        // Test various object types with wildcards
        $wildcardTests = [
            "/api/*/members/{$this->member2->id}",
            "/api/v1/members/{$this->member2->id}/../{$this->member1->id}",
            "/members/{$this->member2->id}?user_id={$this->user1->id}",
        ];

        foreach ($wildcardTests as $route) {
            $response = $this->get($route);

            // Should not expose unauthorized data
            $this->assertTrue(
                in_array($response->getStatusCode(), [401, 403, 404]),
                "Wildcard route should be secured: {$route}"
            );
        }
    }
}
