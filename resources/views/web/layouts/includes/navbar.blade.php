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
                <li class="nav-item"><a class="nav-link" href="{{ route('community') }}">Community</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('problems') }}">Problems</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('contests') }}">Contests</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('leaderboard') }}">Leaderboard</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('contact.us') }}">Contact Us</a></li>
            </ul>

            <ul class="navbar-nav mb-2 mb-lg-0 align-items-lg-center gap-lg-1">
                @auth
                    @if (auth()->user()->role === 'admin')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                    @else
                        <li class="nav-item ms-3">
                            <a href="{{ route('user.profile.show', ['username' => auth()->user()->username]) }}"
                                class="btn btn-primary-gradient">
                                <i class="bi bi-person-circle"></i>
                                Profile
                            </a>
                        </li>
                    @endif
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-dark rounded-pill px-4 mt-2 mt-lg-0" href="{{ route('register') }}">Register</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
