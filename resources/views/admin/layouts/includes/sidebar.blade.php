<aside id="layout-menu" class="layout-menu menu-vertical menu">
    <div class="app-brand demo ">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <span class="text-primary">
                    <img src="{{ imageShow(settings('business_settings', 'logo')) }}"
                        alt="{{ settings('business_settings', 'company_name') }}" class="logo"
                        style="max-height: 30px; max-width: 100%;">
                </span>
            </span>
            <span
                class="app-brand-text demo menu-text fw-bold ms-3">{{ settings('business_settings', 'company_name') }}</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
            <i class="icon-base ti tabler-x d-block d-xl-none"></i>
        </a>
    </div>
    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1">
        <li class="menu-header small">
            <span class="menu-header-text">{{ __('Home') }}</span>
        </li>
        <li class="menu-item">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-smart-home"></i>
                <div>{{ __('Dashboard') }}</div>
            </a>
        </li>
        <li class="menu-header small">
            <span class="menu-header-text">{{ __('Platform Management') }}</span>
        </li>
        <li class="menu-item">
            <a href="{{ route('platforms.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-device-laptop"></i>
                <div>{{ __('Platforms') }}</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('all-problems.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-list-check"></i>
                <div>{{ __('Problems') }}</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('all-contests.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-trophy"></i>
                <div>{{ __('Contests') }}</div>
            </a>
        </li>
        <li class="menu-header small">
            <span class="menu-header-text">{{ __('User Management') }}</span>
        </li>
        <li class="menu-item">
            <a href="{{ route('users.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-users"></i>
                <div>{{ __('Users') }}</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('contact-messages.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-mail"></i>
                <div>{{ __('Contact Messages') }}</div>
            </a>
        </li>
        <li class="menu-header small">
            <span class="menu-header-text">{{ __('System') }}</span>
        </li>
        <li class="menu-item">
            <a href="{{ route('admins.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-shield-check"></i>
                <div>{{ __('Admins') }}</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('settings.index') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-settings"></i>
                <div>{{ __('Settings') }}</div>
            </a>
        </li>
    </ul>
</aside>
<div class="menu-mobile-toggler d-xl-none rounded-1">
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large text-bg-secondary p-2 rounded-1">
        <i class="ti tabler-menu icon-base"></i>
        <i class="ti tabler-chevron-right icon-base"></i>
    </a>
</div>
