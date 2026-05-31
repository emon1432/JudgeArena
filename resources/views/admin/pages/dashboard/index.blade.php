@extends('admin.layouts.app')
@section('title', __('Dashboard'))
@section('content')
	<div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">{{ __('Total Platforms') }}</div>
                    <div class="fs-3 fw-bold">{{ $totalPlatforms }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">{{ __('Total Contests') }}</div>
                    <div class="fs-3 fw-bold">{{ $totalContests }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">{{ __('Total Problems') }}</div>
                    <div class="fs-3 fw-bold">{{ $totalProblems }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">{{ __('Total Users') }}</div>
                    <div class="fs-3 fw-bold">{{ $totalUsers }}</div>
                </div>
            </div>
        </div>
		<div class="col-md-6 col-xl-3">
			<div class="card h-100">
				<div class="card-body">
					<div class="text-muted small mb-1">{{ __('Errors Today') }}</div>
					<div class="fs-3 fw-bold">{{ $errorsToday }}</div>
				</div>
			</div>
		</div>
		<div class="col-md-6 col-xl-3">
			<div class="card h-100">
				<div class="card-body">
					<div class="text-muted small mb-1">{{ __('Warnings Today') }}</div>
					<div class="fs-3 fw-bold">{{ $warningsToday }}</div>
				</div>
			</div>
		</div>
		<div class="col-md-6 col-xl-3">
			<div class="card h-100">
				<div class="card-body">
					<div class="text-muted small mb-1">{{ __('Last Sync Errors') }}</div>
					<div class="fs-3 fw-bold">{{ $lastSyncErrors->count() }}</div>
				</div>
			</div>
		</div>
		<div class="col-md-6 col-xl-3">
			<div class="card h-100">
				<div class="card-body">
					<div class="text-muted small mb-1">{{ __('Recent Critical Logs') }}</div>
					<div class="fs-3 fw-bold">{{ $recentCriticalLogs->count() }}</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row g-4">
		<div class="col-lg-6">
			<div class="card h-100">
				<div class="card-header border-bottom">
					<h5 class="card-title mb-0">{{ __('Last Sync Errors') }}</h5>
				</div>
				<div class="card-body">
					@forelse ($lastSyncErrors as $log)
						<div class="my-3 pb-3 border-bottom">
							<div class="d-flex justify-content-between gap-3 mb-1">
								<span class="badge bg-label-danger text-uppercase">{{ $log->level }}</span>
								<small class="text-muted">{{ $log->created_at?->format('d M, Y h:i A') }}</small>
							</div>
							<div class="fw-semibold">{{ $log->message }}</div>
							<div class="text-muted small">{{ $log->source }}</div>
						</div>
					@empty
						<p class="text-muted my-5">{{ __('No sync errors logged yet.') }}</p>
					@endforelse
				</div>
			</div>
		</div>
		<div class="col-lg-6">
			<div class="card h-100">
				<div class="card-header border-bottom">
					<h5 class="card-title mb-0">{{ __('Recent Critical Logs') }}</h5>
				</div>
				<div class="card-body">
					@forelse ($recentCriticalLogs as $log)
						<div class="my-3 pb-3 border-bottom">
							<div class="d-flex justify-content-between gap-3 mb-1">
								<span class="badge bg-label-dark text-uppercase">{{ $log->level }}</span>
								<small class="text-muted">{{ $log->created_at?->format('d M, Y h:i A') }}</small>
							</div>
							<div class="fw-semibold">{{ $log->message }}</div>
							<div class="text-muted small">{{ $log->source }}</div>
						</div>
					@empty
						<p class="text-muted my-5">{{ __('No critical logs recorded yet.') }}</p>
					@endforelse
				</div>
			</div>
		</div>
	</div>
@endsection
