@if(!config('app.debug') && config('app.analytics.fathom.siteId') !== null)
    <!-- Fathom - beautiful, simple website analytics -->
    <script src="https://piranha.pkgtrends.app/script.js" site="{{ config('app.analytics.fathom.siteId') }}" defer></script>
    <!-- / Fathom -->
@endif
