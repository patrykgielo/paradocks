<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserInvoiceProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserInvoiceProfile>
 */
class UserInvoiceProfileFactory extends Factory
{
    protected $model = UserInvoiceProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'individual',
            'company_name' => null,
            'nip' => null,
            'vat_id' => null,
            'regon' => null,
            'street' => fake()->streetName(),
            'street_number' => fake()->buildingNumber(),
            'postal_code' => fake()->regexify('[0-9]{2}-[0-9]{3}'), // Format: XX-XXX
            'city' => fake()->city(),
            'country' => 'PL',
            'validated_at' => now(),
            'consent_given_at' => now(),
            'consent_ip' => fake()->ipv4(),
            'consent_user_agent' => fake()->userAgent(),
        ];
    }

    /**
     * State for company type invoice profile.
     */
    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'company',
            'company_name' => fake()->company(),
            'nip' => '7751001452', // Valid NIP (checksum verified)
            'regon' => fake()->regexify('[0-9]{9}'),
        ]);
    }

    /**
     * State for foreign EU type invoice profile.
     */
    public function foreignEu(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'foreign_eu',
            'company_name' => fake()->company(),
            'vat_id' => 'DE'.fake()->regexify('[0-9]{9}'),
            'country' => 'DE',
        ]);
    }

    /**
     * State for foreign non-EU type invoice profile.
     */
    public function foreignNonEu(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'foreign_non_eu',
            'company_name' => fake()->company(),
            'country' => 'US',
        ]);
    }
}
