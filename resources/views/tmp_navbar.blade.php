            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Logout button -->
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('logout') }}" 
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                       title="Sign out">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </li>