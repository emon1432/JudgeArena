<header class="site-header sticky-top">
    <nav class="navbar navbar-expand-lg border-bottom py-2">
        <div class="container-fluid px-3 px-md-4">
            <button class="navbar-toggler border-0 p-2 me-2 d-lg-none" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#mobileNav" aria-controls="mobileNav" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars text-secondary fs-5"></i>
            </button>

            <a class="navbar-brand header-brand me-2 me-lg-4" href="{{ route('home') }}">
                <i class="fa-solid fa-code me-2 text-primary"></i>
                {{ settings('system_settings', 'app_name') }}
            </a>

            <div class="collapse navbar-collapse" id="desktopNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1 gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('platforms.*') ? 'active fw-semibold' : 'fw-medium' }}"
                            href="{{ route('platforms.index') }}">Platforms</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contests.*') ? 'active fw-semibold' : 'fw-medium' }}"
                            href="{{ route('contests.index') }}">Contests</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('problems.*') ? 'active fw-semibold' : 'fw-medium' }}"
                            href="{{ route('problems.index') }}">Problems</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('rankings.*') ? 'active fw-semibold' : 'fw-medium' }}"
                            href="{{ route('rankings.index') }}">Rankings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('community.*') ? 'active fw-semibold' : 'fw-medium' }}"
                            href="{{ route('community.index') }}">Community</a>
                    </li>
                </ul>

                <div class="header-search me-3 d-none d-xl-flex">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Search users, contests, problems..." />
                    <kbd class="search-kbd-hint">⌘K</kbd>
                </div>
            </div>

            <div class="d-flex align-items-center gap-1 gap-sm-2">
                <button class="header-icon-btn d-xl-none" type="button" data-bs-toggle="collapse"
                    data-bs-target="#mobileSearchCollapse" aria-expanded="false" title="Search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>

                @guest
                    <button class="header-icon-btn me-1" id="theme-toggle-btn" title="Toggle theme">
                        <i class="fa-solid fa-sun" id="theme-toggle-icon"></i>
                    </button>

                    <a href="{{ route('login') }}" class="header-icon-btn text-primary d-lg-none" title="Log In">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    </a>

                    <div class="d-none d-lg-block">
                        <a href="{{ route('login') }}"
                            class="btn btn-sm btn-outline-secondary fw-semibold rounded-3 px-3 py-1-5 ms-1">
                            <i class="fas fa-sign-in-alt me-1"></i> Log In
                        </a>
                        <a href="{{ route('register') }}"
                            class="btn btn-sm btn-primary fw-semibold rounded-3 px-3 py-1-5 shadow-sm ms-1">
                            <i class="fas fa-user-plus me-1"></i> Register
                        </a>
                    </div>
                @else
                    @if (Auth::user()->role === 'admin')
                        <button class="header-icon-btn me-1" id="theme-toggle-btn" title="Toggle theme">
                            <i class="fa-solid fa-sun" id="theme-toggle-icon"></i>
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="header-icon-btn text-primary me-1"
                            title="Admin Dashboard">
                            <i class="fa-solid fa-gauge-high"></i>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="header-icon-btn text-danger border-0 bg-transparent"
                                title="Sign Out">
                                <i class="fa-solid fa-arrow-right-from-bracket text-danger"></i>
                            </button>
                        </form>
                    @else
                        <div class="dropdown">
                            <button class="header-icon-btn position-relative" type="button" data-bs-toggle="dropdown"
                                data-bs-auto-close="outside" aria-expanded="false" title="Notifications">
                                <i class="fa-regular fa-bell"></i>
                                <span class="dot-badge"></span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end notification-dropdown-menu shadow-lg">
                                <div class="notification-header d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <h6 class="fw-bold mb-0 text-primary fs-6">
                                            Notifications
                                        </h6>
                                        <span class="badge bg-primary rounded-pill small">3 New</span>
                                    </div>
                                    <a href="#" class="text-muted small text-decoration-none hover-primary">Mark all
                                        as read</a>
                                </div>

                                <ul class="notification-list">
                                    <li>
                                        <a href="#" class="notification-item unread">
                                            <div class="notification-icon trophy">
                                                <i class="fa-solid fa-trophy"></i>
                                            </div>
                                            <div class="notification-content">
                                                <div>
                                                    <b>Codeforces Round #928
                                                        (Div. 2)</b>
                                                    is starting in 30 minutes!
                                                </div>
                                                <div class="notification-time">
                                                    <i class="fa-regular fa-clock me-1"></i>5 min ago
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="notification-item unread">
                                            <div class="notification-icon verdict">
                                                <i class="fa-solid fa-circle-check"></i>
                                            </div>
                                            <div class="notification-content">
                                                <div>
                                                    Your submission for
                                                    <b>1931F - Programmable
                                                        Robot</b>
                                                    passed System Tests.
                                                </div>
                                                <div class="notification-time">
                                                    <i class="fa-regular fa-clock me-1"></i>1 hour ago
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="notification-item unread">
                                            <div class="notification-icon blog">
                                                <i class="fa-solid fa-book-open"></i>
                                            </div>
                                            <div class="notification-content">
                                                <div>
                                                    Editorial for
                                                    <b>Educational Round 165</b>
                                                    has been published.
                                                </div>
                                                <div class="notification-time">
                                                    <i class="fa-regular fa-clock me-1"></i>3 hours ago
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="notification-item">
                                            <div class="notification-icon mention">
                                                <i class="fa-solid fa-at"></i>
                                            </div>
                                            <div class="notification-content">
                                                <div>
                                                    <b>Someone</b> mentioned you
                                                    in a comment on
                                                    <i>Global Round 25
                                                        Announcement</i>.
                                                </div>
                                                <div class="notification-time">
                                                    <i class="fa-regular fa-clock me-1"></i>Yesterday
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                </ul>

                                <div class="notification-footer">
                                    <a href="#" class="text-decoration-none">View All Notifications
                                        <i class="fa-solid fa-angle-right ms-1"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="dropdown">
                            <a class="header-avatar-btn border-0 bg-transparent p-0 ms-1 d-block" href="#"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false"
                                title="{{ Auth::user()->name }}">
                                <img src="{{ imageExists(Auth::user()->image) ? imageShow(Auth::user()->image) : Auth::user()->profile_photo_url }}"
                                    alt="{{ Auth::user()->name }}" />
                            </a>
                            <div class="dropdown-menu dropdown-menu-end profile-dropdown-menu shadow-lg">
                                <div class="profile-dropdown-header">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="profile-dropdown-user-name mb-0">
                                            {{ Auth::user()->name }}
                                        </div>
                                    </div>
                                    <div class="profile-dropdown-user-handle">
                                        {{ '@' . (Auth::user()->username ?? 'user') }}
                                    </div>
                                </div>

                                <div class="py-1">
                                    <a class="dropdown-item"
                                        href="{{ route('user.show', Auth::user()->username ?? 'user') }}">
                                        <i class="fa-regular fa-user me-1.5"></i> My Profile
                                    </a>

                                    <div class="dropdown-item d-flex align-items-center justify-content-between py-2"
                                        onclick="event.stopPropagation();">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-moon me-1"></i>
                                            <span>Dark Mode</span>
                                        </div>
                                        <div class="form-check form-switch m-0 p-0 d-flex align-items-center">
                                            <input class="form-check-input ms-0 cursor-pointer" type="checkbox"
                                                role="switch" id="theme-switch-checkbox"
                                                onchange="if (typeof applyTheme === 'function') applyTheme(this.checked ? 'dark' : 'light', true)">
                                        </div>
                                    </div>

                                    <a class="dropdown-item"
                                        href="{{ route('user.edit', Auth::user()->username ?? 'user') }}">
                                        <i class="fa-solid fa-gear me-1.5"></i> Account Settings
                                    </a>
                                    <hr class="dropdown-divider" />

                                    <!-- USER: Logout Action Form -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="dropdown-item text-danger w-100 text-start border-0 bg-transparent py-1.5">
                                            <i class="fa-solid fa-arrow-right-from-bracket text-danger me-1.5"></i>
                                            Sign Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @endguest
            </div>
        </div>
    </nav>

    <div class="collapse d-xl-none mobile-search-bar shadow-sm" id="mobileSearchCollapse">
        <div class="header-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Search users, contests, problems..." />
        </div>
    </div>
