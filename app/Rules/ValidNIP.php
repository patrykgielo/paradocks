<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidNIP implements ValidationRule
{
    /**
     * Validate NIP using weighted checksum algorithm
     *
     * Algorithm:
     * - Weights: [6, 5, 7, 2, 3, 4, 5, 6, 7]
     * - Checksum: (digit[0]×6 + digit[1]×5 + ... + digit[8]×7) mod 11
     * - Valid if: checksum == digit[9] AND checksum != 10
     *
     * Example: 7751001452
     * (7×6 + 7×5 + 5×7 + 1×2 + 0×3 + 0×4 + 1×5 + 4×6 + 5×7) mod 11 = 2 ✓
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Remove dashes and spaces
        $nip = preg_replace('/[^0-9]/', '', $value);

        // Check length
        if (strlen($nip) !== 10) {
            $fail('NIP musi składać się z 10 cyfr.');

            return;
        }

        // Check if all characters are digits
        if (! ctype_digit($nip)) {
            $fail('NIP może zawierać tylko cyfry.');

            return;
        }

        // Weighted checksum calculation
        $weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $nip[$i] * $weights[$i];
        }

        $checksum = $sum % 11;

        // Validate checksum
        if ($checksum === 10) {
            $fail('Nieprawidłowy numer NIP (błąd sumy kontrolnej).');

            return;
        }

        if ($checksum !== (int) $nip[9]) {
            $fail('Nieprawidłowy numer NIP (błąd sumy kontrolnej).');
        }
    }
}
