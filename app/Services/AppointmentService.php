<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Appointment booking with double-booking protection.
 *
 * Two layers: assertSlotAvailable() checks for an overlapping appointment for the
 * same staff member, and the unique index on (staff_id, starts_at) catches the
 * race between two requests arriving at the same instant.
 */
class AppointmentService
{
    public const SLOT_MINUTES = 45;

    public function book(array $data): Appointment
    {
        $start = Carbon::parse($data['starts_at']);
        $end = isset($data['ends_at'])
            ? Carbon::parse($data['ends_at'])
            : $start->copy()->addMinutes(self::SLOT_MINUTES);

        return DB::transaction(function () use ($data, $start, $end) {
            $staffId = $data['staff_id'] ?? null;

            $this->assertSlotAvailable($staffId, $start, $end);

            return Appointment::create([
                'reference' => $this->nextReference(),
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'type' => $data['type'] ?? 'showroom_visit',
                'starts_at' => $start,
                'ends_at' => $end,
                'staff_id' => $staffId,
                'status' => $data['status'] ?? 'requested',
                'location' => $data['location'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    public function reschedule(Appointment $appointment, string|CarbonInterface $startsAt): Appointment
    {
        $start = Carbon::parse($startsAt);
        $end = $start->copy()->addMinutes(self::SLOT_MINUTES);

        return DB::transaction(function () use ($appointment, $start, $end) {
            $this->assertSlotAvailable($appointment->staff_id, $start, $end, $appointment->id);

            $appointment->forceFill([
                'starts_at' => $start,
                'ends_at' => $end,
                'status' => 'confirmed',
            ])->save();

            return $appointment;
        });
    }

    /** @throws \RuntimeException when the slot overlaps an existing booking */
    public function assertSlotAvailable(?int $staffId, CarbonInterface $start, CarbonInterface $end, ?int $ignoreId = null): void
    {
        if (! $staffId) {
            return;
        }

        $clash = Appointment::query()
            ->where('staff_id', $staffId)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($clash) {
            throw new \RuntimeException('That staff member already has an appointment in this slot.');
        }
    }

    /** @return array<int, Carbon> free slot starts for a staff member on a day */
    public function availableSlots(int $staffId, CarbonInterface $day): array
    {
        $open = Carbon::parse($day)->setTime(11, 0);
        $close = Carbon::parse($day)->setTime(19, 0);

        $booked = Appointment::query()
            ->where('staff_id', $staffId)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->whereBetween('starts_at', [$open, $close])
            ->get(['starts_at', 'ends_at']);

        $slots = [];

        for ($cursor = $open->copy(); $cursor->lt($close); $cursor->addMinutes(self::SLOT_MINUTES)) {
            $slotEnd = $cursor->copy()->addMinutes(self::SLOT_MINUTES);

            $taken = $booked->contains(
                fn ($booking) => $booking->starts_at->lt($slotEnd) && $booking->ends_at->gt($cursor)
            );

            if (! $taken) {
                $slots[] = $cursor->copy();
            }
        }

        return $slots;
    }

    public static function nextReference(): string
    {
        $prefix = 'AP-'.now()->format('Ymd').'-';
        $count = Appointment::query()->where('reference', 'like', $prefix.'%')->count() + 1;

        do {
            $reference = $prefix.str_pad((string) $count++, 3, '0', STR_PAD_LEFT);
        } while (Appointment::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
