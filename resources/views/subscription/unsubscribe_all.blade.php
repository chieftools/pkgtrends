@extends('layout.default')

@section('content')
    @include('partial.alert')

    <div class="my-5 pb-3 text-muted text-center text-small">
        <h4 class="mb-2">
            <i class="fa fa-fw fa-bomb text-black-50"></i>&nbsp;&nbsp;Just one more thing...
        </h4>
        <p>
            You will be unsubscribed from all <b>{{ $subsriptions->count() }}</b> subscriptions.
        </p>
        <form class="form" method="POST" action="">
            @csrf
            <div class="form-row justify-content-center">
                <div class="col-auto my-1">
                    <button type="submit" class="btn btn-primary form-control">Unsubscribe all!</button>
                </div>
            </div>
        </form>
    </div>
@endsection
