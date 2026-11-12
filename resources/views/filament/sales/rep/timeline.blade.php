@php $record = $getRecord(); $tz = config('sales.timezone'); @endphp
@if ($record)
    <div>
        <h3 class="sx-eyebrow">Timeline</h3>
        <ul class="en-timeline" style="margin-top:0.5rem">
            @forelse (\App\Sales\Support\SalesInsights::enquiryTimeline($record) as $line)
                <li>
                    <span class="sx-small sx-mute">{{ $line['at']->format('d M Y, h:i A') }}</span>
                    @if ($line['status'])<span class="mv-chip">{{ $line['status'] }}</span>@endif
                    <span class="en-tl-text">{{ $line['text'] }}</span>
                </li>
            @empty
                <li class="sx-small sx-mute">Nothing written yet.</li>
            @endforelse
        </ul>
    </div>
@endif
