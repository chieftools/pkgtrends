@extends('layout.default')

@section('content')
    @include('partial.alert')

    <div class="my-5 pb-3 text-center text-small">
        <h3>
            <i class="fa fa-fw fa-bomb text-black-50"></i>&nbsp;Just one more thing...
        </h3>
        <p>
            You will be unsubscribed from all <b>{{ $subscriptions->count() }}</b> subscriptions.
        </p>
        <form class="form" method="POST" action="">
            @csrf
            <div class="form-row justify-content-center">
                <div class="col-auto mt-3">
                    <button type="submit" class="btn btn-outline-danger btn-lg">
                        Unsubscribe all!
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
