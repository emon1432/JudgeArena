<h4 class="mb-4">
    <i class="bi bi-list-stars"></i> Full Leaderboard
</h4>
<div class="card leaderboard-table-card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table leaderboard-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Rank</th>
                        <th>User</th>
                        <th>Institute</th>
                        <th class="text-end">Total Rating</th>
                        <th class="text-end">Total Solved</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="ps-4">
                                <span
                                    class="badge bg-primary leaderboard-rank-badge">#{{ $user->leaderboard_rank }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <a href="{{ route('user.profile.show', $user->username) }}"
                                        class="d-flex align-items-center gap-3 text-decoration-none text-dark">
                                        <img src="{{ $user->image && imageExists($user->image) ? imageShow($user->image) : $user->profile_photo_url }}"
                                            alt="{{ $user->name }}" class="rounded-circle" width="42"
                                            height="42">
                                    </a>
                                    <div>
                                        <div class="fw-semibold">
                                            <a href="{{ route('user.profile.show', $user->username) }}"
                                                class=" text-decoration-none text-dark">
                                                {{ $user->name }}
                                            </a>
                                            @if ($user->country)
                                                <span
                                                    title="{{ $user->country->name . ' (' . $user->country->code . ')' }}">
                                                    {{ $user->country->flag }}</span>
                                            @endif
                                        </div>
                                        <a href="{{ route('user.profile.show', $user->username) }}"
                                            class="small text-muted text-decoration-none">
                                            &#64;{{ $user->username }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($user->institute)
                                        <span class="">{{ $user->institute->name }}</span>
                                    @else
                                        <span class="small text-muted">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="leaderboard-score">{{ number_format((int) $user->total_rating) }}</span>
                            </td>
                            <td class="text-end">
                                <span class="leaderboard-score">{{ number_format((int) $user->total_solved) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No users matched your
                                filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($users->hasPages())
        <div
            class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="small text-muted">
                Showing
                {{ number_format($users->firstItem() ?? 0) }}-{{ number_format($users->lastItem() ?? 0) }}
                of {{ number_format($users->total()) }} users
            </div>
            <nav aria-label="Leaderboard pagination">
                <ul class="pagination mb-0">
                    <li class="page-item @if ($users->onFirstPage()) disabled @endif">
                        <a class="page-link" href="{{ $users->previousPageUrl() ?: '#' }}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    @foreach ($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                        <li class="page-item @if ($page === $users->currentPage()) active @endif">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach

                    <li class="page-item @if (!$users->hasMorePages()) disabled @endif">
                        <a class="page-link" href="{{ $users->nextPageUrl() ?: '#' }}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    @endif
</div>
