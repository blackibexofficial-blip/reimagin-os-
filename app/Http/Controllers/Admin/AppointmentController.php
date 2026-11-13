<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Services\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentService $appointments) {}

    public function index(Request $request): View
    {
        $day = $request->date('date') ?? today();

        return view('admin.appointments.index', [
            'appointments' => Appointment::query()
                ->with('staff')
                ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
                ->when($request->boolean('today'), fn ($query) => $query->whereDate('starts_at', today()))
                ->when($request->boolean('upcoming'), fn ($query) => $query->where('starts_at', '>=', now()))
                ->orderBy('starts_at')
                ->paginate(20)
                ->withQueryString(),
            'staff' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'day' => $day,
            'slots' => $this->slotsFor($day),
        ]);
    }

    public function create(): View
    {
        return view('admin.appointments.form', [
            'appointment' => new Appointment(['status' => 'requested', 'type' => 'showroom']),
            'staff' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'type' => ['required', 'in:showroom,site_survey,consultation'],
            'staff_id' => ['nullable', 'integer', 'exists:users,id'],
            'starts_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:180'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'in:requested,confirmed'],
        ]);

        try {
            $appointment = $this->appointments->book($data);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['starts_at' => $e->getMessage()]);
        }

        return redirect()->route('admin.appointments.index')
            ->with('status', 'Appointment '.$appointment->reference.' booked for '.$appointment->starts_at->format('j M, g:i A').'.');
    }

    public function edit(Appointment $appointment): View
    {
        return view('admin.appointments.form', [
            'appointment' => $appointment,
            'staff' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'type' => ['required', 'in:showroom,site_survey,consultation'],
            'staff_id' => ['nullable', 'integer', 'exists:users,id'],
            'starts_at' => ['required', 'date'],
            'status' => ['required', 'in:requested,confirmed,completed,cancelled,no_show'],
            'location' => ['nullable', 'string', 'max:180'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            /* Rescheduling goes through the service so the overlap check and the
             * end-time calculation are applied in one place. */
            if (filled($data['starts_at'])) {
                $this->appointments->reschedule($appointment, $data['starts_at']);
            }
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['starts_at' => $e->getMessage()]);
        }

        $appointment->update(collect($data)->except('starts_at')->all());

        return back()->with('status', 'Appointment updated.');
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $appointment->update(['status' => 'cancelled']);

        return redirect()->route('admin.appointments.index')->with('status', 'Appointment cancelled.');
    }

    /**
     * Free 45-minute slots for the studio as a whole (staff_id 0 = unassigned
     * bookings), used to render the day column on the index screen.
     *
     * @return array<int, \Carbon\CarbonInterface>
     */
    private function slotsFor(\Carbon\CarbonInterface $day): array
    {
        return $this->appointments->availableSlots(0, \Carbon\Carbon::parse($day));
    }
}
