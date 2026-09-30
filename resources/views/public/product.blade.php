<x-layouts.app :title="$product->name">
    <div class="text-sm text-slate mb-2">
        <a href="/" class="hover:text-link">Home</a> /
        {{ $product->category->brand->name }} / {{ $product->category->name }}
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div>
            @if ($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" class="w-full rounded-lg shadow">
            @endif
        </div>

        <div>
            <h1 class="text-3xl font-bold mb-1">{{ $product->name }}</h1>
            <p class="text-slate mb-4">{{ $product->category->brand->name }} — {{ $product->category->name }}</p>
            <p class="mb-6">{{ $product->short_description }}</p>

            @if ($product->specs)
                <table class="w-full text-sm mb-6 border-t border-slate/20">
                    @foreach ($product->specs as $key => $value)
                        <tr class="border-b border-slate/20">
                            <td class="py-2 font-medium w-1/3">{{ $key }}</td>
                            <td class="py-2 text-slate">{{ $value }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif

            <button onclick="document.getElementById('enquiry-form').classList.remove('hidden')"
                class="bg-cyan text-navy font-medium rounded px-6 py-3">
                Send Enquiry
            </button>
        </div>
    </div>

    <div id="enquiry-form" class="hidden mt-10 bg-white rounded-lg shadow p-6 max-w-xl">
        <h2 class="text-xl font-bold mb-4">Send an Enquiry</h2>

        @if (session('success'))
            <p class="text-success mb-4">{{ session('success') }}</p>
        @endif

        <form method="POST" action="{{ route('enquiry.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">

            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" required class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" required class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Phone</label>
                <input type="text" name="phone" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Application Budget</label>
                <input type="text" name="budget" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Where is the order coming from?</label>
                <input type="text" name="order_location" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Message</label>
                <textarea name="message" rows="3" class="w-full border rounded px-3 py-2"></textarea>
            </div>

            <button type="submit" class="bg-navy text-white font-medium rounded px-6 py-3">
                Submit Enquiry
            </button>
        </form>
    </div>
</x-layouts.app>