<div wire:offline class="blinking-div">
    {{ __('This device is currently offline.') }}
</div>

<!-- Trigger offline event on load if already offline -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!navigator.onLine) {
            window.dispatchEvent(new Event('offline'));
        }
    });
</script>
