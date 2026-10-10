@php
    $opening = $opening ?? null;
    $questions = collect($opening?->questions ?? [])->values();
    $input = 'h-11 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm text-navy placeholder:text-slate outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20 transition';
    $label = 'mb-1.5 block text-sm font-semibold text-navy';
@endphp

<div id="apply" class="scroll-mt-6">
    @if (session('success'))
        <div class="mb-5"><x-form.alert>{{ session('success') }}</x-form.alert></div>
    @endif

    @if ($errors->any())
        <div class="mb-5"><x-form.alert type="error">Please fix the highlighted fields and try again.</x-form.alert></div>
    @endif

    <form action="{{ $action }}" method="POST" enctype="multipart/form-data" x-data="formGuard(@js(collect($errors->messages())->map(fn ($m) => $m[0])->all()))" @submit="submit($event)" novalidate
          class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5 sm:p-8">
        @csrf

        {{-- Honeypot --}}
        <div class="hidden" aria-hidden="true">
            <input type="text" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="{{ $label }}" for="name">Full Name *</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Enter your full name" class="{{ $input }}" required data-rule="required name" data-clean="name" data-label="Name" maxlength="80" autocomplete="name" @input="clean($el)" @blur="check($el)">
                <x-form.error name="name" />
            </div>
            <div>
                <label class="{{ $label }}" for="email">Email *</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Enter your email" class="{{ $input }}" required data-rule="required email" data-label="Email" maxlength="255" autocomplete="email" @input="clean($el)" @blur="check($el)">
                <x-form.error name="email" />
            </div>
            <div>
                <label class="{{ $label }}" for="phone">Phone *</label>
                <x-form.phone :required="true" :input="$input" />
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
                    <input id="position" name="position" type="text" value="{{ old('position') }}" placeholder="Role you are interested in" class="{{ $input }}" required data-rule="required" data-label="Position" @input="clean($el)" @blur="check($el)">
                    <x-form.error name="position" />
                </div>
                <div>
                    <label class="{{ $label }}" for="preferred_location">Preferred Location *</label>
                    <input id="preferred_location" name="preferred_location" type="text" value="{{ old('preferred_location') }}" placeholder="Enter preferred location" class="{{ $input }}" required data-rule="required" data-label="Preferred location" @input="clean($el)" @blur="check($el)">
                    <x-form.error name="preferred_location" />
                </div>
                <div>
                    <label class="{{ $label }}" for="department">Department *</label>
                    <input id="department" name="department" type="text" value="{{ old('department') }}" placeholder="e.g. Sales, Service, Marketing" class="{{ $input }}" required data-rule="required" data-label="Department" @input="clean($el)" @blur="check($el)">
                    <x-form.error name="department" />
                </div>
            @endif

            <div class="md:col-span-2">
                <label class="{{ $label }}" for="resume">Resume * <span class="font-normal text-slate">(PDF or DOCX, max 5 MB)</span></label>
                <input id="resume" name="resume" type="file" accept=".pdf,.docx" required data-rule="required" data-label="Resume" @change="clean($el); check($el)"
                       class="block w-full rounded-lg border border-dashed border-cyan/50 bg-ice px-4 py-3 text-sm text-slate
                              file:mr-4 file:rounded-full file:border-0 file:bg-navy file:px-4 file:py-1.5 file:text-sm file:font-semibold file:text-white
                              hover:file:bg-link cursor-pointer">
                <x-form.error name="resume" />
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
                        <textarea id="q{{ $i }}" name="answers[{{ $i }}]" rows="3" @required($required) @if ($required) data-rule="required" data-label="This answer" @input="clean($el)" @blur="check($el)" @endif
                            class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-navy outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20 transition">{{ old("answers.$i") }}</textarea>
                    @elseif ($type === 'select' || $type === 'yes_no')
                        @php $choices = $type === 'yes_no' ? collect(['Yes', 'No']) : $options; @endphp
                        <select id="q{{ $i }}" name="answers[{{ $i }}]" @required($required) @if ($required) data-rule="required" data-label="This answer" @change="clean($el); check($el)" @endif class="{{ $input }}">
                            <option value="">Select</option>
                            @foreach ($choices as $choice)
                                <option value="{{ $choice }}" @selected(old("answers.$i") === $choice)>{{ $choice }}</option>
                            @endforeach
                        </select>
                    @else
                        <input id="q{{ $i }}" name="answers[{{ $i }}]" type="text" value="{{ old("answers.$i") }}" @required($required) @if ($required) data-rule="required" data-label="This answer" @input="clean($el)" @blur="check($el)" @endif class="{{ $input }}">
                    @endif

                    <x-form.error name="answers.{{ $i }}" />
                </div>
            @endforeach

            <div class="md:col-span-2">
                <label class="{{ $label }}" for="message">Anything else you'd like us to know? <span class="font-normal text-slate">(optional)</span></label>
                <textarea id="message" name="message" rows="3"
                    class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-navy outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20 transition">{{ old('message') }}</textarea>
                <x-form.error name="message" />
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <x-form.submit label="Submit Application" class="px-8" />
        </div>
    </form>
</div>
