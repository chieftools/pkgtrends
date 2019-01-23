<div class="my-5 pt-4 text-muted text-center text-small">
    <h4 class="mb-2">
        <i class="fa fa-fw fa-broadcast-tower text-black-50"></i>&nbsp;&nbsp;Subscribe to this report
    </h4>
    <p>Get a weekly e-mail from us with the latest trends for this report.</p>
    <form class="form" method="POST" action="/{{ $packages }}/subscribe">
        @csrf
        <div class="form-row justify-content-center">
            <div class="col-3 my-1">
                <input type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"  placeholder="Your e-mail" name="email" >
                @if($error = $errors->has('email'))
                    <div class="invalid-feedback">
                        {{ $errors->first('email') }}
                    </div>
                @endif
            </div>
            <div class="col-auto my-1">
                <button type="submit" class="btn btn-primary form-control">Subscribe!</button>
                @if($error)
                    <div>&nbsp;</div>
                @endif
            </div>
        </div>
    </form>
</div>

