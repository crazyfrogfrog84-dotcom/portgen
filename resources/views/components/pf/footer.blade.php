@props(['name' => '', 'links' => []])

<footer class="pf-footer">
    <p>© {{ now()->year }} {{ $name }}</p>
    <x-pf.contact-links :links="$links" />
</footer>
