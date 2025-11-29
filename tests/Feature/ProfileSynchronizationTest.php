<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileSynchronizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $staff;
    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user with empty profile
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'first_name' => null,
            'last_name' => null,
            'phone_e164' => null,
            'street_name' => null,
            'street_number' => null,
            'city' => null,
            'postal_code' => null,
            'access_notes' => null,
        ]);

        // Create test service
        $this->service = Service::factory()->create([
            'name' => 'Test Service',
            'duration_minutes' => 60,
            'price' => 100,
        ]);

        // Create staff member with availability
        $this->staff = User::factory()->create([
            'email' => 'staff@example.com',
            'email_verified_at' => now(),
        ]);
        $this->staff->assignRole('staff');

        // Create service availability for tomorrow (to meet 24h advance booking)
        $tomorrow = Carbon::tomorrow();
        $this->staff->serviceAvailabilities()->create([
            'service_id' => $this->service->id,
            'day_of_week' => $tomorrow->dayOfWeek,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);
    }

    /** @test */
    public function booking_page_displays_empty_form_for_new_user()
    {
        $response = $this->actingAs($this->user)
            ->get(route('booking.create', $this->service));

        $response->assertStatus(200);
        $response->assertViewHas('user', function ($user) {
            return $user->id === $this->user->id
                && empty($user->first_name)
                && empty($user->last_name);
        });
    }

    /** @test */
    public function first_booking_saves_profile_data()
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $bookingData = [
            'service_id' => $this->service->id,
            'appointment_date' => $tomorrow,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'phone_e164' => '+48501234567',
            'street_name' => 'Marszałkowska',
            'street_number' => '12/34',
            'city' => 'Warszawa',
            'postal_code' => '00-000',
            'access_notes' => 'Kod do bramy: 1234',
            'notes' => 'Test appointment',
        ];

        $this->actingAs($this->user)
            ->post(route('appointments.store'), $bookingData);

        // Verify profile was updated
        $this->user->refresh();
        $this->assertEquals('Jan', $this->user->first_name);
        $this->assertEquals('Kowalski', $this->user->last_name);
        $this->assertEquals('+48501234567', $this->user->phone_e164);
        $this->assertEquals('Marszałkowska', $this->user->street_name);
        $this->assertEquals('12/34', $this->user->street_number);
        $this->assertEquals('Warszawa', $this->user->city);
        $this->assertEquals('00-000', $this->user->postal_code);
        $this->assertEquals('Kod do bramy: 1234', $this->user->access_notes);
    }

    /** @test */
    public function booking_page_pre_fills_form_for_returning_user()
    {
        // Update user profile
        $this->user->update([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'phone_e164' => '+48501234567',
            'city' => 'Warszawa',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('booking.create', $this->service));

        $response->assertStatus(200);
        $response->assertSee('Jan', false);
        $response->assertSee('Kowalski', false);
        $response->assertSee('+48501234567', false);
        $response->assertSee('Warszawa', false);
    }

    /** @test */
    public function second_booking_does_not_overwrite_existing_profile_data()
    {
        // Set initial profile data
        $this->user->update([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'phone_e164' => '+48501234567',
            'street_name' => 'Marszałkowska',
            'street_number' => '12/34',
            'city' => 'Warszawa',
            'postal_code' => '00-000',
            'access_notes' => 'Kod do bramy: 1234',
        ]);

        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $bookingData = [
            'service_id' => $this->service->id,
            'appointment_date' => $tomorrow,
            'start_time' => '10:00',
            'end_time' => '11:00',
            // Different data in booking form
            'first_name' => 'Adam',
            'last_name' => 'Nowak',
            'phone_e164' => '+48600999888',
            'street_name' => 'Nowa',
            'street_number' => '99',
            'city' => 'Kraków',
            'postal_code' => '30-000',
            'access_notes' => 'Nowy kod: 9999',
            'notes' => 'Second appointment',
        ];

        $this->actingAs($this->user)
            ->post(route('appointments.store'), $bookingData);

        // Verify profile was NOT overwritten
        $this->user->refresh();
        $this->assertEquals('Jan', $this->user->first_name); // Original data preserved
        $this->assertEquals('Kowalski', $this->user->last_name);
        $this->assertEquals('+48501234567', $this->user->phone_e164);
        $this->assertEquals('Marszałkowska', $this->user->street_name);
        $this->assertEquals('12/34', $this->user->street_number);
        $this->assertEquals('Warszawa', $this->user->city);
        $this->assertEquals('00-000', $this->user->postal_code);
        $this->assertEquals('Kod do bramy: 1234', $this->user->access_notes);
    }

    /** @test */
    public function partial_profile_only_fills_empty_fields()
    {
        // User has partial profile
        $this->user->update([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'phone_e164' => '+48501234567',
            // Address fields empty
        ]);

        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $bookingData = [
            'service_id' => $this->service->id,
            'appointment_date' => $tomorrow,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'first_name' => 'Adam', // Won't overwrite
            'last_name' => 'Nowak', // Won't overwrite
            'phone_e164' => '+48600999888', // Won't overwrite
            'street_name' => 'Marszałkowska', // Will save
            'street_number' => '12/34', // Will save
            'city' => 'Warszawa', // Will save
            'postal_code' => '00-000', // Will save
            'access_notes' => 'Kod: 1234', // Will save
        ];

        $this->actingAs($this->user)
            ->post(route('appointments.store'), $bookingData);

        // Verify only empty fields were filled
        $this->user->refresh();
        $this->assertEquals('Jan', $this->user->first_name); // Not overwritten
        $this->assertEquals('Kowalski', $this->user->last_name); // Not overwritten
        $this->assertEquals('+48501234567', $this->user->phone_e164); // Not overwritten
        $this->assertEquals('Marszałkowska', $this->user->street_name); // Filled
        $this->assertEquals('12/34', $this->user->street_number); // Filled
        $this->assertEquals('Warszawa', $this->user->city); // Filled
        $this->assertEquals('00-000', $this->user->postal_code); // Filled
        $this->assertEquals('Kod: 1234', $this->user->access_notes); // Filled
    }

    /** @test */
    public function optional_address_fields_only_save_when_provided()
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $bookingData = [
            'service_id' => $this->service->id,
            'appointment_date' => $tomorrow,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'phone_e164' => '+48501234567',
            // Optional address fields omitted
            'notes' => 'Test appointment',
        ];

        $this->actingAs($this->user)
            ->post(route('appointments.store'), $bookingData);

        // Verify required fields saved, optional fields remain null
        $this->user->refresh();
        $this->assertEquals('Jan', $this->user->first_name);
        $this->assertEquals('Kowalski', $this->user->last_name);
        $this->assertEquals('+48501234567', $this->user->phone_e164);
        $this->assertNull($this->user->street_name);
        $this->assertNull($this->user->street_number);
        $this->assertNull($this->user->city);
        $this->assertNull($this->user->postal_code);
        $this->assertNull($this->user->access_notes);
    }
}
