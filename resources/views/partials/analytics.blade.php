@php($measurementId = config('services.analytics.measurement_id'))
@if($measurementId)
    <!-- Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $measurementId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);} // eslint-disable-line
        gtag('js', new Date());
        gtag('config', '{{ $measurementId }}');
    </script>
@endif
