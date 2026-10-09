@php
    $opening = $opening ?? null;
    $questions = collect($opening?->questions ?? [])->values();
    $input = 'h-11 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm text-navy placeholder:text-slate/50 outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20 transition';
    $label = 'mb-1.5 block text-sm font-semibold text-navy';
@endphp

<div id="apply" class="scroll-mt-6">
    @if (session('success'))
        <div class="mb-5 flex items-start gap-3 rounded-xl border border-success/30 bg-success/10 px-5 py-4 text-sm text-navy">
            <svg class="h-5 w-5 shrink-0 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-alert/30 bg-alert/10 px-5 py-4 text-sm text-navy">
            Please fix the highlighted fields and try again.
        </div>
    @endif

    <form action="{{ $action }}" method="POST" enctype="multipart/form-data"
          class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5 sm:p-8">
        @csrf

        {{-- Honeypot --}}
        <div class="hidden" aria-hidden="true">
            <input type="text" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="{{ $label }}" for="name">Full Name *</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Enter your full name" class="{{ $input }}" required>
                @error('name') <p class="mt-1 text-xs text-alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="{{ $label }}" for="email">Email *</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Enter your email" class="{{ $input }}" required>
                @error('email') <p class="mt-1 text-xs text-alert">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="{{ $label }}" for="phone">Phone *</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="Enter phone number" class="{{ $input }}" required>
                @error('phone') <p class="mt-1 text-xs text-alert">{{ $message }}</p> @enderror
            </div>

            @if ($opening)
                <div>
                    <label class="{{ $label }}">Applying for</label>
                    <div class="flex h-11 items-center rounded-lg border border-gray-200 bg-ice px-4 text-sm font-medium text-navy">
                        {{ $opening->title }}
                    </div>
                </div>
            @else
                <div>
                    <label class="{{ $label }}" for="position">Position *</label>
                    <input id="position" name="position" type="text" value="{{ old('position') }}" placeholder="Role you are interested in" class="{{ $input }}" required>
                    @error('position') <p class="mt-1 text-xs text-alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $label }}" for="preferred_location">Preferred Location *</label>
                    <input id="preferred_location" name="preferred_location" type="text" value="{{ old('preferred_location') }}" placeholder="Enter preferred location" class="{{ $input }}" required>
                    @error('preferred_location') <p class="mt-1 text-xs text-alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $label }}" for="department">Department *</label>
                    <input id="department" name="department" type="text" value="{{ old('department') }}" placeholder="e.g. Sales, Service, Marketing" class="{{ $input }}" required>
                    @error('department') <p class="mt-1 text-xs text-alert">{{ $message }}</p> @enderror
                </div>
            @endif

            <div class="md:col-span-2">
                <label class="{{ $label }}" for="resume">Resume * <span class="font-normal text-slate">(PDF, DOC or DOCX, max 5 MB)</span></label>
                <input id="resume" name="resume" type="file" accept=".pdf,.doc,.docx" required
                       class="block w-full rounded-lg border border-dashed border-cyan/50 bg-ice px-4 py-3 text-sm text-slate
                              file:mr-4 file:rounded-full file:border-0 file:bg-navy file:px-4 file:py-1.5 file:text-sm file:font-semibold file:text-white
                              hover:file:bg-link cursor-pointer">
                @error('resume') <p class="mt-1 text-xs text-alert">{{ $message }}</p> @enderror
            </div>

            {{-- Role specific questions --}}
            @foreach ($questions as $i => $q)
                @php
                    $type = $q['type'] ?? 'text';
                    $required = (bool) ($q['required'] ?? false);
                    $options = collect(explode(',', (string) ($q['options'] ?? '')))->map(fn ($o) => trim($o))->filter()->values();
                @endphp
                <div class="{{ in_array($type, ['textarea']) ? 'md:col-span-2' : '' }}">
                    <label class="{{ $label }}" for="q{{ $i }}">{{ $q['label'] }}{{ $required ? ' *' : '' }}</label>

                    @if ($type === 'textarea')
                        <textarea id="q{{ $i }}" name="answers[{{ $i }}]" rows="3" @required($required)
                            class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-navy outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20 transition">{{ old("answers.$i") }}</textarea>
                    @elseif ($type === 'select' || $type === 'yes_no')
                        @php $choices = $type === 'yes_no' ? collect(['Yes', 'No']) : $options; @endphp
                        <select id="q{{ $i }}" name="answers[{{ $i }}]" @required($required) class="{{ $input }}">
                            <option value="">Select</option>
                            @foreach ($choices as $choice)
                                <option value="{{ $choice }}" @selected(old("answers.$i") === $choice)>{{ $choice }}</option>
                            @endforeach
                        </select>
                    @else
                        <input id="q{{ $i }}" name="answers[{{ $i }}]" type="text" value="{{ old("answers.$i") }}" @required($required) class="{{ $input }}">
                    @endif

                    @error("answers.$i") <p class="mt-1 text-xs text-alert">{{ $message }}</p> @enderror
                </div>
            @endforeach

            <div class="md:col-span-2">
                <label class="{{ $label }}" for="message">Anything else you'd like us to know? <span class="font-normal text-slate">(optional)</span></label>
                <textarea id="message" name="message" rows="3"
                    class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-navy outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20 transition">{{ old('message') }}</textarea>
                @error('message') <p class="mt-1 text-xs text-alert">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-full bg-navy px-8 py-3 text-sm font-semibold text-white shadow-md
                       hover:bg-link hover:scale-105 transition-all duration-300 btn-primary">
                Submit Application
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                </svg>
            </button>
        </div>
    </form>
</div>
