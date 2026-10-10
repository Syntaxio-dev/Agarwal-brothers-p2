@php
    // [column, required?, what to write, example]
    $columns = [
        ['name', true, 'Product name', 'Entris II Essential'],
        ['brand', true, 'Brand name exactly as in Catalogue, Brands', 'Sartorius'],
        ['category', true, 'Product line (category) under that brand', 'Analytical Balances'],
        ['model_group', false, 'Heading to group models on the category page', 'Standard Models'],
        ['short_description', false, 'One or two lines for the product card', 'Compact precision balance'],
        ['overview', false, 'Longer introduction', 'A reliable everyday balance...'],
        ['specs', false, 'Name: Value pairs separated by |', 'Pan size: 90 mm | Display: LCD'],
        ['spec: Capacity', false, 'Optional: one column per specification (the name goes after "spec:")', '220 g'],
        ['features', false, 'Title: Description pairs separated by |', 'Fast: Stable in 2 seconds | Compact: Small footprint'],
        ['advantages', false, 'Same as features', 'Easy to clean: Smooth housing'],
        ['is_active', false, 'yes or no. Empty means no (draft)', 'no'],
        ['slug', false, 'Web address part. Leave out to make it from the name', 'entris-ii-essential'],
    ];
    $result = $this->result;
    $failed = $result && ($result['fatal'] || $result['errors']);
@endphp

