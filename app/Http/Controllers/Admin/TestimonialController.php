<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(): View
    {
        return view('admin.testimonials.index', [
            'testimonials' => Testimonial::query()->orderBy('position')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.testimonials.form', ['testimonial' => new Testimonial(['is_published' => true, 'rating' => 5])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Testimonial::create($this->validated($request));

        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonial added.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('admin.testimonials.form', ['testimonial' => $testimonial]);
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($this->validated($request));

        return back()->with('status', 'Testimonial saved.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonial removed.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'author' => ['required', 'string', 'max:120'],
            'role' => ['nullable', 'string', 'max:80'],
            'company' => ['nullable', 'string', 'max:80'],
            'quote' => ['required', 'string', 'max:600'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'position' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $data['is_published'] = $request->boolean('is_published');
        $data['position'] = (int) ($data['position'] ?? 0);

        return $data;
    }
}
