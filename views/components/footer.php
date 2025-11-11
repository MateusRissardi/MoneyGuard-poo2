</main>
<hr>
<footer>
    <p>&copy; 2025 MoneyGuard</p>
</footer>

</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');

        if (sidebar && toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                sidebar.classList.toggle('closed');
            });
        }
    });
</script>

</body>

</html>