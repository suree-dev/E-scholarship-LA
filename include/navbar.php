<?php
$to_root = "";
if (file_exists('assets/images/bg/update-09.png')) {
    $to_root = "./";
} elseif (file_exists('../assets/images/bg/update-09.png')) {
    $to_root = "../";
} elseif (file_exists('../../assets/images/bg/update-09.png')) {
    $to_root = "../../";
} else {
    $to_root = "../";
}
?>
<nav class="navbar">
    <a href="<?php echo $to_root; ?>root/index.php" class="navbar-brand p-0">
        <img src="<?php echo $to_root; ?>assets/images/bg/update-09.png" alt="PSU E-Scholarship Logo" class="navbar-logo">
    </a>

    <a href="https://www.facebook.com/pages/งานกิจการนักศึกษา-คณะศิลปศาสตร์-มอ/374074482603129" target="_blank" class="social-button">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z" />
        </svg>
        <span>ติดตามข่าวสาร</span>
    </a>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.querySelector('.navbar');
        const statusBar = document.querySelector('.user-status-bar');

        if (navbar) {
            window.addEventListener('scroll', function() {
                if (window.innerWidth > 767) {
                    if (window.scrollY > 50) {
                        navbar.classList.add('navbar-scrolled');
                        if (statusBar) statusBar.classList.add('bar-scrolled');
                    } else {
                        navbar.classList.remove('navbar-scrolled');
                        if (statusBar) statusBar.classList.remove('bar-scrolled');
                    }
                }
            });
        }

        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            const menuHeader = sidebar.querySelector('.menu-header');
            const hasSubmenuLinks = sidebar.querySelectorAll('.has-submenu > a');

            if (menuHeader) {
                menuHeader.addEventListener('click', function() {
                    sidebar.classList.toggle('is-open');
                    if (!sidebar.classList.contains('is-open')) {
                        sidebar.querySelectorAll('.submenu-open').forEach(li => li.classList.remove('submenu-open'));
                    }
                });
            }

            hasSubmenuLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (window.innerWidth <= 1024) {
                        const listItem = link.closest('li');

                        if (sidebar.classList.contains('is-open')) {
                            e.preventDefault();
                            listItem.classList.toggle('submenu-open');

                            listItem.parentNode.querySelectorAll('.has-submenu').forEach(item => {
                                if (item !== listItem) item.classList.remove('submenu-open');
                            });
                        }
                    }
                });
            });
        }

        const fileInput = document.getElementById('profile-pic');
        const fileNameDisplay = document.getElementById('file-name-info');
        if (fileInput && fileNameDisplay) {
            fileInput.addEventListener('change', function() {
                fileNameDisplay.textContent = (this.files && this.files.length > 0) ? this.files[0].name : 'No file chosen';
            });
        }
    });
</script>