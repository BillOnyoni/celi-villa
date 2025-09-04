<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="/celica/index.php">Celica Computers</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <?php if (isLoggedIn()): ?>
            <li class="nav-item"><a class="nav-link" href="/celica/logout.php">Logout</a></li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="/celica/login.php">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="/celica/register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
