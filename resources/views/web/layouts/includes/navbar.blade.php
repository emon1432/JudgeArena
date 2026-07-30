<!-- ================= HEADER / BOOTSTRAP NAVBAR ================= -->
<header class="site-header sticky-top">
    <nav class="navbar navbar-expand-lg border-bottom py-2">
        <div class="container-fluid px-3 px-md-4">
            <!-- Mobile Toggler Button -->
            <button class="navbar-toggler border-0 p-2 me-2 d-lg-none" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#mobileNav" aria-controls="mobileNav" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars text-secondary fs-5"></i>
            </button>

            <!-- Brand Logo -->
            <a class="navbar-brand header-brand me-2 me-lg-4" href="{{ route('home') }}">
                <i class="fa-solid fa-code me-2 text-primary"></i>
                JudgeArena
            </a>

            <!-- Desktop Navigation & Search -->
            <div class="collapse navbar-collapse" id="desktopNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1 gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="{{ route('platforms.index') }}">Platforms</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="{{ route('contests.index') }}">Contests</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="{{ route('problems.index') }}">Problems</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="{{ route('rankings.index') }}">Rankings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="{{ route('community.index') }}">Community</a>
                    </li>
                </ul>

                <!-- Global Search Input -->
                <div class="header-search me-3 d-none d-xl-flex">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Search users, contests, problems..." />
                    <kbd class="search-kbd-hint">⌘K</kbd>
                </div>
            </div>

            <!-- Right Utility Actions (Mobile Search Trigger, Theme Toggle, Notifications Dropdown, User Avatar) -->
            <div class="d-flex align-items-center gap-1 gap-sm-2">
                <!-- Mobile Search Icon Button -->
                <button class="header-icon-btn d-xl-none" type="button" data-bs-toggle="collapse"
                    data-bs-target="#mobileSearchCollapse" aria-expanded="false" title="Search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>

                <button class="header-icon-btn" id="theme-toggle-btn" title="Toggle theme">
                    <i class="fa-solid fa-sun" id="theme-toggle-icon"></i>
                </button>

                <!-- Notification Dropdown -->
                <div class="dropdown">
                    <button class="header-icon-btn position-relative" type="button" data-bs-toggle="dropdown"
                        data-bs-auto-close="outside" aria-expanded="false" title="Notifications">
                        <i class="fa-regular fa-bell"></i>
                        <span class="dot-badge"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown-menu shadow-lg">
                        <!-- Header -->
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

                        <!-- Items List -->
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
                                            <b>tourist</b> mentioned you
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

                        <!-- Footer -->
                        <div class="notification-footer">
                            <a href="#" class="text-decoration-none">View All Notifications
                                <i class="fa-solid fa-angle-right ms-1"></i></a>
                        </div>
                    </div>
                </div>

                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <a class="header-avatar-btn border-0 bg-transparent p-0 ms-1 d-block" href="#"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false" title="User Profile">
                        <img src="{{ asset('web') }}/img/khairul-islam-emon.jpg" alt="Khairul Islam Emon avatar" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-end profile-dropdown-menu shadow-lg">
                        <!-- Header info -->
                        <div class="profile-dropdown-header">
                            <div class="d-flex align-items-center gap-2">
                                <div class="profile-dropdown-user-name mb-0">
                                    Khairul Islam Emon
                                </div>
                                <i class="fa-solid fa-circle-check verified-badge" style="font-size: 0.85rem"></i>
                            </div>
                            <div class="profile-dropdown-user-handle">
                                @tourist
                            </div>
                            <div class="mt-2">
                                <span class="badge-expert">Candidate Master (1964)</span>
                            </div>
                        </div>

                        <!-- Menu items -->
                        <div class="py-1">
                            <a class="dropdown-item" href="profile.html">
                                <i class="fa-regular fa-user"></i> My
                                Profile
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                Submissions
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fa-regular fa-bookmark"></i>
                                Bookmarks
                            </a>
                            <hr class="dropdown-divider" />
                            <a class="dropdown-item" href="settings.html">
                                <i class="fa-solid fa-gear me-1"></i>
                                Account Settings
                            </a>
                            <hr class="dropdown-divider" />
                            <a class="dropdown-item text-danger" href="#">
                                <i class="fa-solid fa-arrow-right-from-bracket text-danger"></i>
                                Sign Out
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Log In & Register Buttons -->
                <div class="d-none d-lg-block">
                    <a href="{{ route('login') }}"
                        class="btn btn-sm btn-outline-secondary fw-semibold rounded-3 px-3 py-1-5 ms-1">
                        Log In
                    </a>
                    <a href="{{ route('register') }}"
                        class="btn btn-sm btn-primary fw-semibold rounded-3 px-3 py-1-5 shadow-sm">
                        Register
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Collapsible Search Bar -->
    <div class="collapse d-xl-none mobile-search-bar shadow-sm" id="mobileSearchCollapse">
        <div class="header-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Search users, contests, problems..." />
        </div>
    </div>
</header>

<!-- Mobile Offcanvas Menu -->
<aside class="offcanvas offcanvas-start mobile-nav-drawer" tabindex="-1" id="mobileNav"
    aria-labelledby="mobileNavLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title header-brand mb-0" id="mobileNavLabel">
            <a href="{{ route('home') }}" class="text-decoration-none">
                <i class="fa-solid fa-code me-2"></i>
                JudgeArena
            </a>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column justify-content-between">
        <ul class="navbar-nav gap-2">
            <li class="nav-item">
                <a class="nav-link py-2" href="{{ route('platforms.index') }}">Platforms</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2" href="{{ route('contests.index') }}">Contests</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2" href="{{ route('problems.index') }}">Problems</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2" href="{{ route('rankings.index') }}">Rankings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2" href="{{ route('community.index') }}">Community</a>
            </li>
        </ul>
        <div class="mt-4 pt-3 border-top d-flex gap-2">
            <a href="{{ route('login') }}" class="btn btn-outline-secondary fw-semibold w-100">Log In</a>
            <a href="{{ route('register') }}" class="btn btn-primary fw-semibold w-100">Register</a>
        </div>
    </div>
</aside>
