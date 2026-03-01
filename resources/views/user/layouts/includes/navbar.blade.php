<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm fixed-top py-2">
    <div class="container-fluid px-4 px-lg-5">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}">
            <i class="bi bi-code-square fs-4"></i>
            <span>JudgeArena</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
            aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link px-3" href="{{ route('community') }}">Community</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="{{ route('problems') }}">Problems</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="{{ route('contests') }}">Contests</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="{{ route('leaderboard') }}">Leaderboard</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="{{ route('contact.us') }}">Contact Us</a></li>
            </ul>

            <ul class="navbar-nav ms-lg-3 align-items-lg-center gap-lg-2">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userDropdown"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @if (auth()->user()->image && imageExists(auth()->user()->image))
                                <img src="{{ imageShow(auth()->user()->image) }}" alt="Profile Image" class="rounded-circle"
                                    width="32" height="32">
                            @else
                                <i class="bi bi-person-circle fs-5"></i>
                            @endif
                            <span class="fw-medium">{{ auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                            <li><a class="dropdown-item"
                                    href="{{ route('user.profile.show', auth()->user()->username) }}"><i
                                        class="bi bi-person-circle me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item"
                                    href="{{ route('user.profile.edit', auth()->user()->username) }}"><i
                                        class="bi bi-gear me-2"></i>Settings</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i
                                            class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link px-3" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-dark rounded-pill px-4 mt-2 mt-lg-0" href="{{ route('register') }}">Register</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
