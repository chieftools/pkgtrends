@extends('layout.html')

@section('body')
    <div class="container">
        <div class="py-5 text-center">
            <h1><i class="fas fa-chart-line text-body-emphasis"></i> {{ config('app.name') }}</h1>
            <p class="lead">{!! nl2br(trim(str_replace('.', ".\n", config('app.description')))) !!}</p>
        </div>

        @include('partial.alert')

        @yield('content')

        <footer class="my-5 pt-5 text-muted text-center text-small">
            <div class="btn-group btn-group-sm mb-4" role="group" aria-label="Theme">
                <button type="button" class="btn btn-outline-secondary" data-theme-value="light" aria-label="Light theme" data-toggle="tooltip" title="Light">
                    <i class="fas fa-sun"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary" data-theme-value="system" aria-label="Use system theme" data-toggle="tooltip" title="System">
                    <i class="fas fa-desktop"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary" data-theme-value="dark" aria-label="Dark theme" data-toggle="tooltip" title="Dark">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
            <p class="mb-3">
                Data sourced from
                <br>
                <small>
                    @foreach(config('app.sources') as $source)
                        <i class="{{ $source::getIcon() }} text-body-emphasis"></i> {!! collect($source::getSources())->map(function ($url, $name) {
                            return '<a href="' . e($url) . '" target="_blank" rel="noopener">' . e($name) . '</a>';
                        })->implode(' & ') !!}{!! $loop->last ? '' : '&nbsp;&nbsp;&middot;&nbsp;&nbsp;' !!}
                    @endforeach
                </small>
            </p>
            <p class="mb-3">
                Made with ❤ by <a href="https://github.com/chieftools/pkgtrends/graphs/contributors" target="_blank" rel="noopener">all contributors</a> & <i class="fa fa-toolbox"></i> <a href="https://chief.app?ref=pkgtrends" target="_blank" rel="noopener">Chief Tools</a>.
                <br>
                <small>
                    Seeing something broken or have suggestions? <a href="https://github.com/chieftools/pkgtrends/issues/new" target="_blank" rel="noopener">Let us know!</a>
                </small>
            </p>
            <small class="text-muted">
                @if(config('app.analytics.fathom.public'))
                    <a href="{{ config('app.analytics.fathom.public') }}" target="_blank" rel="noopener" class="text-muted">Analytics</a> &middot;
                @endif
                {{ config('app.versionString') }} (<a href="https://github.com/chieftools/pkgtrends/tree/{{ config('app.version') }}" class="text-muted">{{ config('app.version') }}</a>)
            </small>
        </footer>
    </div>
@endsection
