@extends('layout.default', ['title' => isset($query) ? $query->getFormattedTitle() : null])

@section('content')
    <div class="row {{ isset($query) ? 'pb-4' : 'pb-2 pt-5' }}">
        <div class="col-12">
            <input type="text" class="form-control" id="packages" placeholder="Search for packages" value="">
        </div>
    </div>

    @if(isset($query))
        <input type="hidden" id="package-options" data-value='@json($query->getData()->pluck('info'))'>
        <input type="hidden" id="package-items" data-value='@json($query->getData()->pluck('info.id'))'>

        <div class="row">
            <div class="col-12">
                <canvas id="chart" width="400" height="250"></canvas>

                @php
                    $colors = [
                        '#6f42c1', '#dc3545', '#fd7e14', '#ffc107', '#28a745', '#17a2b8', '#343a40', '#007bff',
                        '#6f42c1', '#dc3545', '#fd7e14', '#ffc107', '#28a745', '#17a2b8', '#343a40', '#007bff'
                    ];

                    $dataMapper = function ($dependency) use (&$colors) {
                        return [
                            'label' => $dependency['info']['name'],
                            'data' => $dependency['stats'],
                            'borderColor' => array_pop($colors),
                            'backgroundColor' => 'rgba(0,0,0,0)',
                        ];
                    };
                @endphp

                <input type="hidden" id="chart-labels" data-value='@json($query->getGraphLabels()->all())'>
                <input type="hidden" id="chart-datasets" data-value='@json($query->getData()->map($dataMapper)->values())'>
            </div>
        </div>

        <br>

        <div class="row">
            <div class="col-12">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="border-top-0">Package</th>
                            <th class="border-top-0">Last 7 days</th>
                            <th class="border-top-0">Last week</th>
                            <th class="border-top-0">4 weeks ago</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($query->getData() as $dependency)
                            @php($stats = $dependency['stats']->reverse()->values())
                            <tr>
                                <td>
                                    <i class="{{ $vendors[$dependency['info']['vendor']] }} fa-fw"></i>
                                    <a href="{{ $dependency['info']['permalink'] }}" target="_blank" rel="noopener">{{ $dependency['info']['name'] }}</a><br>
                                    <small class="text-muted">{{ $dependency['info']['source_formatted'] }}</small>
                                </td>
                                <td>
                                    {{ number_format($stats[0]) }}<br>
                                    <small>
                                    <span class="{{ $stats[0] > $stats[4] ? 'text-success' : 'text-warning' }}" data-title="Compared to 4 weeks ago" data-toggle="tooltip">
                                        {{ $stats[0] > $stats[4] ? '+' : '-' }}{{ abs(100 - (int)(100 * ($stats[0] ?: 1) / ($stats[4] ?: 1))) }}%
                                    </span>
                                    </small>
                                </td>
                                <td>
                                    {{ number_format($stats[1]) }}
                                </td>
                                <td>
                                    {{ number_format($stats[4]) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @include('partial.forms.subscribe')
    @endif
@endsection

@push('body.before_script')
    <script>
        window.pkgtrends = @json(compact('vendors'));
    </script>
@endpush
