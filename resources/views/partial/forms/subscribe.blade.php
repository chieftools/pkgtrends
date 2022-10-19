<div class="my-5 pt-4 text-center text-small">
    <h4 class="mb-2">
        <i class="fa fa-fw fa-broadcast-tower text-muted"></i>&nbsp;&nbsp;Subscribe to this report
    </h4>
    <p>
        Get a weekly e-mail from us with the latest trends for this report.
    </p>
    <form id="subscriptionForm" class="form" method="POST" action="{{ route('subscription.create', [request('query')]) }}">
        @csrf
        <div class="row justify-content-center">
            <div class="col-lg-3 col-md-5 col-sm-7 px-0">
                <input type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="Your e-mail" name="email" value="{{ old('email') }}"/>
                @if($error = ($errors->has('email') || $errors->has('cf-turnstile-response')))
                    <div class="invalid-feedback">
                        {{ $errors->first('email') ?? $errors->first('cf-turnstile-response') }}
                    </div>
                @endif
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-auto px-0">
                <div class="mt-2 cf-turnstile" data-sitekey="{{ config('services.turnstile.key') }}" data-theme="light"></div>
                <button type="submit" class="btn btn-primary form-control">Subscribe</button>
                @if($error)
                    <div>&nbsp;</div>
                @endif
            </div>
        </div>
    </form>
</div>

@push('body.script')
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endpush
