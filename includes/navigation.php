<nav class="navbar bg-primary navbar-dark navbar-expand-sm">
  <div class="container">
    <!-- Links -->
    <ul class="nav navbar-nav" style="visibility: visible;">
      <li class="nav-item">
        <a class="nav-link <?= ($nav_active == 'home') ? 'active': false; ?>" href="<?= URL ?>">Home</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= ($nav_active == 'events') ? 'active': false; ?>" href="<?= URL ?>events.php">Events</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= ($nav_active == 'register') ? 'active': false; ?>" href="<?= URL ?>register.php">Register</a>
      </li>
      <?php if (isset($_SESSION['username'])): ?>
      <li class="nav-item">
        <a class="nav-link <?= ($nav_active == 'settings') ? 'active': false; ?>" href="<?= URL ?>settings.php">Settings</a>
      </li>
      <?php endif; ?>
    </ul>

    <ul class="navbar-nav">
      <?php if (isset($_SESSION['username'])): ?>
      <li class="nav-item">
        <a class="nav-link" href="?logout">Logout</a>
      </li>
      <?php else: ?>
      <li class="nav-item">
        <a class="nav-link" href="login.php">Login</a>
      </li>
      <?php endif; ?>
    </ul>
  </div>
</nav>