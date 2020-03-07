@component('mail::message')
# Your Weekly Update

Here are the packages you requested us to keep an eye on.

@component('mail::table')
| Package | Last 7 days | Last week | 4 weeks ago |
|:------- | -----------:| ---------:| -----------:|
@foreach($deps as $dependency)
@php($stats = $dependency['stats']->reverse()->values())
| <a href="{{ $dependency['info']['permalink'] }}">{{ $dependency['info']['name'] }}</a><br><small>{{ $dependency['info']['source_formatted'] }}</small> | {{ number_format($stats[0]) }}<br><small style="color: {{ $stats[0] > $stats[4] ? '#28a745' : '#ffc107' }};">{{ $stats[0] > $stats[4] ? '+' : '-' }}{{ abs(100 - (int)(100 * ($stats[0] ?: 1) / ($stats[4] ?: 1))) }}%</small> | {{ number_format($stats[1]) }}<br>&nbsp; | {{ number_format($stats[4]) }}<br>&nbsp; |
@endforeach
@endcomponent

<em>The number below "last 7 days" compares to "4 weeks ago".</em>

@component('mail::button', ['url' => $subscription->report->permalink])
View on {{ config('app.name') }}
@endcomponent

Don't want to receive these weekly updates anymore?<br>
Unsubscribe for <a href="{{ url()->signedRoute('subscription.unsubscribe', [$subscription->id]) }}">this one</a>, or <a href="{{ url()->signedRoute('subscription.unsubscribe_all', [$subscription->email]) }}">all of them</a>.

Greetings,<br>
<a href="{{ route('home') }}">{{ config('app.name') }}</a>
@endcomponent
