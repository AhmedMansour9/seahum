{{-- One Meta Pixel event, sent only when the pixel is installed from admin (Third party > Analytics). --}}
<script>
    if (typeof window.fbq === 'function') {
        fbq('track', @json($pixelEvent), @json($pixelData){!! !empty($pixelEventId) ? ', '.json_encode(['eventID' => $pixelEventId]) : '' !!});
    }
</script>
