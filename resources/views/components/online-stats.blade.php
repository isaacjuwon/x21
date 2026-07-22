@php
    $activeThreshold = now()->subMinutes(15)->getTimestamp();
    $onlineUsers = \Illuminate\Support\Facades\DB::table('sessions')
        ->whereNotNull('user_id')
        ->where('last_activity', '>=', $activeThreshold)
        ->count();
    $onlineGuests = \Illuminate\Support\Facades\DB::table('sessions')
        ->whereNull('user_id')
        ->where('last_activity', '>=', $activeThreshold)
        ->count();
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center space-x-4 text-sm font-medium']) }}>
    <div class="flex items-center space-x-1.5 text-zinc-600 dark:text-zinc-400">
        <flux:icon name="user-circle" class="w-4 h-4 text-green-500" />
        <span>{{ $onlineUsers }} {{ str('User')->plural($onlineUsers) }} Online</span>
    </div>
    <div class="flex items-center space-x-1.5 text-zinc-600 dark:text-zinc-400">
        <flux:icon name="user" class="w-4 h-4 text-zinc-400" />
        <span>{{ $onlineGuests }} {{ str('Guest')->plural($onlineGuests) }}</span>
    </div>
</div>
