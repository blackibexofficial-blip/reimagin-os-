<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Lead capture, scoring, follow-up and conversion to a customer.
 *
 * A lead becomes exactly one customer: LinkedIn-style duplicate customer rows
 * would break quotation and order history, so conversion is idempotent.
 */
class LeadService
{
    public function create(array $data, ?User $user = null): Lead
    {
        return DB::transaction(function () use ($data, $user) {
            $lead = new Lead($data);

            $lead->status ??= 'new';
            $lead->score = $lead->calculateScore();
            $lead->save();

            $this->logActivity($lead, 'note', 'Lead captured', $lead->message, $user);

            return $lead;
        });
    }

    public function assign(Lead $lead, User $user, ?User $by = null): Lead
    {
        $lead->forceFill(['assigned_to' => $user->id])->save();

        $this->logActivity($lead, 'note', 'Assigned to '.$user->name, null, $by);

        return $lead;
    }

    public function changeStatus(Lead $lead, string $status, ?User $user = null): Lead
    {
        $previous = $lead->status;

        $lead->forceFill([
            'status' => $status,
            'lost_reason' => $status === 'lost' ? ($lead->lost_reason ?? 'Marked lost') : null,
        ])->save();

        $lead->forceFill(['score' => $lead->calculateScore()])->save();

        $this->logActivity($lead, 'note', "Stage changed from {$previous} to {$status}", null, $user);

        return $lead;
    }

    /**
     * Create (or reuse) the customer record for a lead.
     *
     * @throws \RuntimeException when the lead has no contact detail to match on
     */
    public function convertToCustomer(Lead $lead, ?User $user = null): Customer
    {
        return DB::transaction(function () use ($lead, $user) {
            if ($lead->customer_id) {
                return Customer::query()->findOrFail($lead->customer_id);
            }

            $match = Customer::query()
                ->when($lead->email, fn ($q) => $q->orWhere('email', $lead->email))
                ->when($lead->phone, fn ($q) => $q->orWhere('phone', $lead->phone))
                ->first();

            if (! $match && blank($lead->email) && blank($lead->phone)) {
                throw new \RuntimeException('Add a phone number or email before converting this lead.');
            }

            $customer = $match ?: Customer::create([
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'company' => $lead->company,
                'notes' => $lead->message,
                'owner_id' => $lead->assigned_to,
            ]);

            $lead->forceFill([
                'customer_id' => $customer->id,
                'status' => 'won',
                'converted_at' => now(),
            ])->save();

            $lead->forceFill(['score' => $lead->calculateScore()])->save();

            $this->logActivity($lead, 'note', 'Converted to customer', $customer->name, $user);
            $this->logActivity($customer, 'note', 'Created from lead', $lead->name, $user);

            return $customer;
        });
    }

    public function scheduleFollowUp(Lead $lead, \DateTimeInterface $when, ?User $user = null): Task
    {
        $task = Task::create([
            'title' => 'Follow up with '.$lead->name,
            'taskable_type' => $lead->getMorphClass(),
            'taskable_id' => $lead->id,
            'assigned_to' => $lead->assigned_to ?? $user?->id,
            'created_by' => $user?->id,
            'status' => 'open',
            'priority' => $lead->priority,
            'due_at' => $when,
        ]);

        $lead->forceFill(['next_follow_up_at' => $when])->save();

        return $task;
    }

    public function logActivity(Lead|Customer $subject, string $type, string $title, ?string $body = null, ?User $user = null, array $meta = []): void
    {
        $subject->activities()->create([
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'user_id' => $user?->id ?? auth()->id(),
            'meta' => $meta ?: null,
        ]);
    }

    /** @return array<string, array{count:int, value_minor:int}> */
    public function pipelineSummary(): array
    {
        $rows = Lead::query()
            ->selectRaw('status, count(*) as total, coalesce(sum(estimated_value_minor), 0) as value')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $summary = [];

        foreach (Lead::STATUSES as $status) {
            $row = $rows->get($status);
            $summary[$status] = [
                'count' => (int) ($row->total ?? 0),
                'value_minor' => (int) ($row->value ?? 0),
            ];
        }

        return $summary;
    }

    public function conversionRate(): float
    {
        $total = Lead::query()->count();

        if ($total === 0) {
            return 0.0;
        }

        return round(Lead::query()->where('status', 'won')->count() / $total * 100, 1);
    }
}
