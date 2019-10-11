@if(Session::has('message'))
    <div class="alert alert-{{ Session::get('message.type', 'info') }}">
        {{ Session::get('message.text') }}
    </div>
@endif
