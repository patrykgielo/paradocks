<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\ChangeEmailRequest;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\RequestDeletionRequest;
use App\Http\Requests\Profile\UpdateNotificationsRequest;
use App\Http\Requests\Profile\UpdatePersonalInfoRequest;
use App\Models\CarBrand;
use App\Models\VehicleType;
use App\Services\ProfileService;
use App\Support\Settings\SettingsManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService,
        protected SettingsManager $settings
    ) {}

    /**
     * Display profile index page with grouped list navigation.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $user->load(['vehicles.vehicleType', 'vehicles.carBrand', 'vehicles.carModel', 'addresses']);

        // Get primary vehicle and address for display
        $vehicle = $user->vehicles()->first();
        $address = $user->addresses()->first();

        return view('profile.index', [
            'user' => $user,
            'vehicle' => $vehicle,
            'address' => $address,
        ]);
    }

    /**
     * Display personal info page.
     */
    public function personal(Request $request): View
    {
        return view('profile.pages.personal', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display vehicle page.
     */
    public function vehicle(Request $request): View
    {
        $user = $request->user();
        $user->load(['vehicles.vehicleType', 'vehicles.carBrand', 'vehicles.carModel']);

        return view('profile.pages.vehicle', [
            'user' => $user,
            'vehicleTypes' => VehicleType::active()->ordered()->get(),
            'carBrands' => CarBrand::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    /**
     * Display address page.
     */
    public function address(Request $request): View
    {
        $user = $request->user();
        $user->load('addresses');

        return view('profile.pages.address', [
            'user' => $user,
            'googleMapsApiKey' => config('services.google_maps.api_key'),
            'googleMapsMapId' => $this->settings->get('map.map_id') ?? config('services.google_maps.map_id'),
        ]);
    }

    /**
     * Display notifications page.
     */
    public function notifications(Request $request): View
    {
        return view('profile.pages.notifications', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display security page.
     */
    public function security(Request $request): View
    {
        return view('profile.pages.security', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update personal information (name, phone).
     */
    public function updatePersonalInfo(UpdatePersonalInfoRequest $request): RedirectResponse
    {
        $this->profileService->updatePersonalInfo(
            $request->user(),
            $request->validated()
        );

        return redirect()->route('profile.personal')
            ->with('success', __('Dane osobowe zostały zaktualizowane.'));
    }

    /**
     * Request email change (start verification flow).
     */
    public function requestEmailChange(ChangeEmailRequest $request): RedirectResponse
    {
        $this->profileService->requestEmailChange(
            $request->user(),
            $request->validated('email')
        );

        return redirect()->route('profile.security')
            ->with('success', __('Link weryfikacyjny został wysłany na nowy adres email.'));
    }

    /**
     * Confirm email change via token link.
     */
    public function confirmEmailChange(Request $request, string $token): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', __('Musisz być zalogowany, aby zmienić email.'));
        }

        $success = $this->profileService->confirmEmailChange($user, $token);

        if ($success) {
            return redirect()->route('profile.security')
                ->with('success', __('Adres email został zmieniony.'));
        }

        return redirect()->route('profile.security')
            ->with('error', __('Link weryfikacyjny jest nieprawidłowy lub wygasł.'));
    }

    /**
     * Change password.
     */
    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $this->profileService->changePassword(
            $request->user(),
            $request->validated('current_password'),
            $request->validated('password')
        );

        return redirect()->route('profile.security')
            ->with('success', __('Hasło zostało zmienione.'));
    }

    /**
     * Update notification preferences.
     */
    public function updateNotifications(UpdateNotificationsRequest $request): RedirectResponse
    {
        $this->profileService->updateCommunicationPreferences(
            $request->user(),
            $request->validated(),
            $request->ip()
        );

        return redirect()->route('profile.notifications')
            ->with('success', __('Preferencje powiadomień zostały zaktualizowane.'));
    }

    /**
     * Request account deletion.
     */
    public function requestDeletion(RequestDeletionRequest $request): RedirectResponse
    {
        $this->profileService->requestAccountDeletion($request->user());

        return redirect()->route('profile.security')
            ->with('success', __('Link potwierdzający usunięcie konta został wysłany na Twój email.'));
    }

    /**
     * Confirm account deletion via token link.
     */
    public function confirmDeletion(Request $request, string $token): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', __('Musisz być zalogowany, aby usunąć konto.'));
        }

        $success = $this->profileService->confirmAccountDeletion($user, $token);

        if ($success) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')
                ->with('success', __('Twoje konto zostało usunięte.'));
        }

        return redirect()->route('profile.security')
            ->with('error', __('Link potwierdzający jest nieprawidłowy.'));
    }

    /**
     * Cancel account deletion request.
     */
    public function cancelDeletion(Request $request): RedirectResponse
    {
        $request->user()->cancelAccountDeletion();

        return redirect()->route('profile.security')
            ->with('success', __('Żądanie usunięcia konta zostało anulowane.'));
    }

    /**
     * Display invoice profile page.
     */
    public function invoice(Request $request): View
    {
        $user = $request->user();
        $invoiceProfile = $user->invoiceProfile;

        return view('profile.pages.invoice', [
            'user' => $user,
            'invoiceProfile' => $invoiceProfile,
        ]);
    }

    /**
     * Store new invoice profile.
     */
    public function storeInvoice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:individual,company,foreign_eu,foreign_non_eu',
            'company_name' => 'required_if:type,company,foreign_eu,foreign_non_eu|nullable|string|max:255',
            'nip' => ['nullable', 'required_if:type,company', new \App\Rules\ValidNIP],
            'vat_id' => 'nullable|required_if:type,foreign_eu|string|max:20',
            'regon' => 'nullable|string|max:14|regex:/^[0-9]+$/',
            'street' => 'required|string|max:255',
            'street_number' => 'nullable|string|max:20',
            'postal_code' => 'required|string|size:6|regex:/^\d{2}-\d{3}$/',
            'city' => 'required|string|max:100',
            'country' => 'required|string|size:2',
        ]);

        $user = $request->user();

        $user->invoiceProfile()->create([
            'type' => $validated['type'],
            'company_name' => $validated['company_name'] ?? null,
            'nip' => $validated['nip'] ?? null,
            'vat_id' => $validated['vat_id'] ?? null,
            'regon' => $validated['regon'] ?? null,
            'street' => $validated['street'],
            'street_number' => $validated['street_number'] ?? null,
            'postal_code' => $validated['postal_code'],
            'city' => $validated['city'],
            'country' => $validated['country'],
            'validated_at' => now(),
            'consent_given_at' => now(),
            'consent_ip' => $request->ip(),
            'consent_user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('profile.invoice')
            ->with('success', __('Dane do faktury zostały zapisane.'));
    }

    /**
     * Update existing invoice profile.
     */
    public function updateInvoice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:individual,company,foreign_eu,foreign_non_eu',
            'company_name' => 'required_if:type,company,foreign_eu,foreign_non_eu|nullable|string|max:255',
            'nip' => ['nullable', 'required_if:type,company', new \App\Rules\ValidNIP],
            'vat_id' => 'nullable|required_if:type,foreign_eu|string|max:20',
            'regon' => 'nullable|string|max:14|regex:/^[0-9]+$/',
            'street' => 'required|string|max:255',
            'street_number' => 'nullable|string|max:20',
            'postal_code' => 'required|string|size:6|regex:/^\d{2}-\d{3}$/',
            'city' => 'required|string|max:100',
            'country' => 'required|string|size:2',
        ]);

        $user = $request->user();
        $invoiceProfile = $user->invoiceProfile;

        if (! $invoiceProfile) {
            return redirect()->route('profile.invoice')
                ->with('error', __('Nie znaleziono profilu faktury.'));
        }

        $invoiceProfile->update([
            'type' => $validated['type'],
            'company_name' => $validated['company_name'] ?? null,
            'nip' => $validated['nip'] ?? null,
            'vat_id' => $validated['vat_id'] ?? null,
            'regon' => $validated['regon'] ?? null,
            'street' => $validated['street'],
            'street_number' => $validated['street_number'] ?? null,
            'postal_code' => $validated['postal_code'],
            'city' => $validated['city'],
            'country' => $validated['country'],
            'validated_at' => now(),
        ]);

        return redirect()->route('profile.invoice')
            ->with('success', __('Dane do faktury zostały zaktualizowane.'));
    }

    /**
     * Delete invoice profile.
     */
    public function destroyInvoice(Request $request): RedirectResponse
    {
        $user = $request->user();
        $invoiceProfile = $user->invoiceProfile;

        if ($invoiceProfile) {
            $invoiceProfile->delete();

            return redirect()->route('profile.invoice')
                ->with('success', __('Profil faktury został usunięty.'));
        }

        return redirect()->route('profile.invoice')
            ->with('error', __('Nie znaleziono profilu faktury.'));
    }
}
