{{-- resources/views/components/flash-message.blade.php --}}

@push('css')
    @vite(['resources/css/flash-message.css'])
@endpush

@if (session('message'))
    <div id="flash-message" class="flash-message">
        {{ session('message') }}
        <button onclick="this.parentElement.style.display='none'" class="flash-close">×</button>
    </div>
@endif

@if (session('error'))
    <div id="flash-error" class="flash-error">
        {{ session('error') }}
        <button onclick="this.parentElement.style.display='none'" class="flash-close">×</button>
    </div>
@endif
