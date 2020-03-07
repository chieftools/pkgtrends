@component('mail::message')
# Just one more step

To receive your weekly trends report click the button below.

@component('mail::button', ['url' => url()->signedRoute('subscription.confirm', [$subscription->id])])
Confirm subscription
@endcomponent

You will be subscribed to a weekly report for: {{ $packages }}.

If you did not request this, you can ignore this e-mail safely.

Greetings,<br>
<a href="{{ route('home') }}">{{ config('app.name') }}</a>
@endcomponent
