@props(['status' => null])
@php
$map = [
    'draft' => ['neutral', 'Draft'], 'published' => ['success', 'Published'], 'scheduled' => ['info', 'Scheduled'],
    'archived' => ['neutral', 'Archived'],
    'new' => ['info', 'New'], 'contacted' => ['info', 'Contacted'], 'qualified' => ['brand', 'Qualified'],
    'proposal' => ['warning', 'Proposal'], 'won' => ['success', 'Won'], 'lost' => ['danger', 'Lost'],
    'sent' => ['warning', 'Sent'], 'approved' => ['success', 'Approved'], 'rejected' => ['danger', 'Rejected'],
    'converted' => ['success', 'Converted'], 'expired' => ['neutral', 'Expired'],
    'pending' => ['warning', 'Pending'], 'confirmed' => ['info', 'Confirmed'], 'in_production' => ['warning', 'In production'],
    'ready' => ['info', 'Ready'], 'delivered' => ['success', 'Delivered'], 'cancelled' => ['danger', 'Cancelled'],
    'requested' => ['info', 'Requested'], 'completed' => ['success', 'Completed'], 'no_show' => ['danger', 'No show'],
    'open' => ['info', 'Open'], 'handed_over' => ['warning', 'With staff'], 'resolved' => ['success', 'Resolved'],
    'planning' => ['info', 'Planning'], 'design' => ['info', 'Design'], 'production' => ['warning', 'Production'],
    'qc' => ['warning', 'QC'], 'delivery' => ['brand', 'Delivery'], 'installed' => ['success', 'Installed'],
    'closed' => ['neutral', 'Closed'], 'on_hold' => ['danger', 'On hold'], 'in_progress' => ['info', 'In progress'],
    'blocked' => ['danger', 'Blocked'], 'done' => ['success', 'Done'],
    'paid' => ['success', 'Paid'], 'partial' => ['warning', 'Part paid'], 'unpaid' => ['danger', 'Unpaid'],
    'refunded' => ['neutral', 'Refunded'],
];
[$tone, $label] = $map[$status] ?? ['neutral', $status ? \Illuminate\Support\Str::headline($status) : '—'];
@endphp
<x-ui.badge :tone="$tone">{{ $label }}</x-ui.badge>
