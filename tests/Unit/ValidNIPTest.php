<?php

namespace Tests\Unit;

use App\Rules\ValidNIP;
use PHPUnit\Framework\TestCase;

class ValidNIPTest extends TestCase
{
    /**
     * Test that valid NIP passes validation.
     */
    public function test_validates_correct_nip(): void
    {
        $rule = new ValidNIP;
        $passes = true;

        $rule->validate('nip', '7751001452', function () use (&$passes) {
            $passes = false;
        });

        $this->assertTrue($passes, 'Valid NIP 7751001452 should pass validation');
    }

    /**
     * Test that NIP with dashes is accepted and validated correctly.
     */
    public function test_accepts_nip_with_dashes(): void
    {
        $rule = new ValidNIP;
        $passes = true;

        $rule->validate('nip', '775-100-14-52', function () use (&$passes) {
            $passes = false;
        });

        $this->assertTrue($passes, 'Valid NIP with dashes 775-100-14-52 should pass validation');
    }

    /**
     * Test that NIP with spaces is accepted and validated correctly.
     */
    public function test_accepts_nip_with_spaces(): void
    {
        $rule = new ValidNIP;
        $passes = true;

        $rule->validate('nip', '775 100 14 52', function () use (&$passes) {
            $passes = false;
        });

        $this->assertTrue($passes, 'Valid NIP with spaces should pass validation');
    }

    /**
     * Test that incorrect checksum fails validation.
     */
    public function test_rejects_incorrect_checksum(): void
    {
        $rule = new ValidNIP;
        $passes = true;

        $rule->validate('nip', '7751001455', function () use (&$passes) {
            $passes = false;
        });

        $this->assertFalse($passes, 'NIP with incorrect checksum should fail validation');
    }

    /**
     * Test that checksum of 10 fails validation (invalid by algorithm).
     */
    public function test_rejects_checksum_of_ten(): void
    {
        $rule = new ValidNIP;
        $passes = true;

        // This NIP would have checksum of 10, which is invalid
        $rule->validate('nip', '1234567890', function () use (&$passes) {
            $passes = false;
        });

        $this->assertFalse($passes, 'NIP with checksum of 10 should fail validation');
    }

    /**
     * Test that NIP with wrong length fails validation.
     */
    public function test_rejects_nip_with_wrong_length(): void
    {
        $rule = new ValidNIP;
        $passes = true;

        $rule->validate('nip', '123456789', function () use (&$passes) {
            $passes = false;
        });

        $this->assertFalse($passes, 'NIP with 9 digits should fail validation');
    }

    /**
     * Test that NIP with too many digits fails validation.
     */
    public function test_rejects_nip_with_too_many_digits(): void
    {
        $rule = new ValidNIP;
        $passes = true;

        $rule->validate('nip', '12345678901', function () use (&$passes) {
            $passes = false;
        });

        $this->assertFalse($passes, 'NIP with 11 digits should fail validation');
    }

    /**
     * Test that NIP with non-digit characters fails validation.
     */
    public function test_rejects_nip_with_letters(): void
    {
        $rule = new ValidNIP;
        $passes = true;

        $rule->validate('nip', '775100145A', function () use (&$passes) {
            $passes = false;
        });

        $this->assertFalse($passes, 'NIP with letters should fail validation');
    }

    /**
     * Test that empty NIP fails validation.
     */
    public function test_rejects_empty_nip(): void
    {
        $rule = new ValidNIP;
        $passes = true;

        $rule->validate('nip', '', function () use (&$passes) {
            $passes = false;
        });

        $this->assertFalse($passes, 'Empty NIP should fail validation');
    }

    /**
     * Test multiple valid NIP examples.
     *
     * @dataProvider validNIPProvider
     */
    public function test_validates_multiple_valid_nips(string $nip): void
    {
        $rule = new ValidNIP;
        $passes = true;

        $rule->validate('nip', $nip, function () use (&$passes) {
            $passes = false;
        });

        $this->assertTrue($passes, "Valid NIP {$nip} should pass validation");
    }

    /**
     * Provide valid NIP examples for testing.
     */
    public static function validNIPProvider(): array
    {
        return [
            'NIP without formatting' => ['7751001452'],
            'NIP with dashes' => ['775-100-14-52'],
            'Another valid NIP' => ['1234563218'],
            'NIP with different checksum' => ['5252828563'],
            'NIP starting with 9' => ['9876543210'],
            'NIP with repeated digits' => ['1112223332'],
        ];
    }

    /**
     * Test multiple invalid NIP examples.
     *
     * @dataProvider invalidNIPProvider
     */
    public function test_rejects_multiple_invalid_nips(string $nip): void
    {
        $rule = new ValidNIP;
        $passes = true;

        $rule->validate('nip', $nip, function () use (&$passes) {
            $passes = false;
        });

        $this->assertFalse($passes, "Invalid NIP {$nip} should fail validation");
    }

    /**
     * Provide invalid NIP examples for testing.
     */
    public static function invalidNIPProvider(): array
    {
        return [
            'Incorrect checksum (should be 2)' => ['7751001454'],
            'Incorrect checksum (should be 3)' => ['5252828562'],
            'Too short' => ['123456789'],
            'Too long' => ['12345678901'],
            'With letters' => ['775100145A'],
            'Checksum of 10 (invalid)' => ['1234567890'],
        ];
    }

    /**
     * Test the actual checksum calculation for a known valid NIP.
     */
    public function test_checksum_calculation(): void
    {
        // NIP: 7751001452 (corrected valid NIP)
        // Calculation: (7×6 + 7×5 + 5×7 + 1×2 + 0×3 + 0×4 + 1×5 + 4×6 + 5×7) mod 11
        // = (42 + 35 + 35 + 2 + 0 + 0 + 5 + 24 + 35) mod 11
        // = 178 mod 11
        // = 2 ✓ (matches digit[9])

        $rule = new ValidNIP;
        $passes = true;

        // This is a correctly calculated valid NIP
        $rule->validate('nip', '7751001452', function () use (&$passes) {
            $passes = false;
        });

        $this->assertTrue($passes, 'Correctly calculated NIP should pass validation');
    }
}
