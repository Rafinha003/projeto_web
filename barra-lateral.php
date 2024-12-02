<!--SIDEBAR-->
<div class="d-flex flex-nowrap">
    <div class="d-flex flex-column flex-shrink-0 p-3 text-bg-dark sidebar" style="width: 280px;">
        <a href="pagina-inicial.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none" id="link-inicial">
            <span class="fs-4">CarsGram</span>
        </a>
        <hr>

        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="pagina-inicial.php" class="nav-link text-white" id="link-home">
                    Página Inicial
                </a>
            </li>
            <li>
                <a href="perfil.php" class="nav-link text-white" id="link-perfil">
                    Perfil
                </a>
            </li>
        </ul>
        <hr>

        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                data-bs-toggle="dropdown" aria-expanded="false">
                <strong>Configurações</strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item" href="Login.php">Sign out</a></li>
            </ul>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const currentPath = window.location.pathname;

            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.classList.remove('active');
            });

            if (currentPath.includes('pagina-inicial.php')) {
                document.getElementById('link-home').classList.add('active');
            } else if (currentPath.includes('perfil.php')) {
                document.getElementById('link-perfil').classList.add('active');
            }
        });
    </script>