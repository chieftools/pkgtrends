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
            <div class="col-lg-3 col-md-5 col-sm-7 px-0 me-3">
                <input type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="Your e-mail" name="email" value="{{ old('email') }}"/>
                @if($error = ($errors->has('email') || $errors->has('h-captcha-response')))
                    <div class="invalid-feedback">
                        {{ $errors->first('email') ?? $errors->first('h-captcha-response') }}
                    </div>
                @endif
            </div>
            <div class="col-auto px-0">
                <button type="submit" class="btn btn-primary form-control h-captcha" data-sitekey="{{ config('services.hcaptcha.key') }}" data-callback="hCaptchaCallback" data-badge="inline">Subscribe</button>
                @if($error)
                    <div>&nbsp;</div>
                @endif
            </div>
        </div>
        <small class="pt-3 d-block text-muted">
            This form is protected by <a href="https://www.hcaptcha.com" target="_blank" rel="noopener">hCaptcha</a> and their
            <a href="https://www.hcaptcha.com/privacy" target="_blank" rel="noopener">Privacy Policy</a> and
            <a href="https://www.hcaptcha.com/terms" target="_blank" rel="noopener">Terms of Service</a> apply.
        </small>
    </form>
</div>

@push('body.script')
    <script src="https://hcaptcha.com/1/api.js" async defer></script>
    <script>
        function hCaptchaCallback(token) {
            document.getElementById('subscriptionForm').submit();
        }
    </script>
@endpush
