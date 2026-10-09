<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CareerController extends Controller
{
    public function index()
    {
        $openings = JobOpening::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('public.careers', compact('openings'));
    }

    public function show(JobOpening $opening)
    {
        abort_unless($opening->is_active, 404);

        return view('public.career', compact('opening'));
    }

    public function applyToOpening(Request $request, JobOpening $opening): RedirectResponse
    {
        abort_unless($opening->is_active, 404);

        $questions = collect($opening->questions ?? [])->values();

        $rules = $this->baseRules() + [
            'answers' => ['nullable', 'array'],
        ];

        foreach ($questions as $i => $q) {
            $rule = [($q['required'] ?? false) ? 'required' : 'nullable', 'string', 'max:2000'];

            if (($q['type'] ?? 'text') === 'select') {
                $choices = collect(explode(',', (string) ($q['options'] ?? '')))
                    ->map(fn ($o) => trim($o))->filter()->values()->all();
                $rule[] = Rule::in($choices);
            } elseif (($q['type'] ?? 'text') === 'yes_no') {
                $rule[] = Rule::in(['Yes', 'No']);
            }

            $rules["answers.$i"] = $rule;
        }

        $data = $request->validate($rules);

        $answers = $questions->map(fn ($q, $i) => [
            'label' => $q['label'],
            'answer' => $data['answers'][$i] ?? null,
        ])->all();

        $this->store($request, [
            'job_opening_id' => $opening->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'position' => $opening->title,
            'preferred_location' => $opening->location,
            'department' => $opening->department,
            'message' => $data['message'] ?? null,
            'answers' => $answers,
        ]);

        return back()
            ->with('success', 'Thank you! Your application for ' . $opening->title . ' has been received. Our HR team will get in touch with you.')
            ->withFragment('apply');
    }

    public function applyGeneral(Request $request): RedirectResponse
    {
        $data = $request->validate($this->baseRules() + [
            'position' => ['required', 'string', 'max:255'],
            'preferred_location' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
        ]);

        $this->store($request, [
            'job_opening_id' => null,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'position' => $data['position'],
            'preferred_location' => $data['preferred_location'],
            'department' => $data['department'],
            'message' => $data['message'] ?? null,
        ]);

        return back()
            ->with('success', 'Thank you! Your profile has been received. We will reach out when a suitable role opens up.')
            ->withFragment('apply');
    }

    private function baseRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'message' => ['nullable', 'string', 'max:2000'],
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            // Honeypot: real users never fill this in.
            'website' => ['prohibited'],
        ];
    }

    private function store(Request $request, array $attributes): void
    {
        $path = $request->file('resume')->store('resumes', 'local');

        JobApplication::create($attributes + ['resume' => $path, 'status' => 'new']);
    }
}