</header>

<aside class="offcanvas offcanvas-start mobile-nav-drawer" tabindex="-1" id="mobileNav"
    aria-labelledby="mobileNavLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title header-brand mb-0" id="mobileNavLabel">
            <a href="{{ route('home') }}" class="text-decoration-none">
                <i class="fa-solid fa-code me-2"></i>
                {{ settings('system_settings', 'app_name') }}
            </a>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column justify-content-between">
        <ul class="navbar-nav gap-2">
            <li class="nav-item">
                <a class="nav-link py-2 {{ request()->routeIs('platforms.*') ? 'active fw-semibold' : '' }}"
                    href="{{ route('platforms.index') }}">Platforms</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 {{ request()->routeIs('contests.*') ? 'active fw-semibold' : '' }}"
                    href="{{ route('contests.index') }}">Contests</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 {{ request()->routeIs('problems.*') ? 'active fw-semibold' : '' }}"
                    href="{{ route('problems.index') }}">Problems</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 {{ request()->routeIs('rankings.*') ? 'active fw-semibold' : '' }}"
                    href="{{ route('rankings.index') }}">Rankings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 {{ request()->routeIs('community.*') ? 'active fw-semibold' : '' }}"
                    href="{{ route('community.index') }}">Community</a>
            </li>
        </ul>
        <div class="mt-4 pt-3 border-top d-flex gap-2">
            @guest
                <a href="{{ route('login') }}" class="btn btn-outline-secondary fw-semibold w-100">
                    <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Log In
                </a>
                <a href="{{ route('register') }}" class="btn btn-primary fw-semibold w-100">
                    <i class="fa-solid fa-user-plus me-1"></i> Register
                </a>
            @else
                @if (Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary fw-semibold w-100">
                        <i class="fa-solid fa-gauge-high me-1"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('user.show', Auth::user()->username ?? 'user') }}"
                        class="btn btn-outline-secondary fw-semibold w-100">
                        <i class="fa-regular fa-user me-1"></i> Profile
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="w-100">
                    @csrf
                    <button type="submit" class="btn btn-danger fw-semibold w-100">
                        <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Log Out
                    </button>
                </form>
            @endguest
        </div>
    </div>
</aside>
