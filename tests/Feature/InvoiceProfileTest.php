<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserInvoiceProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that authenticated user can view invoice profile page.
     */
    public function test_authenticated_user_can_view_invoice_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.invoice'));

        $response->assertStatus(200);
        $response->assertViewIs('profile.pages.invoice');
        $response->assertViewHas('user');
        $response->assertViewHas('invoiceProfile');
    }

    /**
     * Test that guest cannot access invoice profile page.
     */
    public function test_guest_cannot_access_invoice_page(): void
    {
        $response = $this->get(route('profile.invoice'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test creating invoice profile with valid company data.
     */
    public function test_can_create_invoice_profile_for_company(): void
    {
        $user = User::factory()->create();

        $invoiceData = [
            'type' => 'company',
            'company_name' => 'Test Company Sp. z o.o.',
            'nip' => '7751001452',
            'regon' => '123456789',
            'street' => 'Testowa',
            'street_number' => '42A',
            'postal_code' => '60-123',
            'city' => 'Poznań',
            'country' => 'PL',
        ];

        $response = $this->actingAs($user)->post(route('profile.invoice.store'), $invoiceData);

        $response->assertRedirect(route('profile.invoice'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('user_invoice_profiles', [
            'user_id' => $user->id,
            'type' => 'company',
            'company_name' => 'Test Company Sp. z o.o.',
            'nip' => '7751001452', // Stored without dashes
            'street' => 'Testowa',
            'city' => 'Poznań',
        ]);

        // Verify GDPR consent tracking
        $profile = $user->fresh()->invoiceProfile;
        $this->assertNotNull($profile->consent_given_at);
        $this->assertNotNull($profile->consent_ip);
        $this->assertNotNull($profile->consent_user_agent);
    }

    /**
     * Test creating invoice profile for individual (no company fields).
     */
    public function test_can_create_invoice_profile_for_individual(): void
    {
        $user = User::factory()->create();

        $invoiceData = [
            'type' => 'individual',
            'street' => 'Testowa',
            'street_number' => '10',
            'postal_code' => '60-123',
            'city' => 'Poznań',
            'country' => 'PL',
        ];

        $response = $this->actingAs($user)->post(route('profile.invoice.store'), $invoiceData);

        $response->assertRedirect(route('profile.invoice'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('user_invoice_profiles', [
            'user_id' => $user->id,
            'type' => 'individual',
            'company_name' => null,
            'nip' => null,
        ]);
    }

    /**
     * Test creating invoice profile with foreign EU company.
     */
    public function test_can_create_invoice_profile_for_foreign_eu(): void
    {
        $user = User::factory()->create();

        $invoiceData = [
            'type' => 'foreign_eu',
            'company_name' => 'German Company GmbH',
            'vat_id' => 'DE123456789',
            'street' => 'Teststraße',
            'street_number' => '5',
            'postal_code' => '10-115', // Polish format (XX-XXX) for validation
            'city' => 'Berlin',
            'country' => 'DE',
        ];

        $response = $this->actingAs($user)->post(route('profile.invoice.store'), $invoiceData);

        $response->assertRedirect(route('profile.invoice'));

        $this->assertDatabaseHas('user_invoice_profiles', [
            'user_id' => $user->id,
            'type' => 'foreign_eu',
            'vat_id' => 'DE123456789',
        ]);
    }

    /**
     * Test that NIP validation fails with invalid checksum.
     */
    public function test_rejects_invalid_nip_checksum(): void
    {
        $user = User::factory()->create();

        $invoiceData = [
            'type' => 'company',
            'company_name' => 'Test Company',
            'nip' => '7751001455', // Invalid checksum
            'street' => 'Testowa',
            'postal_code' => '60-123',
            'city' => 'Poznań',
            'country' => 'PL',
        ];

        $response = $this->actingAs($user)->post(route('profile.invoice.store'), $invoiceData);

        $response->assertSessionHasErrors('nip');
        $this->assertDatabaseMissing('user_invoice_profiles', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test that company name is required for company type.
     */
    public function test_company_name_required_for_company_type(): void
    {
        $user = User::factory()->create();

        $invoiceData = [
            'type' => 'company',
            'nip' => '7751001452',
            'street' => 'Testowa',
            'postal_code' => '60-123',
            'city' => 'Poznań',
            'country' => 'PL',
            // Missing company_name
        ];

        $response = $this->actingAs($user)->post(route('profile.invoice.store'), $invoiceData);

        $response->assertSessionHasErrors('company_name');
    }

    /**
     * Test that NIP is required for company type.
     */
    public function test_nip_required_for_company_type(): void
    {
        $user = User::factory()->create();

        $invoiceData = [
            'type' => 'company',
            'company_name' => 'Test Company',
            'street' => 'Testowa',
            'postal_code' => '60-123',
            'city' => 'Poznań',
            'country' => 'PL',
            // Missing NIP
        ];

        $response = $this->actingAs($user)->post(route('profile.invoice.store'), $invoiceData);

        $response->assertSessionHasErrors('nip');
    }

    /**
     * Test updating existing invoice profile.
     */
    public function test_can_update_invoice_profile(): void
    {
        $user = User::factory()->create();
        $profile = UserInvoiceProfile::factory()->create([
            'user_id' => $user->id,
            'type' => 'individual',
            'street' => 'Old Street',
            'city' => 'Old City',
        ]);

        $updatedData = [
            'type' => 'company',
            'company_name' => 'Updated Company',
            'nip' => '7751001452',
            'street' => 'New Street',
            'street_number' => '99',
            'postal_code' => '60-999',
            'city' => 'New City',
            'country' => 'PL',
        ];

        $response = $this->actingAs($user)->patch(route('profile.invoice.update'), $updatedData);

        $response->assertRedirect(route('profile.invoice'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('user_invoice_profiles', [
            'id' => $profile->id,
            'user_id' => $user->id,
            'type' => 'company',
            'company_name' => 'Updated Company',
            'city' => 'New City',
        ]);
    }

    /**
     * Test deleting invoice profile.
     */
    public function test_can_delete_invoice_profile(): void
    {
        $user = User::factory()->create();
        $profile = UserInvoiceProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('profile.invoice.destroy'));

        $response->assertRedirect(route('profile.invoice'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('user_invoice_profiles', [
            'id' => $profile->id,
        ]);
    }

    /**
     * Test deleting non-existent profile returns error.
     */
    public function test_deleting_non_existent_profile_returns_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('profile.invoice.destroy'));

        $response->assertRedirect(route('profile.invoice'));
        $response->assertSessionHas('error');
    }

    /**
     * Test that user can only have one invoice profile (unique constraint).
     */
    public function test_user_can_only_have_one_invoice_profile(): void
    {
        $user = User::factory()->create();

        // Create first profile
        UserInvoiceProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        // Attempt to create second profile should fail
        $this->expectException(\Illuminate\Database\QueryException::class);

        UserInvoiceProfile::factory()->create([
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test NIP accessor formats correctly.
     */
    public function test_nip_accessor_formats_with_dashes(): void
    {
        $user = User::factory()->create();
        $profile = UserInvoiceProfile::factory()->create([
            'user_id' => $user->id,
            'type' => 'company',
            'nip' => '7751001452', // Stored without dashes
        ]);

        // Accessor should add dashes
        $this->assertEquals('775-100-14-52', $profile->nip);
    }

    /**
     * Test NIP mutator strips non-digits.
     */
    public function test_nip_mutator_strips_non_digits(): void
    {
        $user = User::factory()->create();
        $profile = UserInvoiceProfile::factory()->create([
            'user_id' => $user->id,
            'type' => 'company',
            'nip' => '775-100-14-52', // With dashes
        ]);

        // Should be stored without dashes
        $this->assertDatabaseHas('user_invoice_profiles', [
            'id' => $profile->id,
            'nip' => '7751001452',
        ]);
    }

    /**
     * Test formatted address accessor.
     */
    public function test_formatted_address_accessor(): void
    {
        $user = User::factory()->create();
        $profile = UserInvoiceProfile::factory()->create([
            'user_id' => $user->id,
            'street' => 'Poznańska',
            'street_number' => '42A',
            'postal_code' => '60-123',
            'city' => 'Poznań',
            'country' => 'PL',
        ]);

        $expected = 'Poznańska 42A, 60-123 Poznań, Polska';
        $this->assertEquals($expected, $profile->formatted_address);
    }

    /**
     * Test postal code validation.
     */
    public function test_rejects_invalid_postal_code_format(): void
    {
        $user = User::factory()->create();

        $invoiceData = [
            'type' => 'individual',
            'street' => 'Testowa',
            'postal_code' => '12345', // Missing dash
            'city' => 'Poznań',
            'country' => 'PL',
        ];

        $response = $this->actingAs($user)->post(route('profile.invoice.store'), $invoiceData);

        $response->assertSessionHasErrors('postal_code');
    }
}
