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

        <br>

        <div class="row">
            <div class="col-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Package</th>
                            <th>Last 7 days</th>
                            <th>Last week</th>
                            <th>4 weeks ago</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dependencies as $dependency)
                            @php($stats = $dependency['stats']->reverse()->values())
                            <tr>
                                <td>
                                    <i class="{{ $vendors[$dependency['info']['vendor']] }}"></i> {{ $dependency['info']['name'] }}
                                </td>
                                <td>
                                    {{ $stats[0] }}
                                    (<span class="{{ $stats[0] > $stats[1] ? 'text-success' : 'text-warning' }}" data-title="Compared to last 7 days" data-toggle="tooltip">{{ $stats[0] > $stats[1] ? '+' : '' }}{{ $stats[0] - $stats[1] }}</span>)
                                </td>
                                <td>
                                    {{ $stats[1] }}
                                    (<span class="{{ $stats[1] > $stats[4] ? 'text-success' : 'text-warning' }}" data-title="Compared to 4 weeks ago" data-toggle="tooltip">{{ $stats[1] > $stats[4] ? '+' : '' }}{{ $stats[1] - $stats[4] }}</span>)
                                </td>
                                <td>
                                    {{ $stats[4] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection

@push('body.before_script')
    <script>
        window.pkgtrends = {!! json_encode(compact('vendors')) !!};
    </script>
@endpush
