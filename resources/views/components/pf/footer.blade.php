@props(['name' => '', 'links' => [], 'email' => null])

<footer class="pf-footer pf-site-footer">
    <p>© {{ now()->year }} {{ $name }}</p>
    <x-pf.contact-links :links="$links" :email="$email" />
</footer>
