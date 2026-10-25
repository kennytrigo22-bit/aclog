@extends('layouts.apprenant')
@section('page-title', $lecon->titre)

@php
    $progressService = app(\App\Services\FormationProgressService::class);
    $ctx = $progressService->contexte(auth()->user());
    $done = $ctx['done'];
    $allLecons = $formation->modules->flatMap->lecons;
    $currentIndex = $allLecons->search(fn($l) => $l->id === $lecon->id);
    $prevLecon = $currentIndex > 0 ? $allLecons[$currentIndex - 1] : null;
    $nextLecon = $currentIndex < $allLecons->count() - 1 ? $allLecons[$currentIndex + 1] : null;
    $totalLecons = $allLecons->count();
    $completedCount = $allLecons->pluck('id')->filter(fn ($i) => isset($done[$i]))->count();

    if (!function_exists('youtubeId')) {
        function youtubeId($url) {
            if (!$url) return null;
            if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) return $m[1];
            return null;
        }
    }
    $ytId = youtubeId($lecon->video_url);
@endphp

@section('content')
<div x-data="coursApp()" class="min-h-screen">
    {{-- Top bar progression --}}
    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm p-4 mb-6">
        <div class="flex items-center justify-between mb-2">
            <a href="{{ route('apprenant.cours.index') }}" class="text-sm text-gray-500 hover:text-primary-700 dark:hover:text-accent-400 transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                {{ $formation->titre }}
            </a>
            <span class="text-sm font-bold text-primary-700 dark:text-white">{{ $completedCount }}/{{ $totalLecons }} leçons</span>
        </div>
        <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-accent-500 to-yellow-400 rounded-full transition-all duration-500" style="width: {{ $progressionFormation }}%"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Contenu principal --}}
        <div class="lg:col-span-8 space-y-5">
            {{-- Video player YouTube ou natif --}}
            @if($ytId)
            <div class="bg-black rounded-xl overflow-hidden shadow-lg">
                <div class="relative w-full" style="padding-bottom: 56.25%">
                    <iframe src="https://www.youtube.com/embed/{{ $ytId }}?rel=0&modestbranding=1" class="absolute inset-0 w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
            @elseif($lecon->video_url)
            <div class="bg-black rounded-xl overflow-hidden shadow-lg aspect-video" x-data="videoPlayer()" x-init="init()">
                <video id="lesson-video" class="w-full h-full" controls>
                    <source src="{{ $lecon->video_url }}" type="video/mp4">
                </video>
            </div>
            @endif

            {{-- Titre et meta --}}
            <div class="bg-white dark:bg-surface-dark rounded-xl p-6 shadow-sm">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-xs font-bold text-accent-500 uppercase tracking-wider mb-1">{{ $lecon->module->titre }}</p>
                        <h1 class="font-heading text-2xl font-bold text-primary-700 dark:text-white">{{ $lecon->titre }}</h1>
                    </div>
                    @if($lecon->duree_minutes)
                    <span class="px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-600 dark:text-gray-400 flex items-center gap-1 shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $lecon->duree_minutes }} min
                    </span>
                    @endif
                </div>

                {{-- Contenu texte --}}
                @if($lecon->contenu_texte)
                <div class="contenu-cours text-gray-700 dark:text-gray-200">
                    {!! str_contains($lecon->contenu_texte, "</") ? \App\Support\HtmlSanitizer::clean($lecon->contenu_texte) : nl2br(e($lecon->contenu_texte)) !!}
                </div>
                @else
                <p class="text-gray-400 text-sm italic">Aucun contenu texte pour cette leçon.</p>
                @endif
            </div>

            {{-- Actions navigation --}}
            <div class="bg-white dark:bg-surface-dark rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        @if($prevLecon)
                        <a href="{{ route('apprenant.cours.lecon', [$inscription, $prevLecon]) }}" class="flex items-center gap-2 text-sm text-gray-500 hover:text-primary-700 dark:hover:text-accent-400 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span class="hidden sm:inline">{{ Str::limit($prevLecon->titre, 30) }}</span>
                            <span class="sm:hidden">Précédent</span>
                        </a>
                        @endif
                    </div>

                    <button @click="markComplete()" id="btn-complete" class="px-6 py-2.5 rounded-xl font-bold text-sm transition flex items-center gap-2" :class="completed ? 'bg-emerald-100 dark:bg-emerald-900/20 text-emerald-600' : 'bg-accent-500 text-primary-700 hover:bg-accent-400'">
                        <svg x-show="completed" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span x-text="completed ? 'Terminée' : 'Marquer terminée'"></span>
                    </button>

                    <div>
                        @if($nextLecon)
                        <a href="{{ route('apprenant.cours.lecon', [$inscription, $nextLecon]) }}" class="flex items-center gap-2 text-sm font-semibold text-accent-500 hover:text-accent-400 transition">
                            <span class="hidden sm:inline">{{ Str::limit($nextLecon->titre, 30) }}</span>
                            <span class="sm:hidden">Suivant</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar plan du cours --}}
        <div class="lg:col-span-4">
            <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm sticky top-20 overflow-hidden">
                <div class="bg-primary-700 px-5 py-4">
                    <h3 class="font-heading font-bold text-white text-sm">Plan du cours</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="flex-1 h-1.5 bg-white/20 rounded-full">
                            <div class="h-full bg-accent-500 rounded-full" style="width: {{ $progressionFormation }}%"></div>
                        </div>
                        <span class="text-xs text-white/70 font-semibold">{{ $progressionFormation }}%</span>
                    </div>
                </div>
                <div class="max-h-[65vh] overflow-y-auto divide-y dark:divide-gray-800">
                    @foreach($formation->modules as $module)
                    <div x-data="{ open: {{ $module->lecons->contains('id', $lecon->id) ? 'true' : 'false' }} }">
                        <button @click="open = !open" class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                            <span class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase">{{ $module->titre }}</span>
                            <svg class="w-3.5 h-3.5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="pb-2">
                            @foreach($module->lecons as $l)
                            @php
                                $isActive = $l->id === $lecon->id;
                                $prog = isset($done[$l->id]);
                            @endphp
                            <a href="{{ route('apprenant.cours.lecon', [$inscription, $l]) }}" class="flex items-center gap-2.5 py-2 px-4 mx-2 rounded-lg text-xs transition {{ $isActive ? 'bg-accent-500/10 text-accent-600 dark:text-accent-400 font-bold' : ($prog ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800') }}">
                                @if($isActive)
                                <span class="w-5 h-5 rounded-full bg-accent-500 flex items-center justify-center shrink-0"><svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg></span>
                                @elseif($prog)
                                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                @else
                                <span class="w-5 h-5 rounded-full border-2 border-gray-300 dark:border-gray-600 shrink-0"></span>
                                @endif
                                <span class="truncate">{{ $l->titre }}</span>
                            </a>
                            @endforeach
                            @if($module->quiz)
                            <a href="{{ route('apprenant.quiz', $module->quiz) }}" class="flex items-center gap-2.5 py-2 px-4 mx-2 rounded-lg text-xs text-purple-500 font-semibold hover:bg-purple-50 dark:hover:bg-purple-900/10 transition">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Quiz du module
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @if($formation->ressources->isNotEmpty())
                    <div class="px-4 py-3 border-t dark:border-gray-800">
                        @foreach($formation->ressources as $ressource)
                        <a href="{{ route('apprenant.ressources.show', $ressource) }}" class="flex items-center justify-center gap-2 w-full py-2 rounded-lg bg-accent-500 text-primary-800 text-xs font-bold text-center hover:bg-accent-400 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            {{ \Illuminate\Support\Str::limit($ressource->titre, 34) }}
                        </a>
                        @endforeach
                    </div>
                    @endif
                    @if($formation->examenTest || $formation->examenFinal)
                    @php
                        $st = $progressService->status(auth()->user(), $formation, $ctx);
                        $coursTermine = $st['unlocked'];
                        $lockReason = $st['lock_reason'];
                    @endphp
                    <div class="px-4 py-3 border-t dark:border-gray-800 space-y-2">
                        @if($formation->examenTest)
                            @if($coursTermine)
                            <a href="{{ route('apprenant.examen', $formation->examenTest) }}" class="flex items-center justify-center gap-2 w-full py-2 rounded-lg bg-blue-600 text-white text-xs font-bold text-center hover:bg-blue-500 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                Examen Test
                            </a>
                            @else
                            <div class="flex items-center justify-center gap-2 w-full py-2 rounded-lg bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 text-xs font-bold text-center cursor-not-allowed">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Examen Test
                            </div>
                            <p class="text-[10px] text-gray-400 text-center">{{ $lockReason }}</p>
                            @endif
                        @endif
                        @if($formation->examenFinal)
                            @if($coursTermine)
                            <a href="{{ route('apprenant.examen', $formation->examenFinal) }}" class="flex items-center justify-center gap-2 w-full py-2 rounded-lg bg-primary-700 text-white text-xs font-bold text-center hover:bg-primary-500 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                Examen Final
                            </a>
                            @else
                            <div class="flex items-center justify-center gap-2 w-full py-2 rounded-lg bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 text-xs font-bold text-center cursor-not-allowed">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Examen Final
                            </div>
                            <p class="text-[10px] text-gray-400 text-center">{{ $lockReason }}</p>
                            @endif
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function coursApp() {
    return {
        completed: {{ ($progression && $progression->is_completed) ? 'true' : 'false' }},
        markComplete() {
            if (this.completed) return;
            fetch('{{ route("apprenant.progression.save") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ lecon_id: {{ $lecon->id }}, is_completed: true, position_video: 0 })
            }).then(r => r.json()).then(() => { this.completed = true; });
        }
    }
}

function videoPlayer() {
    return {
        init() {
            const video = document.getElementById('lesson-video');
            if (!video) return;
            @if($progression && $progression->position_video > 0)
            video.currentTime = {{ $progression->position_video }};
            @endif
            setInterval(() => {
                if (!video.paused) {
                    fetch('{{ route("apprenant.progression.save") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ lecon_id: {{ $lecon->id }}, position_video: Math.floor(video.currentTime) })
                    });
                }
            }, 30000);
        }
    }
}
</script>
@endsection