<x-filament-panels::page>
    {{-- 1. Upload --}}
    <form wire:submit="import" class="space-y-4">
        {{ $this->form }}

        <div class="flex flex-wrap items-center gap-3">
            <x-filament::button type="submit" icon="heroicon-o-arrow-up-tray" wire:loading.attr="disabled" wire:target="import">
                <span wire:loading.remove wire:target="import">Import products</span>
                <span wire:loading wire:target="import">Checking and importing...</span>
            </x-filament::button>
            <x-filament::button type="button" color="gray" icon="heroicon-o-arrow-down-tray" wire:click="downloadSample">
                Download sample file
            </x-filament::button>
        </div>
    </form>

    {{-- 2. Result --}}
    @if ($result)
        @if ($failed)
            <div class="rounded-xl bg-danger-50 p-5 ring-1 ring-danger-200 dark:bg-danger-500/10 dark:ring-danger-500/30" role="alert">
                <p class="font-mono text-[11px] font-semibold uppercase tracking-widest text-danger-700 dark:text-danger-400">Nothing was imported</p>
                @if ($result['fatal'])
                    <p class="mt-2 text-sm text-gray-800 dark:text-gray-200">{{ $result['fatal'] }}</p>
                @else
                    <p class="mt-2 text-sm text-gray-800 dark:text-gray-200">
                        {{ count($result['errors']) }} {{ \Illuminate\Support\Str::plural('problem', count($result['errors'])) }} found. To avoid a half-finished import, nothing was saved. Fix these rows in your file and upload it again.
                    </p>
                    <div class="mt-3 overflow-x-auto rounded-lg bg-white ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-white/10">
                                    <th class="w-20 px-3 py-2 font-mono text-[11px] font-semibold uppercase tracking-wider text-gray-500">Row</th>
                                    <th class="px-3 py-2 font-mono text-[11px] font-semibold uppercase tracking-wider text-gray-500">Product</th>
                                    <th class="px-3 py-2 font-mono text-[11px] font-semibold uppercase tracking-wider text-gray-500">Problem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (array_slice($result['errors'], 0, 50) as $error)
                                    <tr class="border-b border-gray-100 last:border-0 dark:border-white/5">
                                        <td class="px-3 py-2 font-mono text-gray-500">{{ $error['line'] }}</td>
                                        <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ $error['name'] }}</td>
                                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $error['message'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if (count($result['errors']) > 50)
                        <p class="mt-2 text-xs text-gray-500">Showing the first 50 problems.</p>
                    @endif
                @endif
            </div>
        @else
            <div class="rounded-xl bg-success-50 p-5 ring-1 ring-success-200 dark:bg-success-500/10 dark:ring-success-500/30" role="status">
                <p class="font-mono text-[11px] font-semibold uppercase tracking-widest text-success-700 dark:text-success-400">Import finished</p>
                <p class="mt-2 text-sm text-gray-800 dark:text-gray-200">
                    <strong>{{ $result['created'] }}</strong> created,
                    <strong>{{ $result['updated'] }}</strong> updated,
                    <strong>{{ $result['skipped'] }}</strong> skipped (already existed).
                </p>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    New products start as drafts without photos. Add pictures, check the details and switch them to Active.
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <x-filament::button tag="a" size="sm" :href="\App\Filament\Resources\Products\ProductResource::getUrl('index', ['filters' => ['is_active' => ['value' => '0']]])">Open draft products</x-filament::button>
                    <x-filament::button tag="a" size="sm" color="gray" :href="\App\Filament\Resources\Products\ProductResource::getUrl('index', ['filters' => ['no_image' => ['isActive' => true]]])">Products without a photo</x-filament::button>
                </div>
            </div>
        @endif
    @endif

    {{-- 3. Format --}}
    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <p class="font-mono text-[11px] font-semibold uppercase tracking-widest text-primary-600">File format</p>
        <h2 class="mt-1 text-lg font-bold text-gray-900 dark:text-white">How to prepare your CSV or Excel file</h2>

        <ol class="mt-3 list-decimal space-y-1.5 pl-5 text-sm text-gray-700 dark:text-gray-300">
            <li>The first row holds the column names below. Every next row is one product. Column order does not matter.</li>
            <li><strong>name</strong>, <strong>brand</strong> and <strong>category</strong> are required. The brand must already exist. The category must exist under that brand (or switch on "Create missing categories").</li>
            <li>Write specifications as <span class="font-mono">Name: Value</span> and separate them with <span class="font-mono">|</span>. You can also give each specification its own column named <span class="font-mono">spec: Capacity</span>. Different products may have different numbers of specifications; leave a cell empty when it does not apply.</li>
            <li>Pictures, documents and videos cannot be uploaded this way. Add them afterwards on each product. New products start as <strong>drafts</strong> so nothing half-finished goes live.</li>
            <li>Save from Excel as <strong>CSV UTF-8</strong> or <strong>.xlsx</strong>. Up to 1000 products per file. If anything in the file is wrong, nothing is imported and the problem rows are listed.</li>
        </ol>

        <div class="mt-5 overflow-x-auto rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10">
            <table class="w-full min-w-[620px] text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/5">
                        @foreach (['Column', 'Required', 'What to write', 'Example'] as $head)
                            <th class="px-3 py-2 font-mono text-[11px] font-semibold uppercase tracking-wider text-primary-600">{{ $head }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($columns as [$col, $required, $what, $example])
                        <tr class="border-t border-gray-100 align-top dark:border-white/5">
                            <td class="px-3 py-2 font-mono text-xs font-semibold text-gray-900 dark:text-white">{{ $col }}</td>
                            <td class="px-3 py-2">
                                @if ($required)
                                    <span class="rounded-full bg-danger-50 px-2 py-0.5 text-xs font-semibold text-danger-700 dark:bg-danger-500/10 dark:text-danger-400">Required</span>
                                @else
                                    <span class="text-xs text-gray-400">Optional</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $what }}</td>
                            <td class="px-3 py-2 font-mono text-xs text-gray-500">{{ $example }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="mt-4 font-mono text-[11px] font-semibold uppercase tracking-widest text-primary-600">Example (what the file looks like)</p>
        <pre class="mt-2 overflow-x-auto rounded-lg bg-gray-50 p-3 font-mono text-xs leading-relaxed text-gray-700 ring-1 ring-gray-950/5 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10">name,brand,category,model_group,short_description,specs,is_active,spec: Capacity,spec: Readability
Entris II Essential,Sartorius,Analytical Balances,Standard Models,"Compact precision balance",Pan size: 90 mm | Display: LCD,no,220 g,0.1 mg
Entris II Advanced,Sartorius,Analytical Balances,Advanced Models,"Higher accuracy",Pan size: 120 mm | Display: Touch,yes,620 g,1 mg</pre>
        <p class="mt-3 text-xs text-gray-500">Tip: press "Download sample file" above, fill in your products and upload it. The sample opens correctly in Excel.</p>
    </div>
</x-filament-panels::page>
