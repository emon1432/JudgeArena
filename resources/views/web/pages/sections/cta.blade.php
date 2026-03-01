<div class="py-5" style="background: var(--primary-gradient);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 text-white mb-4 mb-lg-0">
                <h2 class="fw-bold mb-2">Start Tracking Your Progress Today</h2>
                <p class="mb-0 opacity-75">Join thousands of competitive programmers already using VertiCode to
                    monitor their journey and compete on global leaderboards.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                @auth
                    <a href="{{ route('user.profile.show', ['username' => auth()->user()->username]) }}"
                        class="btn btn-light btn-lg" style="border-radius: 50px; padding: 12px 40px; font-weight: 600;">
                        View Profile <i class="bi bi-arrow-right"></i>
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-light btn-lg"
                        style="border-radius: 50px; padding: 12px 40px; font-weight: 600;">
                        Sign Up Free <i class="bi bi-arrow-right"></i>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>
