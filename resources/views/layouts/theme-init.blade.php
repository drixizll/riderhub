<script>
    (() => {
        try {
            const preference = localStorage.getItem('riderhub-theme');
            const theme = preference || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.dataset.theme = theme;
        } catch {
            document.documentElement.dataset.theme = 'light';
        }
    })();
</script>
