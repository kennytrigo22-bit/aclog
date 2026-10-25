@extends(auth()->user()->isGestionnaire() ? 'layouts.admin' : 'layouts.apprenant')
@section('page-title', 'Notifications')

@section('content')
<div class="max-w-3xl space-y-4">

    <div class="flex items-center justify-between">
        <h2 class="font-heading text-xl font-bold text-primary-700 dark:text-white">Mes notifications</h2>
        @if($notifications->where('is_read', false)->count() > 0)
        <form method="POST" action="{{ route('notifications.readAll') }}">
            @csrf
            <button type="submit" class="text-sm text-accent-500 hover:underline font-semibold">Tout marquer comme lu</button>
        </form>
        @endif
    </div>

    @forelse($notifications as $notif)
    <div class="bg-white dark:bg-surface-dark rounded-xl p-4 shadow-sm flex items-start gap-4 {{ !$notif->is_read ? 'border-l-4 border-accent-500' : '' }}">
        <div class="shrink-0 mt-0.5">
            @php
                $iconColors = [
                    'info' => 'text-blue-500',
                    'success' => 'text-emerald-500',
                    'warning' => 'text-orange-500',
                    'error' => 'text-red-500',
                ];
                $icons = [
                    'info' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    'success' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                    'warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                    'error' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                ];
            @endphp
            <svg class="w-5 h-5 {{ $iconColors[$notif->type] ?? 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$notif->type] ?? $icons['info'] }}"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-sm text-primary-700 dark:text-white">{{ $notif->titre }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">{{ $notif->message }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
        </div>
        @if(!$notif->is_read)
        <form method="POST" action="{{ route('notifications.read', $notif) }}" class="shrink-0">
            @csrf
            <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-accent-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Marquer comme lu">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </button>
        </form>
        @endif
    </div>
    @empty
    <div class="bg-white dark:bg-surface-dark rounded-xl p-8 shadow-sm text-center">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <p class="text-gray-400 font-medium">Aucune notification</p>
    </div>
    @endforelse

    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
@endsection
