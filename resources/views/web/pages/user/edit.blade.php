@extends('web.layouts.app')
@section('content')
    <main class="container px-3 px-md-4 py-4 max-w-7xl">
        <!-- Top Breadcrumb & Share Row -->
        <x-breadcrumb title="Edit Profile" :breadcrumbs="[
            'User' => route('user.show', $username ?? (auth()->user()->username ?? 'admin')),
            'Edit Profile' => null,
        ]">
            <button class="btn-share-profile" data-bs-toggle="modal" data-bs-target="#shareProfileModal">
                <i class="fa-solid fa-arrow-up-from-bracket"></i> Share Profile
            </button>
        </x-breadcrumb>

        <!-- Settings Main Layout Row -->
        <div class="row g-4 mb-5">
            <!-- Left Navigation Sidebar Menu (Matching User Reference Image) -->
            <div class="col-lg-3 col-md-4">
                <div class="card panel border-0 p-3 position-sticky" style="top: 90px; border-radius: 16px">
                    <div
                        class="text-muted font-monospace extra-small uppercase fw-semibold px-3 mb-2 d-flex align-items-center gap-1.5">
                        <i class="fa-solid fa-gear text-primary me-2"></i>
                        Settings
                    </div>

                    <!-- Nav Tabs List -->
                    <div class="nav flex-column nav-pills settings-sidebar-nav" id="settings-tabs" role="tablist"
                        aria-orientation="vertical">
                        <button class="nav-link settings-nav-link active text-start" id="tab-profile-btn"
                            data-bs-toggle="pill" data-bs-target="#tab-profile" type="button" role="tab">
                            <i class="fa-regular fa-user"></i> Profile
                            Information
                        </button>
                        <button class="nav-link settings-nav-link text-start" id="tab-platforms-btn" data-bs-toggle="pill"
                            data-bs-target="#tab-platforms" type="button" role="tab">
                            <i class="fa-solid fa-network-wired"></i>
                            Connected Platforms
                        </button>
                        <button class="nav-link settings-nav-link text-start" id="tab-social-btn" data-bs-toggle="pill"
                            data-bs-target="#tab-social" type="button" role="tab">
                            <i class="fa-solid fa-share-nodes"></i> Social
                            Links
                        </button>
                        <button class="nav-link settings-nav-link text-start" id="tab-security-btn" data-bs-toggle="pill"
                            data-bs-target="#tab-security" type="button" role="tab">
                            <i class="fa-solid fa-shield-halved"></i>
                            Security
                        </button>
                        <button class="nav-link settings-nav-link text-start" id="tab-notifications-btn"
                            data-bs-toggle="pill" data-bs-target="#tab-notifications" type="button" role="tab">
                            <i class="fa-regular fa-bell"></i> Notifications
                        </button>

                        <hr class="my-2 border-secondary-subtle" />

                        <a href="#" class="settings-nav-link text-danger text-start">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Main Settings Tab Content Pane -->
            <div class="col-lg-9 col-md-8">
                <div class="tab-content" id="settings-tab-content">
                    <!-- TAB 1: Profile Information (Exact Layout from Reference Image) -->
                    <div class="tab-pane fade show active" id="tab-profile" role="tabpanel">
                        <div class="card panel border-0 p-4" style="border-radius: 16px">
                            <!-- Tab Header Title -->
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <i class="fa-regular fa-user text-primary fs-5"></i>
                                <h2 class="h5 fw-bold text-primary-emphasis mb-0">
                                    Profile Information
                                </h2>
                            </div>

                            <!-- Alert Toast Feedback -->
                            <div id="settings-save-alert"
                                class="alert alert-success d-none alert-dismissible fade show extra-small rounded-3"
                                role="alert">
                                <i class="fa-solid fa-circle-check me-2"></i>
                                Your profile information has been updated
                                successfully!
                                <button type="button" class="btn-close extra-small" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>

                            <form id="profile-settings-form">
                                <!-- Avatar Card Section (Matching Reference Image) -->
                                <div class="p-4 bg-body-tertiary rounded-4 border mb-4">
                                    <div class="row align-items-center g-3">
                                        <div class="col-auto">
                                            <div class="avatar-upload-wrapper">
                                                <img src="{{ asset('web') }}/img/khairul-islam-emon.jpg"
                                                    alt="Profile avatar" id="avatar-preview-img" />
                                                <label for="avatar-file-input" class="avatar-upload-badge"
                                                    title="Edit Avatar">
                                                    <i class="fa-solid fa-pencil"
                                                        style="
                                                                font-size: 0.75rem;
                                                            "></i>
                                                </label>
                                                <input type="file" id="avatar-file-input" accept="image/*" class="d-none"
                                                    onchange="
                                                            previewAvatar(event)
                                                        " />
                                            </div>
                                        </div>
                                        <div class="col">
                                            <h6 class="fw-bold mb-1 text-primary-emphasis">
                                                Profile Avatar
                                            </h6>
                                            <p class="text-muted extra-small mb-2">
                                                Upload your official
                                                programmer avatar (JPG, PNG
                                                or WebP). Maximum 5MB.
                                            </p>
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <label for="avatar-file-input"
                                                    class="btn btn-sm btn-primary fw-semibold px-3 py-1-5">
                                                    <i class="fa-solid fa-upload me-1"></i>
                                                    Upload Photo
                                                </label>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-secondary px-3 py-1-5"
                                                    onclick="resetAvatar()">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Fields Grid (Matching Reference Image) -->
                                <div class="row g-3">
                                    <!-- Full Name -->
                                    <div class="col-md-12">
                                        <label for="full_name"
                                            class="form-label small fw-semibold text-primary-emphasis mb-1">Full Name
                                            <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-body-secondary"><i
                                                    class="fa-regular fa-user text-muted"></i></span>
                                            <input type="text" class="form-control" id="full_name" value="Top Users"
                                                required />
                                        </div>
                                    </div>

                                    <!-- Username -->
                                    <div class="col-md-4">
                                        <label for="username_handle"
                                            class="form-label small fw-semibold text-primary-emphasis mb-1">Username
                                            <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-body-secondary font-monospace">@</span>
                                            <input type="text" class="form-control font-monospace"
                                                id="username_handle" value="topusers" required />
                                        </div>
                                    </div>

                                    <!-- Email Address -->
                                    <div class="col-md-8">
                                        <label for="email_address"
                                            class="form-label small fw-semibold text-primary-emphasis mb-1">Email Address
                                            <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-body-secondary"><i
                                                    class="fa-regular fa-envelope text-muted"></i></span>
                                            <input type="email" class="form-control" id="email_address"
                                                value="topusers@judgearena.com" required />
                                        </div>
                                    </div>

                                    <!-- Date of Birth -->
                                    <div class="col-md-4">
                                        <label for="dob_date"
                                            class="form-label small fw-semibold text-primary-emphasis mb-1">Date of
                                            Birth</label>
                                        <input type="date" class="form-control form-control-sm" id="dob_date" />
                                    </div>

                                    <!-- Gender Select -->
                                    <div class="col-md-4">
                                        <label for="gender_select"
                                            class="form-label small fw-semibold text-primary-emphasis mb-1">Gender</label>
                                        <select class="form-select form-select-sm" id="gender_select">
                                            <option value="male" selected>
                                                Male
                                            </option>
                                            <option value="female">
                                                Female
                                            </option>
                                            <option value="other">
                                                Other
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Country Select -->
                                    <div class="col-md-4">
                                        <label for="country_select"
                                            class="form-label small fw-semibold text-primary-emphasis mb-1">Country</label>
                                        <select class="form-select form-select-sm" id="country_select">
                                            <option value="BD" selected>
                                                Bangladesh
                                            </option>
                                            <option value="US">
                                                United States
                                            </option>
                                            <option value="IN">
                                                India
                                            </option>
                                            <option value="CA">
                                                Canada
                                            </option>
                                            <option value="UK">
                                                United Kingdom
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Institute Select + "If Institute not listed" Modal Button -->
                                    <div class="col-md-12">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <label for="institute_name"
                                                class="form-label small fw-semibold text-primary-emphasis mb-0">Institute</label>
                                            <button type="button"
                                                class="btn btn-xs btn-outline-primary d-inline-flex align-items-center gap-1 extra-small rounded-2"
                                                data-bs-toggle="modal" data-bs-target="#addInstituteModal">
                                                <i class="fa-solid fa-plus-circle"></i>
                                                If Institute not listed
                                            </button>
                                        </div>
                                        <select class="form-select form-select-sm" id="institute_name">
                                            <option value="IST" selected>
                                                Institute of Science &
                                                Technology (IST)
                                            </option>
                                            <option value="BUET">
                                                Bangladesh University of
                                                Engineering and Technology
                                                (BUET)
                                            </option>
                                            <option value="DU">
                                                University of Dhaka
                                            </option>
                                            <option value="NSU">
                                                North South University (NSU)
                                            </option>
                                            <option value="MIT">
                                                Massachusetts Institute of
                                                Technology (MIT)
                                            </option>
                                            <option value="OTHER">
                                                Other / Custom Institute
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Form Action Buttons (Matching Reference Image) -->
                                <div class="d-flex align-items-center gap-2 mt-4 pt-3 border-top">
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3 fw-semibold">
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary px-4 fw-semibold shadow-sm">
                                        <i class="fa-solid fa-check me-1"></i>
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- TAB 2: Connected Platforms -->
                    <div class="tab-pane fade" id="tab-platforms" role="tabpanel">
                        <!-- Card 1: Add / Connect New Platform Form -->
                        <div class="card panel border-0 p-4 mb-4" style="border-radius: 16px">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-network-wired text-primary fs-5"></i>
                                    <div>
                                        <h2 class="h5 fw-bold text-primary-emphasis mb-0">
                                            Connected Platforms
                                        </h2>
                                        <p class="text-muted extra-small mb-0">
                                            Add your online judge username /
                                            handle to synchronize ratings
                                            and problem stats.
                                        </p>
                                    </div>
                                </div>
                                <span
                                    class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1-5 extra-small fw-semibold">100+
                                    Platforms Supported</span>
                            </div>

                            <form id="add-connected-platform-form" onsubmit="handleAddPlatform(event)">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="select_platform_dropdown"
                                            class="form-label small fw-semibold text-primary-emphasis mb-1">Platform
                                            <span class="text-danger">*</span></label>
                                        <select class="form-select" id="select_platform_dropdown" required>
                                            <option value="" selected disabled>
                                                Select Platform (100+
                                                Supported)
                                            </option>
                                            <option value="cf">
                                                Codeforces (CF)
                                            </option>
                                            <option value="lc">
                                                LeetCode (LC)
                                            </option>
                                            <option value="hr">
                                                Hackerrank (HR)
                                            </option>
                                            <option value="cc">
                                                Codechef (CC)
                                            </option>
                                            <option value="ac">
                                                AtCoder (AC)
                                            </option>
                                            <option value="kg">
                                                Kaggle (KG)
                                            </option>
                                            <option value="spoj">
                                                SPOJ
                                            </option>
                                            <option value="cses">
                                                CSES Problemset
                                            </option>
                                            <option value="kattis">
                                                Kattis
                                            </option>
                                            <option value="loj">
                                                LightOJ
                                            </option>
                                            <option value="he">
                                                HackerEarth
                                            </option>
                                            <option value="vj">
                                                VJudge
                                            </option>
                                            <option value="topcoder">
                                                TopCoder
                                            </option>
                                            <option value="dmoj">
                                                DMOJ
                                            </option>
                                            <option value="projecteuler">
                                                Project Euler
                                            </option>
                                            <option value="custom">
                                                Other / Custom Online Judge
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="platform_handle"
                                            class="form-label small fw-semibold text-primary-emphasis mb-1">Platform Handle
                                            <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text font-monospace text-muted">@</span>
                                            <input type="text" class="form-control" id="platform_handle"
                                                placeholder="Enter Platform Handle / Username / Id" required />
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between gap-2 mt-4 pt-3 border-top">
                                    <span class="text-muted extra-small"><i
                                            class="fa-solid fa-circle-info me-1 text-primary"></i>
                                        Live handle verification auto-syncs
                                        stats every 6 hours.</span>
                                    <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                                        <i class="fa-solid fa-plus me-1"></i>
                                        Save Connections
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Card 2: List of Connected Platforms Table -->
                        <div class="card panel border-0 p-4" style="border-radius: 16px">
                            <div
                                class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                                <div>
                                    <h3 class="h6 fw-bold text-primary-emphasis mb-1 d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-list-check text-success"></i>
                                        List of Connected Platforms
                                        <span
                                            class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 extra-small fw-semibold ms-1"
                                            id="connected-count-badge">6 Connected</span>
                                    </h3>
                                    <p class="text-muted extra-small mb-0">
                                        Your currently linked competitive
                                        programming accounts and sync
                                        statuses.
                                    </p>
                                </div>

                                <!-- Quick Filter / Search input -->
                                <div class="position-relative" style="min-width: 240px">
                                    <i
                                        class="fa-solid fa-magnifying-glass text-muted position-absolute start-0 top-50 translate-middle-y ms-3 extra-small"></i>
                                    <input type="text" class="form-control form-control-sm ps-5 pe-3 rounded-3"
                                        placeholder="Filter connected..." onkeyup="filterConnectedList(this)" />
                                </div>
                            </div>

                            <!-- Table of Connected Platforms -->
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="connected-platforms-table">
                                    <thead class="table-light extra-small text-uppercase font-monospace text-muted">
                                        <tr>
                                            <th scope="col" style="min-width: 180px">
                                                Platform
                                            </th>
                                            <th scope="col" style="min-width: 160px">
                                                Connected Handle
                                            </th>
                                            <th scope="col" style="min-width: 140px">
                                                Current Rating / Stats
                                            </th>
                                            <th scope="col" style="min-width: 130px">
                                                Sync Status
                                            </th>
                                            <th scope="col" class="text-end" style="min-width: 120px">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="small" id="connected-platforms-tbody">
                                        <!-- Row 1: Codeforces -->
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="bg-primary-subtle text-primary rounded-2 d-flex align-items-center justify-content-center"
                                                        style="
                                                                width: 34px;
                                                                height: 34px;
                                                            ">
                                                        <i class="fa-solid fa-code fs-6"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-primary-emphasis">
                                                            Codeforces (CF)
                                                        </div>
                                                        <div class="extra-small text-muted">
                                                            codeforces.com
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="https://codeforces.com/profile/khairul_emon" target="_blank"
                                                    class="font-monospace fw-semibold text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                                    @khairul_emon
                                                    <i
                                                        class="fa-solid fa-arrow-up-right-from-square extra-small opacity-75"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-purple-subtle text-purple extra-small fw-semibold">Candidate
                                                    Master
                                                    (1964)</span>
                                            </td>
                                            <td>
                                                <span
                                                    class="d-inline-flex align-items-center gap-1.5 extra-small text-success fw-medium">
                                                    <span class="spinner-grow spinner-grow-sm text-success"
                                                        style="
                                                                width: 6px;
                                                                height: 6px;
                                                            "
                                                        role="status"></span>
                                                    Synced 2m ago
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex align-items-center gap-1">
                                                    <button class="btn btn-icon btn-xs btn-outline-primary rounded-2"
                                                        title="Edit / Update Handle"
                                                        onclick="
                                                                editPlatformRow(
                                                                    this,
                                                                    'Codeforces',
                                                                    'khairul_emon',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                                        title="Re-sync Handle"
                                                        onclick="
                                                                syncSinglePlatform(
                                                                    'Codeforces',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-rotate"></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-xs btn-outline-danger rounded-2"
                                                        title="Remove Connection"
                                                        onclick="
                                                                removePlatformRow(
                                                                    this,
                                                                    'Codeforces',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Row 2: LeetCode -->
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="bg-warning-subtle text-warning rounded-2 d-flex align-items-center justify-content-center"
                                                        style="
                                                                width: 34px;
                                                                height: 34px;
                                                            ">
                                                        <i class="fa-solid fa-terminal fs-6"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-primary-emphasis">
                                                            LeetCode (LC)
                                                        </div>
                                                        <div class="extra-small text-muted">
                                                            leetcode.com
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="https://leetcode.com/u/khairul_emon" target="_blank"
                                                    class="font-monospace fw-semibold text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                                    @khairul_emon
                                                    <i
                                                        class="fa-solid fa-arrow-up-right-from-square extra-small opacity-75"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-warning-subtle text-warning extra-small fw-semibold">Guardian
                                                    (2140)</span>
                                            </td>
                                            <td>
                                                <span
                                                    class="d-inline-flex align-items-center gap-1.5 extra-small text-success fw-medium">
                                                    <span class="spinner-grow spinner-grow-sm text-success"
                                                        style="
                                                                width: 6px;
                                                                height: 6px;
                                                            "
                                                        role="status"></span>
                                                    Synced 15m ago
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex align-items-center gap-1">
                                                    <button class="btn btn-icon btn-xs btn-outline-primary rounded-2"
                                                        title="Edit / Update Handle"
                                                        onclick="
                                                                editPlatformRow(
                                                                    this,
                                                                    'LeetCode',
                                                                    'khairul_emon',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                                        title="Re-sync Handle"
                                                        onclick="
                                                                syncSinglePlatform(
                                                                    'LeetCode',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-rotate"></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-xs btn-outline-danger rounded-2"
                                                        title="Remove Connection"
                                                        onclick="
                                                                removePlatformRow(
                                                                    this,
                                                                    'LeetCode',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Row 3: AtCoder -->
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="bg-info-subtle text-info rounded-2 d-flex align-items-center justify-content-center"
                                                        style="
                                                                width: 34px;
                                                                height: 34px;
                                                            ">
                                                        <i class="fa-solid fa-bolt fs-6"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-primary-emphasis">
                                                            AtCoder (AC)
                                                        </div>
                                                        <div class="extra-small text-muted">
                                                            atcoder.jp
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="https://atcoder.jp/users/khairul_emon" target="_blank"
                                                    class="font-monospace fw-semibold text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                                    @khairul_emon
                                                    <i
                                                        class="fa-solid fa-arrow-up-right-from-square extra-small opacity-75"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info extra-small fw-semibold">3-Dan
                                                    Cyan (1542)</span>
                                            </td>
                                            <td>
                                                <span
                                                    class="d-inline-flex align-items-center gap-1.5 extra-small text-success fw-medium">
                                                    <span class="spinner-grow spinner-grow-sm text-success"
                                                        style="
                                                                width: 6px;
                                                                height: 6px;
                                                            "
                                                        role="status"></span>
                                                    Synced 1h ago
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex align-items-center gap-1">
                                                    <button class="btn btn-icon btn-xs btn-outline-primary rounded-2"
                                                        title="Edit / Update Handle"
                                                        onclick="
                                                                editPlatformRow(
                                                                    this,
                                                                    'AtCoder',
                                                                    'khairul_emon',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                                        title="Re-sync Handle"
                                                        onclick="
                                                                syncSinglePlatform(
                                                                    'AtCoder',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-rotate"></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-xs btn-outline-danger rounded-2"
                                                        title="Remove Connection"
                                                        onclick="
                                                                removePlatformRow(
                                                                    this,
                                                                    'AtCoder',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Row 4: CodeChef -->
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="bg-purple-subtle text-purple rounded-2 d-flex align-items-center justify-content-center"
                                                        style="
                                                                width: 34px;
                                                                height: 34px;
                                                            ">
                                                        <i class="fa-solid fa-utensils fs-6"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-primary-emphasis">
                                                            CodeChef (CC)
                                                        </div>
                                                        <div class="extra-small text-muted">
                                                            codechef.com
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="https://codechef.com/users/khairul_emon" target="_blank"
                                                    class="font-monospace fw-semibold text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                                    @khairul_emon
                                                    <i
                                                        class="fa-solid fa-arrow-up-right-from-square extra-small opacity-75"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-purple-subtle text-purple extra-small fw-semibold">5★
                                                    Master (2045)</span>
                                            </td>
                                            <td>
                                                <span
                                                    class="d-inline-flex align-items-center gap-1.5 extra-small text-success fw-medium">
                                                    <span class="spinner-grow spinner-grow-sm text-success"
                                                        style="
                                                                width: 6px;
                                                                height: 6px;
                                                            "
                                                        role="status"></span>
                                                    Synced 2h ago
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex align-items-center gap-1">
                                                    <button class="btn btn-icon btn-xs btn-outline-primary rounded-2"
                                                        title="Edit / Update Handle"
                                                        onclick="
                                                                editPlatformRow(
                                                                    this,
                                                                    'CodeChef',
                                                                    'khairul_emon',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                                        title="Re-sync Handle"
                                                        onclick="
                                                                syncSinglePlatform(
                                                                    'CodeChef',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-rotate"></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-xs btn-outline-danger rounded-2"
                                                        title="Remove Connection"
                                                        onclick="
                                                                removePlatformRow(
                                                                    this,
                                                                    'CodeChef',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Row 5: HackerRank -->
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="bg-success-subtle text-success rounded-2 d-flex align-items-center justify-content-center"
                                                        style="
                                                                width: 34px;
                                                                height: 34px;
                                                            ">
                                                        <i class="fa-solid fa-h fs-6"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-primary-emphasis">
                                                            HackerRank (HR)
                                                        </div>
                                                        <div class="extra-small text-muted">
                                                            hackerrank.com
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="https://hackerrank.com/khairul_emon" target="_blank"
                                                    class="font-monospace fw-semibold text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                                    @khairul_emon
                                                    <i
                                                        class="fa-solid fa-arrow-up-right-from-square extra-small opacity-75"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-success-subtle text-success extra-small fw-semibold">6★
                                                    Problem
                                                    Solving</span>
                                            </td>
                                            <td>
                                                <span
                                                    class="d-inline-flex align-items-center gap-1.5 extra-small text-success fw-medium">
                                                    <span class="spinner-grow spinner-grow-sm text-success"
                                                        style="
                                                                width: 6px;
                                                                height: 6px;
                                                            "
                                                        role="status"></span>
                                                    Synced 5h ago
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex align-items-center gap-1">
                                                    <button class="btn btn-icon btn-xs btn-outline-primary rounded-2"
                                                        title="Edit / Update Handle"
                                                        onclick="
                                                                editPlatformRow(
                                                                    this,
                                                                    'HackerRank',
                                                                    'khairul_emon',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                                        title="Re-sync Handle"
                                                        onclick="
                                                                syncSinglePlatform(
                                                                    'HackerRank',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-rotate"></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-xs btn-outline-danger rounded-2"
                                                        title="Remove Connection"
                                                        onclick="
                                                                removePlatformRow(
                                                                    this,
                                                                    'HackerRank',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Row 6: Kaggle -->
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="bg-primary-subtle text-primary rounded-2 d-flex align-items-center justify-content-center"
                                                        style="
                                                                width: 34px;
                                                                height: 34px;
                                                            ">
                                                        <i class="fa-solid fa-k fs-6"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-primary-emphasis">
                                                            Kaggle (KG)
                                                        </div>
                                                        <div class="extra-small text-muted">
                                                            kaggle.com
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="https://kaggle.com/khairul_emon" target="_blank"
                                                    class="font-monospace fw-semibold text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                                    @khairul_emon
                                                    <i
                                                        class="fa-solid fa-arrow-up-right-from-square extra-small opacity-75"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-primary-subtle text-primary extra-small fw-semibold">Expert
                                                    (Competitions)</span>
                                            </td>
                                            <td>
                                                <span
                                                    class="d-inline-flex align-items-center gap-1.5 extra-small text-success fw-medium">
                                                    <span class="spinner-grow spinner-grow-sm text-success"
                                                        style="
                                                                width: 6px;
                                                                height: 6px;
                                                            "
                                                        role="status"></span>
                                                    Synced 1d ago
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex align-items-center gap-1">
                                                    <button class="btn btn-icon btn-xs btn-outline-primary rounded-2"
                                                        title="Edit / Update Handle"
                                                        onclick="
                                                                editPlatformRow(
                                                                    this,
                                                                    'Kaggle',
                                                                    'khairul_emon',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                                        title="Re-sync Handle"
                                                        onclick="
                                                                syncSinglePlatform(
                                                                    'Kaggle',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-rotate"></i>
                                                    </button>
                                                    <button class="btn btn-icon btn-xs btn-outline-danger rounded-2"
                                                        title="Remove Connection"
                                                        onclick="
                                                                removePlatformRow(
                                                                    this,
                                                                    'Kaggle',
                                                                )
                                                            ">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Social Links -->
                    <div class="tab-pane fade" id="tab-social" role="tabpanel">
                        <div class="card panel border-0 p-4" style="border-radius: 16px">
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <i class="fa-solid fa-share-nodes text-primary fs-5"></i>
                                <h2 class="h5 fw-bold text-primary-emphasis mb-0">
                                    Social Links
                                </h2>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-primary-emphasis mb-1">GitHub
                                        Username</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-body-secondary"><i
                                                class="fa-brands fa-github fs-6"></i></span>
                                        <input type="text" class="form-control" value="topusers" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-primary-emphasis mb-1">Twitter / X
                                        Handle</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-body-secondary"><i
                                                class="fa-brands fa-x-twitter fs-6"></i></span>
                                        <input type="text" class="form-control" value="@topusers" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-primary-emphasis mb-1">LinkedIn
                                        Profile</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-body-secondary"><i
                                                class="fa-brands fa-linkedin fs-6 text-primary"></i></span>
                                        <input type="text" class="form-control" value="topusers" />
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-sm btn-primary px-4 fw-semibold">
                                    Update Social Links
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: Security -->
                    <div class="tab-pane fade" id="tab-security" role="tabpanel">
                        <div class="card panel border-0 p-4" style="border-radius: 16px">
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <i class="fa-solid fa-key text-primary fs-5"></i>
                                <h2 class="h5 fw-bold text-primary-emphasis mb-0">
                                    Change Password
                                </h2>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold text-primary-emphasis mb-1">Current
                                        Password</label>
                                    <input type="password" class="form-control form-control-sm"
                                        placeholder="••••••••••••" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-primary-emphasis mb-1">New
                                        Password</label>
                                    <input type="password" class="form-control form-control-sm"
                                        placeholder="••••••••••••" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-primary-emphasis mb-1">Confirm New
                                        Password</label>
                                    <input type="password" class="form-control form-control-sm"
                                        placeholder="••••••••••••" />
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-sm btn-primary px-4 fw-semibold">
                                    Update Password
                                </button>
                            </div>
                        </div>
                        <div class="card panel border-0 p-4" style="border-radius: 16px">
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <i class="fa-solid fa-question text-primary fs-5"></i>
                                <h2 class="h5 fw-bold text-primary-emphasis mb-0">
                                    Forget Password
                                </h2>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold text-primary-emphasis mb-1">Email
                                        Address</label>
                                    <input type="email" class="form-control form-control-sm"
                                        placeholder="••••••••••••" />
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-sm btn-primary px-4 fw-semibold">
                                    Forgot Password
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 5: Notifications -->
                    <div class="tab-pane fade" id="tab-notifications" role="tabpanel">
                        <div class="card panel border-0 p-4" style="border-radius: 16px">
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <i class="fa-regular fa-bell text-primary fs-5"></i>
                                <h2 class="h5 fw-bold text-primary-emphasis mb-0">
                                    Notifications
                                </h2>
                            </div>

                            <div class="d-flex flex-column gap-3">
                                <div
                                    class="d-flex align-items-center justify-content-between p-3 bg-body-tertiary rounded-3 border">
                                    <div>
                                        <div class="fw-semibold small text-primary-emphasis">
                                            Upcoming Contest Reminders
                                        </div>
                                        <div class="text-muted extra-small">
                                            Email notifications before
                                            contests start.
                                        </div>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked />
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-sm btn-primary px-4 fw-semibold">
                                    Save Notifications
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>



    <!-- Share Profile Modal (Same as profile.html) -->
    <div class="modal fade" id="shareProfileModal" tabindex="-1" aria-labelledby="shareProfileModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background: var(--surface)">
                <!-- Modal Header -->
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold d-flex align-items-center gap-2 text-primary-emphasis"
                            id="shareProfileModalLabel">
                            <i class="fa-solid fa-share-nodes text-primary"></i>
                            Share CP Identity
                        </h5>
                        <p class="text-muted extra-small mb-0 mt-1">
                            Share Khairul Islam Emon's unified competitive
                            programming profile
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <!-- Direct Profile Link Field -->
                    <div class="mb-4">
                        <label class="form-label font-monospace extra-small text-muted uppercase fw-semibold mb-1">Direct
                            Profile Link</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body-secondary border-end-0 text-muted"><i
                                    class="fa-solid fa-link extra-small"></i></span>
                            <input type="text"
                                class="form-control font-monospace border-start-0 ps-0 text-primary-emphasis"
                                id="share-profile-url-input" value="https://judgearena.com/users/khairul_emon" readonly />
                            <button class="btn btn-primary fw-semibold px-3 d-inline-flex align-items-center gap-1"
                                id="btn-copy-profile-url" onclick="copyProfileUrl()">
                                <i class="fa-regular fa-copy" id="copy-btn-icon"></i>
                                <span id="copy-btn-text">Copy Link</span>
                            </button>
                        </div>
                        <div id="copy-success-toast" class="text-success extra-small mt-1-5 d-none">
                            <i class="fa-solid fa-circle-check me-1"></i>
                            Link copied to clipboard!
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-sm btn-secondary w-100 fw-semibold" data-bs-dismiss="modal">
                        Close Window
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: If Institute Not Listed -->
    <div class="modal fade" id="addInstituteModal" tabindex="-1" aria-labelledby="addInstituteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px">
                <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold text-primary-emphasis d-flex align-items-center gap-2"
                            id="addInstituteModalLabel">
                            <i class="fa-solid fa-building-columns text-primary"></i>
                            Request New Institute
                        </h5>
                        <p class="text-muted extra-small mb-0 mt-1">
                            Can't find your college or university? Request
                            to add it to JudgeArena.
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <form id="request-institute-form" onsubmit="submitInstituteRequest(event)">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-primary-emphasis mb-1">Full Institute Name
                                <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm"
                                placeholder="e.g. Institute of Science & Technology" required />
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-primary-emphasis mb-1">Short Acronym /
                                Code</label>
                            <input type="text" class="form-control form-control-sm"
                                placeholder="e.g. IST, BUET, MIT" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-primary-emphasis mb-1">Country
                                <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" required>
                                <option value="BD" selected>
                                    Bangladesh
                                </option>
                                <option value="US">United States</option>
                                <option value="IN">India</option>
                                <option value="OTHER">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-primary-emphasis mb-1">Official Institute
                                Website URL</label>
                            <input type="url" class="form-control form-control-sm"
                                placeholder="https://www.ist.edu.bd" />
                        </div>

                        <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-2">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold">
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Edit Connected Platform Handle -->
    <div class="modal fade" id="editPlatformModal" tabindex="-1" aria-labelledby="editPlatformModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background: var(--surface)">
                <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold text-primary-emphasis d-flex align-items-center gap-2"
                            id="editPlatformModalLabel">
                            <i class="fa-solid fa-pen-to-square text-primary"></i>
                            Edit Platform Handle
                        </h5>
                        <p class="text-muted extra-small mb-0 mt-1">
                            Update your linked account handle for
                            <span id="edit-modal-platform-title" class="fw-semibold text-primary-emphasis">Platform</span>
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <form id="edit-platform-form" onsubmit="saveEditedPlatform(event)">
                        <input type="hidden" id="edit_platform_name" />
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-primary-emphasis mb-1">Platform Name</label>
                            <input type="text" class="form-control bg-body-tertiary" id="edit_platform_display"
                                readonly />
                        </div>
                        <div class="mb-3">
                            <label for="edit_platform_handle_input"
                                class="form-label small fw-semibold text-primary-emphasis mb-1">User Handle / Username
                                <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text font-monospace text-muted">@</span>
                                <input type="text" class="form-control font-monospace" id="edit_platform_handle_input"
                                    required placeholder="e.g. khairul_emon" />
                            </div>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="edit_auto_sync_toggle" checked />
                            <label class="form-check-label small fw-medium text-primary-emphasis"
                                for="edit_auto_sync_toggle">Enable Automatic Background Sync</label>
                        </div>

                        <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary px-4 fw-semibold shadow-sm">
                                <i class="fa-solid fa-check me-1"></i>
                                Update Connection
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
