@props([
    'record',
    'compact' => false,
])

@php
    use Carbon\Carbon;
    
    $nextDue = $record->next_due_date instanceof Carbon ? $record->next_due_date : Carbon::parse($record->next_due_date);
    $today = Carbon::today();
    $daysUntilDue = $today->diffInDays($nextDue, false);
    
    // Determinar estado y colores
    if ($daysUntilDue < 0) {
        $status = 'overdue';
        $statusText = 'Vencido';
        $badgeClass = 'bg-red-100 text-red-700 border-red-200';
        $iconClass = 'text-red-600';
        $icon = '⚠️';
    } elseif ($daysUntilDue <= 7) {
        $status = 'warning';
        $statusText = 'Próximo';
        $badgeClass = 'bg-amber-100 text-amber-700 border-amber-200';
        $iconClass = 'text-amber-600';
        $icon = '⏰';
    } elseif ($daysUntilDue <= 30) {
        $status = 'upcoming';
        $statusText = 'Programado';
        $badgeClass = 'bg-blue-100 text-blue-700 border-blue-200';
        $iconClass = 'text-blue-600';
        $icon = '📅';
    } else {
        $status = 'ok';
        $statusText = 'Al día';
        $badgeClass = 'bg-emerald-100 text-emerald-700 border-emerald-200';
        $iconClass = 'text-emerald-600';
        $icon = '✓';
    }
    
    $typeLabels = [
        'vaccine' => 'Vacuna',
        'deworming' => 'Desparasitación',
        'checkup' => 'Revisión',
        'grooming' => 'Estética',
        'other' => 'Otro',
    ];
    
    $typeLabel = $typeLabels[$record->type] ?? ucfirst($record->type);
@endphp

@if($compact)
    <span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium $badgeClass"]) }}>
        <span>{{ $icon }}</span>
        <span>{{ $typeLabel }}</span>
        @if($status === 'overdue')
            <span class="font-semibold">(Vencido)</span>
        @elseif($status === 'warning')
            <span>({{ abs($daysUntilDue) }}d)</span>
        @endif
    </span>
@else
    <div {{ $attributes->merge(['class' => "rounded-lg border $badgeClass p-3"]) }}>
        <div class="mb-2 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">{{ $icon }}</span>
                <h4 class="font-semibold">{{ $typeLabel }}</h4>
            </div>
            <span class="rounded-full bg-white/60 px-2 py-0.5 text-xs font-medium">
                {{ $statusText }}
            </span>
        </div>

        <div class="space-y-1 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-600">Última aplicación:</span>
                <span class="font-medium">{{ Carbon::parse($record->last_date)->format('d/m/Y') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-600">Próxima fecha:</span>
                <span class="font-medium">{{ $nextDue->format('d/m/Y') }}</span>
            </div>
            @if($daysUntilDue >= 0)
                <div class="flex justify-between">
                    <span class="text-slate-600">Faltan:</span>
                    <span class="font-semibold">{{ $daysUntilDue }} {{ $daysUntilDue === 1 ? 'día' : 'días' }}</span>
                </div>
            @else
                <div class="flex justify-between">
                    <span class="text-slate-600">Atrasado:</span>
                    <span class="font-semibold">{{ abs($daysUntilDue) }} {{ abs($daysUntilDue) === 1 ? 'día' : 'días' }}</span>
                </div>
            @endif
        </div>

        @if($record->notes)
            <p class="mt-2 border-t border-current/10 pt-2 text-xs text-slate-600">{{ $record->notes }}</p>
        @endif
    </div>
@endif
