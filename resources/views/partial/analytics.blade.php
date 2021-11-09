@if(!config('app.debug') && config('app.analytics.fathom.site') !== null)
    <script src="https://{{ config('app.analytics.fathom.domain') }}/script.js" data-site="{{ config('app.analytics.fathom.site') }}" defer></script>
@endif
