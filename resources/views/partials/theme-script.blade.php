<script>
    (function () {
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }

        if (localStorage.getItem('fullscreenPreferred') === 'true') {
            document.documentElement.classList.add('asp-app-fullscreen');
        }
    })();
</script>
