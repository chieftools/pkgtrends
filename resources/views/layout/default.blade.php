@extends('layout.html', ['bodyClass' => 'bg-light'])

@section('body')
    <div class="container">
        <div class="py-5 text-center">
            <h1><i class="fas fa-chart-line" style="color: #2c3e50;"></i> {{ config('app.name') }}</h1>
            <p class="lead">{!! nl2br(trim(str_replace('.', ".\n", config('app.description')))) !!}</p>
        </div>

        @yield('content')

        <footer class="my-5 pt-5 text-muted text-center text-small">
            <p class="mb-2">Made with ❤ in The Netherlands by <a href="https://twitter.com/@stayallive" target="_blank" rel="noopener">@stayallive</a></p>
            Data sourced from
            <p class="mb-1">
                @foreach(config('app.sources') as $source)
                    <i class="{{ $source::getIcon() }}" style="color: #2c3e50;"></i> {!! collect($source::getSources())->map(function ($url, $name) {
                        return '<a href="' . e($url) . '" target="_blank" rel="noopener">' . e($name) . '</a>';
                    })->implode(' & ') !!}{!! $loop->last ? '' : '&nbsp;&nbsp;&middot;&nbsp;&nbsp;' !!}
                @endforeach
            </p>
        </footer>
    </div>
@endsection
