<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Models\UserInvoiceProfile;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingWithInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected User $staff;

    protected Service $service;

    protected VehicleType $vehicleType;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->customer = User::factory()->create();
        $this->staff = User::factory()->create();

        // Create service
        $this->service = Service::factory()->create([
            'duration_minutes' => 60,
            'price' => 100.00,
        ]);

        // Create vehicle type
        $this->vehicleType = VehicleType::factory()->create();
    }

    /**
     * Test completing booking with invoice requested.
     */
    public function test_can_complete_booking_with_invoice_company(): void
    {
        $bookingData = $this->getBaseBookingData();
        $bookingData['invoice_requested'] = true;
        $bookingData['invoice_type'] = 'company';
        $bookingData['invoice_company_name'] = 'Test Company Sp. z o.o.';
        $bookingData['invoice_nip'] = '7751001452';
        $bookingData['invoice_regon'] = '123456789';
        $bookingData['invoice_street'] = 'Testowa';
        $bookingData['invoice_street_number'] = '42A';
        $bookingData['invoice_postal_code'] = '60-123';
        $bookingData['invoice_city'] = 'Poznań';
        $bookingData['invoice_country'] = 'PL';

        // Store data in session
        session(['booking' => $bookingData]);

        $response = $this->actingAs($this->customer)->post(route('booking.confirm'));

        $response->assertRedirect(route('booking.confirmation'));

        // Verify appointment was created with invoice data
        $this->assertDatabaseHas('appointments', [
            'customer_id' => $this->customer->id,
            'service_id' => $this->service->id,
            'invoice_requested' => true,
            'invoice_type' => 'company',
            'invoice_company_name' => 'Test Company Sp. z o.o.',
            'invoice_nip' => '7751001452',
            'invoice_city' => 'Poznań',
        ]);
    }

    /**
     * Test completing booking with invoice for individual.
     */
    public function test_can_complete_booking_with_invoice_individual(): void
    {
        $bookingData = $this->getBaseBookingData();
        $bookingData['invoice_requested'] = true;
        $bookingData['invoice_type'] = 'individual';
        $bookingData['invoice_street'] = 'Domowa';
        $bookingData['invoice_street_number'] = '10';
        $bookingData['invoice_postal_code'] = '60-456';
        $bookingData['invoice_city'] = 'Warszawa';
        $bookingData['invoice_country'] = 'PL';

        session(['booking' => $bookingData]);

        $response = $this->actingAs($this->customer)->post(route('booking.confirm'));

        $response->assertRedirect(route('booking.confirmation'));

        $this->assertDatabaseHas('appointments', [
            'customer_id' => $this->customer->id,
            'invoice_requested' => true,
            'invoice_type' => 'individual',
            'invoice_company_name' => null,
            'invoice_nip' => null,
            'invoice_city' => 'Warszawa',
        ]);
    }

    /**
     * Test completing booking without invoice.
     */
    public function test_can_complete_booking_without_invoice(): void
    {
        $bookingData = $this->getBaseBookingData();
        $bookingData['invoice_requested'] = false;

        session(['booking' => $bookingData]);

        $response = $this->actingAs($this->customer)->post(route('booking.confirm'));

        $response->assertRedirect(route('booking.confirmation'));

        $this->assertDatabaseHas('appointments', [
            'customer_id' => $this->customer->id,
            'invoice_requested' => false,
            'invoice_type' => null,
            'invoice_company_name' => null,
        ]);
    }

    /**
     * Test saving invoice profile when user opts in.
     */
    public function test_saves_invoice_profile_when_user_opts_in(): void
    {
        $bookingData = $this->getBaseBookingData();
        $bookingData['invoice_requested'] = true;
        $bookingData['invoice_type'] = 'company';
        $bookingData['invoice_company_name'] = 'Profile Company';
        $bookingData['invoice_nip'] = '7751001452';
        $bookingData['invoice_street'] = 'Testowa';
        $bookingData['invoice_postal_code'] = '60-123';
        $bookingData['invoice_city'] = 'Poznań';
        $bookingData['invoice_country'] = 'PL';
        $bookingData['save_invoice_profile'] = true; // User opted in

        session(['booking' => $bookingData]);

        $response = $this->actingAs($this->customer)->post(route('booking.confirm'));

        $response->assertRedirect(route('booking.confirmation'));

        // Verify invoice profile was created
        $this->assertDatabaseHas('user_invoice_profiles', [
            'user_id' => $this->customer->id,
            'type' => 'company',
            'company_name' => 'Profile Company',
            'nip' => '7751001452',
        ]);

        // Verify GDPR consent tracking
        $profile = $this->customer->fresh()->invoiceProfile;
        $this->assertNotNull($profile->consent_given_at);
        $this->assertNotNull($profile->consent_ip);
        $this->assertNotNull($profile->consent_user_agent);
    }

    /**
     * Test NOT saving invoice profile when user does not opt in.
     */
    public function test_does_not_save_invoice_profile_when_user_does_not_opt_in(): void
    {
        $bookingData = $this->getBaseBookingData();
        $bookingData['invoice_requested'] = true;
        $bookingData['invoice_type'] = 'company';
        $bookingData['invoice_company_name'] = 'No Save Company';
        $bookingData['invoice_nip'] = '7751001452';
        $bookingData['invoice_street'] = 'Testowa';
        $bookingData['invoice_postal_code'] = '60-123';
        $bookingData['invoice_city'] = 'Poznań';
        $bookingData['invoice_country'] = 'PL';
        $bookingData['save_invoice_profile'] = false; // User did NOT opt in

        session(['booking' => $bookingData]);

        $response = $this->actingAs($this->customer)->post(route('booking.confirm'));

        $response->assertRedirect(route('booking.confirmation'));

        // Verify invoice profile was NOT created
        $this->assertDatabaseMissing('user_invoice_profiles', [
            'user_id' => $this->customer->id,
        ]);
    }

    /**
     * Test invoice data is pre-filled from existing profile.
     */
    public function test_invoice_data_prefills_from_existing_profile(): void
    {
        // Create existing invoice profile
        $profile = UserInvoiceProfile::factory()->company()->create([
            'user_id' => $this->customer->id,
            'company_name' => 'Existing Company',
            'nip' => '7751001452',
            'street' => 'Existing Street',
            'city' => 'Existing City',
        ]);

        $response = $this->actingAs($this->customer)->get(route('booking.step', 4));

        $response->assertStatus(200);

        // Verify session was populated with profile data
        $booking = session('booking');
        $this->assertEquals('company', $booking['invoice_type']);
        $this->assertEquals('Existing Company', $booking['invoice_company_name']);
        $this->assertEquals('775-100-14-54', $booking['invoice_nip']); // Formatted
        $this->assertEquals('Existing Street', $booking['invoice_street']);
        $this->assertEquals('Existing City', $booking['invoice_city']);
    }

    /**
     * Test invoice snapshot is immutable (not affected by profile changes).
     */
    public function test_invoice_snapshot_is_immutable(): void
    {
        $bookingData = $this->getBaseBookingData();
        $bookingData['invoice_requested'] = true;
        $bookingData['invoice_type'] = 'company';
        $bookingData['invoice_company_name'] = 'Original Company';
        $bookingData['invoice_nip'] = '7751001452';
        $bookingData['invoice_street'] = 'Original Street';
        $bookingData['invoice_postal_code'] = '60-123';
        $bookingData['invoice_city'] = 'Original City';
        $bookingData['invoice_country'] = 'PL';
        $bookingData['save_invoice_profile'] = true;

        session(['booking' => $bookingData]);

        $this->actingAs($this->customer)->post(route('booking.confirm'));

        $appointment = Appointment::where('customer_id', $this->customer->id)->first();

        // Now update the invoice profile
        $profile = $this->customer->fresh()->invoiceProfile;
        $profile->update([
            'company_name' => 'Updated Company',
            'street' => 'Updated Street',
            'city' => 'Updated City',
        ]);

        // Verify appointment still has original data (snapshot)
        $appointment->refresh();
        $this->assertEquals('Original Company', $appointment->invoice_company_name);
        $this->assertEquals('Original Street', $appointment->invoice_street);
        $this->assertEquals('Original City', $appointment->invoice_city);
    }

    /**
     * Test formatted invoice address accessor.
     */
    public function test_appointment_formatted_invoice_address(): void
    {
        $appointment = Appointment::factory()->create([
            'invoice_requested' => true,
            'invoice_street' => 'Testowa',
            'invoice_street_number' => '42A',
            'invoice_postal_code' => '60-123',
            'invoice_city' => 'Poznań',
            'invoice_country' => 'PL',
        ]);

        $expected = 'Testowa 42A, 60-123 Poznań, Polska';
        $this->assertEquals($expected, $appointment->formatted_invoice_address);
    }

    /**
     * Test formatted invoice address returns null when no invoice requested.
     */
    public function test_formatted_invoice_address_returns_null_when_no_invoice(): void
    {
        $appointment = Appointment::factory()->create([
            'invoice_requested' => false,
        ]);

        $this->assertNull($appointment->formatted_invoice_address);
    }

    /**
     * Helper: Get base booking data.
     */
    protected function getBaseBookingData(): array
    {
        return [
            'service_id' => $this->service->id,
            'staff_id' => $this->staff->id,
            'appointment_date' => now()->addDays(3)->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'vehicle_type_id' => $this->vehicleType->id,
            'vehicle_brand' => 'Toyota',
            'vehicle_model' => 'Corolla',
            'vehicle_year' => 2020,
            'location_address' => 'Test Address 123, 60-123 Poznań',
            'location_latitude' => 52.4064,
            'location_longitude' => 16.9252,
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
            'phone' => '123456789',
            'notify_email' => true,
            'notify_sms' => true,
        ];
    }
}
