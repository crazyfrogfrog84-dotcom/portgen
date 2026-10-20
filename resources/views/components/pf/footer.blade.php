@props(['name' => '', 'links' => [], 'email' => null, 'simple' => false])

<footer class="pf-footer pf-site-footer">
    <p>© {{ now()->year }} {{ $name }}</p>
    @if ($simple)
        <a class="surface-raised" href="#home">Back to top</a>
        <a class="surface-raised" href="{{ route('portfolios.create') }}">Made with PortGen</a>
    @else
        <x-pf.contact-links :links="$links" :email="$email" />
    @endif
</footer>
