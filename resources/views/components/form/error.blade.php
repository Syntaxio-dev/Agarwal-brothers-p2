@props(['name'])
{{-- Inline error for one field. Shows the server message after a failed submit, or the browser check before it. --}}
<p x-cloak x-show="errors['{{ $name }}']" x-text="errors['{{ $name }}']" role="alert" {{ $attributes->class(['mt-1 text-xs font-medium text-alert']) }}></p>
