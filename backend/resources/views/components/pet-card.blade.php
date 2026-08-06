@props([
    'pet',
    'showOwner' => false,
    'clickable' => true,
    'compact' => false,
])

@php
    $species = $pet->species ?? 'dog';
    $speciesEmoji = match($species) {
        'dog' => '🐕',
        'cat' => '🐈',
        'bird' => '🐦',
        'rabbit' => '🐇',
        'hamster' => '🐹',
        'fish' => '🐠',
        default => '🐾',
    };
    
    $sizeColor = match($pet->size ?? 'medium') {
        'small' => 'bg-sky-100 text-sky-700',
        'medium' => 'bg-emerald-100 text-emerald-700',
        'large' => 'bg-amber-100 text-amber-700',
        default => 'bg-slate-100 text-slate-700',
    };
    
    $energyColor = match($pet->energy_level ?? 'medium') {
        'low' => 'bg-slate-100 text-slate-600',
        'medium' => 'bg-blue-100 text-blue-600',
        'high' => 'bg-orange-100 text-orange-600',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp

<div {{ $attributes->merge(['class' => ($clickable ? 'cursor-pointer transition hover:shadow-lg ' : '') . ($compact ? 'rounded-lg' : 'rounded-xl') . ' overflow-hidden border border-slate-200 bg-white shadow-sm']) }}>
    @if(!$compact)
        <div class="relative h-48 overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200">
            @if(isset($pet->photos) && $pet->photos->isNotEmpty())
                <img src="{{ asset('storage/' . $pet->photos->first()->path) }}" alt="{{ $pet->name }}" class="h-full w-full object-cover">
            @else
                <div class="flex h-full items-center justify-center text-6xl">
                    {{ $speciesEmoji }}
                </div>
            @endif
            
            @if($pet->available_for_walks ?? false)
                <div class="absolute right-3 top-3 rounded-full bg-emerald-500 px-3 py-1 text-xs font-semibold text-white shadow-md">
                    Disponible para paseos
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $compact ? 'p-3' : 'p-4' }}">
        <div class="mb-2 flex items-start justify-between gap-2">
            <div class="flex-1">
                <h3 class="brand-font text-lg font-semibold text-slate-900">{{ $pet->name }}</h3>
                <p class="text-sm text-slate-600">{{ $pet->breed ?? 'Sin raza definida' }}</p>
            </div>
            @if($compact)
                <span class="text-2xl">{{ $speciesEmoji }}</span>
            @endif
        </div>

        <div class="flex flex-wrap gap-2">
            <span class="{{ $sizeColor }} rounded-full px-2 py-1 text-xs font-medium">
                {{ ucfirst($pet->size ?? 'medium') }}
            </span>
            <span class="{{ $energyColor }} rounded-full px-2 py-1 text-xs font-medium">
                Energía: {{ ucfirst($pet->energy_level ?? 'media') }}
            </span>
            @if(isset($pet->age))
                <span class="rounded-full bg-purple-100 px-2 py-1 text-xs font-medium text-purple-700">
                    {{ $pet->age }} {{ $pet->age === 1 ? 'año' : 'años' }}
                </span>
            @endif
        </div>

        @if($pet->personality ?? false)
            <p class="mt-3 line-clamp-2 text-sm text-slate-600">{{ $pet->personality }}</p>
        @endif

        @if($showOwner && isset($pet->user))
            <div class="mt-3 flex items-center gap-2 border-t border-slate-100 pt-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-700">
                    {{ substr($pet->user->name, 0, 2) }}
                </div>
                <span class="text-sm text-slate-600">{{ $pet->user->name }}</span>
            </div>
        @endif
    </div>
</div>
