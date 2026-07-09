@if (config('desktop.enabled'))
    <meta name="app-runtime" content="electron">
    @if (config('desktop.remote_sync_url'))
        <meta name="desktop-remote-sync-url" content="{{ config('desktop.remote_sync_url') }}">
    @endif
@endif
