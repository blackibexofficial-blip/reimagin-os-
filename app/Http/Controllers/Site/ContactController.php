<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\AppointmentService;
use App\Services\LeadService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __construct(
        private LeadService $leads,
        private AppointmentService $appointments,
    ) {}

    public function index(Request $request): View
    {
        $page = Page::query()->published()->where('slug', 'contact')->first();

        return view('site.contact', [
            'page' => $page,
            'piece' => $request->string('piece')->toString() ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:40', 'required_without:email'],
            'message' => ['nullable', 'string', 'max:2000'],
            'form' => ['nullable', 'string', 'in:quote,contact'],
            'source' => ['nullable', 'string', 'max:40'],
        ]);

        $lead = $this->leads->create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'] ?? null,
            'source' => $data['source'] ?? ($data['form'] ?? 'contact_form'),
            'status' => 'new',
            'priority' => 'normal',
        ]);

        return back()->with('status', 'Thank you, '.$lead->name.'. A designer will be in touch within one working day.');
    }

    public function book(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['required', 'string', 'max:40'],
            'type' => ['required', 'string', 'in:showroom,site_survey,consultation'],
            'staff_id' => ['nullable', 'integer', 'exists:users,id'],
            'starts_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $appointment = $this->appointments->book([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'],
                'type' => $data['type'],
                'staff_id' => $data['staff_id'] ?? null,
                'starts_at' => Carbon::parse($data['starts_at']),
                'location' => $data['type'] === 'site_survey' ? 'Site visit' : 'Gulberg showroom',
                'notes' => $data['notes'] ?? null,
            ]);
        } catch (\RuntimeException $e) {
            /* The slot was taken between rendering the form and submitting it;
             * a friendly message beats a 500 on the public site. */
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Your visit is requested for '.$appointment->starts_at->format('l j F, g:i A').'. We will confirm by phone.');
    }
}
