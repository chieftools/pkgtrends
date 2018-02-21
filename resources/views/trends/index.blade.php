@extends('layout.default')

@section('content')
    <div class="row pb-5">
        <div class="col-12">
            <input type="text" class="form-control" id="packages" placeholder="Search for packages" value="">
        </div>
    </div>

    @if(isset($dependencies) && $dependencies->isNotEmpty())
        <input type="hidden" id="package-options" data-value='{!! json_encode_html($dependencies->pluck('info')) !!}'>
        <input type="hidden" id="package-items" data-value='{!! json_encode_html($dependencies->pluck('info.id')) !!}'>

        <div class="row">
            <div class="col-12">
                <canvas id="chart" width="400" height="250"></canvas>

                @php
                    $colors = [
                        '#6f42c1', '#dc3545', '#fd7e14', '#ffc107', '#28a745', '#17a2b8', '#343a40', '#007bff',
                        '#6f42c1', '#dc3545', '#fd7e14', '#ffc107', '#28a745', '#17a2b8', '#343a40', '#007bff'
                    ];
                @endphp

                <input type="hidden" id="chart-labels" data-value='{!! json_encode_html($statLabels->all()) !!}'>
                <input type="hidden" id="chart-datasets" data-value='{!! json_encode_html($dependencies->map(function ($dependency) use (&$colors) {
                        return [
                            'label' => $dependency['info']['name'],
                            'data' => $dependency['stats'],
                            'borderColor' => array_pop($colors),
                            'backgroundColor' => 'rgba(0,0,0,0)'
                        ];
                    })->values()) !!}'>
            </div>
        </div>
    @endif
@endsection

@push('body.before_script')
    <script>
        window.pkgtrends = {!! json_encode(['vendors' => collect(config('app.sources'))->mapWithKeys(function ($source) { return [$source::getKey() => $source::getIcon()]; })]) !!};
    </script>
@endpush
