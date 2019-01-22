@component('mail::message')
# Your Weekly Update

Here are the packages you requested us to keep an eye on.

The number below "last 7 days" compares to "4 weeks ago".

<div id="deps">
@component('mail::table')
| Package | Last 7 days | Last week | 4 weeks ago |
|:------- |:----------- |:--------- |:----------- |
@foreach($deps as $dependency)
@php($stats = $dependency['stats']->reverse()->values())
| <a href="{{ $dependency['info']['permalink'] }}">{{ $dependency['info']['name'] }}</a><br><small>{{ $dependency['info']['source_formatted'] }}</small> | {{ $stats[0] }}<br><small style="color: {{ $stats[0] > $stats[4] ? '#28a745' : '#ffc107' }};">{{ $stats[0] > $stats[4] ? '+' : '' }}{{ $stats[0] - $stats[4] }}</small> | {{ $stats[1] }} | {{ $stats[4] }} |
@endforeach
@endcomponent
</div>

Don't want to receive these weekly updates?<br>
Unsubscribe for <a href="#">this one</a>, or <a href="#">all of them</a>.

Greatings,<br>
{{ config('app.name') }}
<style>#deps table { width: 100%; }</style>
@endcomponent
