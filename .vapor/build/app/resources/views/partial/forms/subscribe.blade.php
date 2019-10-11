<div class="my-5 pt-4 text-center text-small">
    <h4 class="mb-2">
        <i class="fa fa-fw fa-broadcast-tower text-muted"></i>&nbsp;&nbsp;Subscribe to this report
    </h4>
    <p>
        Get a weekly e-mail from us with the latest trends for this report.
    </p>
    <form id="subscriptionForm" class="form" method="POST" action="{{ request()->fullUrl() }}/subscribe">
        @csrf
        <div class="form-row justify-content-center">
            <div class="col-lg-3 col-md-5 col-sm-7 my-1">
                <input type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="Your e-mail" name="email" value="{{ old('email') }}"/>
                @if($error = ($errors->has('email') || $errors->has('g-recaptcha-response')))
                    <div class="invalid-feedback">
                        {{ $errors->first('email') ?? $errors->first('g-recaptcha-response') }}
                    </div>
                @endif
            </div>
            <div class="col-auto my-1">
                <button type="submit" class="btn btn-primary form-control g-recaptcha" data-sitekey="{{ config('services.recaptcha.key') }}" data-callback="recaptchaCallback" data-badge="inline">Subscribe</button>
                @if($error)
                    <div>&nbsp;</div>
                @endif
            </div>
        </div>
        <small class="pt-3 d-block text-muted">
            This site is protected by reCAPTCHA and the Google
            <a href="https://policies.google.com/privacy" target="_blank" rel="nofollow noopener">Privacy Policy</a> and
            <a href="https://policies.google.com/terms" target="_blank" rel="nofollow noopener">Terms of Service</a> apply.
        </small>
    </form>
</div>

@push('body.script')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        function recaptchaCallback(token) {
            document.getElementById('subscriptionForm').submit();
        }
    </script>
@endpush
