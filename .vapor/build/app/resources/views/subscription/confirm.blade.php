@extends('layout.default')

@section('content')
    @include('partial.alert')

    <div class="my-5 pb-3 text-center text-small">
        <h3>Just one more thing...</h3>
        <form class="form" method="POST" action="">
            @csrf
            <div class="form-row justify-content-center">
                <div class="col-auto mt-3">
                    <button type="submit" class="btn btn-outline-success btn-lg">
                        Confirm subscription!
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
