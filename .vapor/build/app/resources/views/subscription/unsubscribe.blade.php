@extends('layout.default')

@section('content')
    @include('partial.alert')

    <div class="my-5 pb-3 text-center text-small">
        <h3>
            <i class="fa fa-fw fa-heart-broken text-black-50"></i>&nbsp;Just one more thing...
        </h3>
        <form class="form" method="POST" action="">
            @csrf
            <div class="form-row justify-content-center">
                <div class="col-auto mt-3">
                    <button type="submit" class="btn btn-outline-danger btn-lg">
                        Unsubscribe!
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
