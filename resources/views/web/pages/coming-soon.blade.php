@extends('web.layouts.app')

@section('title', $title . ' | Coming Soon - JudgeArena')
@section('description', $title . ' is coming soon on JudgeArena. Stay tuned for the next big update.')
@section('robots', 'noindex,nofollow')

@push('styles')
	<style>
		.coming-soon-hero {
			min-height: calc(100vh - 140px);
			display: flex;
			align-items: center;
			position: relative;
			overflow: hidden;
		}

		.coming-soon-hero::after {
			content: '';
			position: absolute;
			inset: 0;
			background: radial-gradient(circle at 80% 10%, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0) 55%);
			pointer-events: none;
		}

		.coming-soon-card {
			position: relative;
			z-index: 2;
			background: rgba(255, 255, 255, 0.12);
			border: 1px solid rgba(255, 255, 255, 0.2);
			backdrop-filter: blur(14px);
			border-radius: 24px;
			padding: 2rem;
		}

		.coming-soon-badge {
			display: inline-flex;
			align-items: center;
			gap: .4rem;
			background: rgba(255, 255, 255, 0.16);
			color: #fff;
			border: 1px solid rgba(255, 255, 255, 0.25);
			border-radius: 999px;
			padding: .45rem .9rem;
			font-weight: 600;
			font-size: .9rem;
			margin-bottom: 1.2rem;
		}

		.coming-soon-title {
			color: #fff;
			font-weight: 800;
			line-height: 1.15;
			font-size: clamp(2rem, 4vw, 3.4rem);
			margin-bottom: 1rem;
		}

		.coming-soon-text {
			color: rgba(255, 255, 255, 0.92);
			font-size: 1.08rem;
			max-width: 720px;
		}

		.coming-soon-preview {
			position: relative;
			z-index: 2;
		}

		.preview-panel {
			background: rgba(255, 255, 255, 0.12);
			border: 1px solid rgba(255, 255, 255, 0.2);
			border-radius: 20px;
			padding: 1.25rem;
			color: #fff;
		}

		.preview-row {
			background: rgba(255, 255, 255, 0.1);
			border: 1px solid rgba(255, 255, 255, 0.15);
			border-radius: 12px;
			padding: .75rem .9rem;
		}

		.coming-soon-strip {
			background: #f8f9fa;
			padding: 72px 0;
		}

		.coming-feature {
			border-left: 4px solid transparent;
			border-image: var(--primary-gradient) 1;
		}

		.release-status-text {
			color: rgba(255, 255, 255, 0.85);
			font-weight: 600;
		}

		.release-status-text .dots::after {
			content: '';
			display: inline-block;
			width: 1.2em;
			text-align: left;
			animation: dotPulse 1.4s infinite steps(4, end);
		}

		@keyframes dotPulse {
			0% {
				content: '';
			}

			25% {
				content: '.';
			}

			50% {
				content: '..';
			}

			75%,
			100% {
				content: '...';
			}
		}
	</style>
@endpush

@section('content')
	@php
		$statusMap = [
			'Problems' => [
				'progress' => 40,
				'tag' => 'Finalizing',
				'message' => 'Final review in progress',
			],
			'Contests' => [
				'progress' => 60,
				'tag' => 'Integrating',
				'message' => 'Core modules are under active development',
			],
			'Community' => [
				'progress' => 80,
				'tag' => 'Polishing',
				'message' => 'Refining engagement features and experience',
			],
		];

		$releaseState = $statusMap[$title] ?? [
			'progress' => 62,
			'tag' => 'In Progress',
			'message' => 'Feature development is moving forward',
		];
	@endphp

	<section class="hero-section coming-soon-hero">
		<div class="container position-relative" style="z-index: 2;">
			<div class="row g-4 align-items-center">
				<div class="col-lg-7">
					<div class="coming-soon-card">
						<span class="coming-soon-badge">
							<i class="bi bi-stars"></i> New Module In Progress
						</span>

						<h1 class="coming-soon-title">{{ $title }} is launching soon.</h1>

						<p class="coming-soon-text mb-4">
							We are building a better {{ strtolower($title) }} experience for competitive programmers with
							cleaner insights, stronger performance tracking, and smoother workflows across the platform.
						</p>

						<div class="d-flex flex-wrap gap-3">
							<a href="{{ route('home') }}" class="btn btn-light btn-lg" style="border-radius: 50px; padding: 12px 30px; font-weight: 700;">
								<i class="bi bi-house-door"></i> Back to Home
							</a>
							<a href="{{ route('contact.us') }}" class="btn btn-outline-light-custom btn-lg">
								<i class="bi bi-chat-dots"></i> Contact Us
							</a>
						</div>
					</div>
				</div>

				<div class="col-lg-5">
					<div class="coming-soon-preview floating-animation">
						<div class="preview-panel mb-3">
							<div class="d-flex justify-content-between align-items-center mb-2">
								<strong>Release Status</strong>
								<span class="badge bg-warning text-dark">{{ $releaseState['tag'] }}</span>
							</div>
							<div class="progress" style="height: 10px; border-radius: 999px; background: rgba(255, 255, 255, 0.25);">
								<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $releaseState['progress'] }}%; background: var(--secondary-gradient);"></div>
							</div>
							<small class="d-block mt-2 release-status-text">
								{{ $releaseState['message'] }} <span class="dots"></span>
							</small>
						</div>

						<div class="d-grid gap-2">
							<div class="preview-row d-flex justify-content-between align-items-center">
								<span><i class="bi bi-check2-circle"></i> Core architecture</span>
								<span class="fw-bold">Done</span>
							</div>
							<div class="preview-row d-flex justify-content-between align-items-center">
								<span><i class="bi bi-hourglass-split" style="animation: spin 2s linear infinite;"></i> Data integration</span>
								<span class="fw-bold">In Progress</span>
							</div>
							<div class="preview-row d-flex justify-content-between align-items-center">
								<span><i class="bi bi-clock-history"></i>
                                     UI refinement</span>
								<span class="fw-bold">Queued</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="coming-soon-strip">
		<div class="container">
			<div class="text-center mb-5">
				<h2 class="display-6 fw-bold">What to expect</h2>
				<p class="text-muted mb-0">Focused improvements designed to make your journey faster and clearer.</p>
			</div>

			<div class="row g-4">
				<div class="col-md-4">
					<div class="feature-box coming-feature h-100">
						<div class="feature-icon" style="background: var(--primary-gradient);">
							<i class="bi bi-speedometer2"></i>
						</div>
						<h5 class="fw-bold">Faster Performance</h5>
						<p class="text-muted mb-0">A more responsive experience with optimized loading and smoother interactions.</p>
					</div>
				</div>

				<div class="col-md-4">
					<div class="feature-box coming-feature h-100">
						<div class="feature-icon" style="background: var(--secondary-gradient);">
							<i class="bi bi-bar-chart-line"></i>
						</div>
						<h5 class="fw-bold">Richer Insights</h5>
						<p class="text-muted mb-0">Actionable analytics to help you understand progress and improve consistently.</p>
					</div>
				</div>

				<div class="col-md-4">
					<div class="feature-box coming-feature h-100">
						<div class="feature-icon" style="background: var(--dark-gradient);">
							<i class="bi bi-people"></i>
						</div>
						<h5 class="fw-bold">Community Focus</h5>
						<p class="text-muted mb-0">More engaging ways to connect, learn, and stay motivated with fellow coders.</p>
					</div>
				</div>
			</div>
		</div>
	</section>
@endsection
