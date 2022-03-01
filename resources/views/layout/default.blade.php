@extends('layout.html', ['bodyClass' => 'bg-light'])

@section('body')
    <div class="container">
        <div class="py-5 text-center">
            <h1><i class="fas fa-chart-line" style="color: #2c3e50;"></i> {{ config('app.name') }}</h1>
            <p class="lead">{!! nl2br(trim(str_replace('.', ".\n", config('app.description')))) !!}</p>
        </div>

        @include('partial.alert')

        @yield('content')

        <footer class="my-5 pt-5 text-muted text-center text-small">
            <p class="mb-3">
                Data sourced from
                <br>
                <small>
                    @foreach(config('app.sources') as $source)
                        <i class="{{ $source::getIcon() }}" style="color: #2c3e50;"></i> {!! collect($source::getSources())->map(function ($url, $name) {
                            return '<a href="' . e($url) . '" target="_blank" rel="noopener">' . e($name) . '</a>';
                        })->implode(' & ') !!}{!! $loop->last ? '' : '&nbsp;&nbsp;&middot;&nbsp;&nbsp;' !!}
                    @endforeach
                </small>
            </p>
            <p class="mb-3">
                Made with ❤ by <a href="https://github.com/irongate/pkgtrends/graphs/contributors" target="_blank" rel="noopener">all contributors</a> & <i class="fa fa-toolbox"></i> <a href="https://chief.app?ref=pkgtrends" target="_blank" rel="noopener">Chief Tools</a>.
                <br>
                <small>
                    Seeing something broken or have suggestions? <a href="https://github.com/irongate/pkgtrends/issues/new" target="_blank" rel="noopener">Let us know!</a>
                </small>
            </p>
            <small class="text-muted">
                @if(config('app.analytics.fathom.public'))
                    <a href="{{ config('app.analytics.fathom.public') }}" target="_blank" rel="noopener" class="text-muted">Analytics</a> &middot;
                @endif
                {{ config('app.versionString') }} (<a href="https://github.com/irongate/pkgtrends/tree/{{ config('app.version') }}" class="text-muted">{{ config('app.version') }}</a>)
            </small>
        </footer>
    </div>
@endsection
