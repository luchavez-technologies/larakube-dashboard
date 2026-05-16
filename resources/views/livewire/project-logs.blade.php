<div 
    wire:poll.5s="refreshLogs" 
    class="larakube-terminal-container"
    x-data="{ 
        scrollToBottom() { 
            const el = this.$refs.logContainer;
            el.scrollTop = el.scrollHeight;
        } 
    }"
    x-init="scrollToBottom()"
    x-on:log-updated.window="scrollToBottom()"
>
    <div class="terminal-header">
        <div class="flex items-center">
            @if(! $hideSelector)
                <select wire:model.live="selectedPod" class="pod-selector">
                    @foreach($record?->pods ?? [] as $pod)
                        <option value="{{ $pod['metadata']['name'] }}">{{ $pod['metadata']['name'] }}</option>
                    @endforeach
                </select>
            @endif
        </div>
        <div class="flex items-center gap-6">
            <button wire:click="refreshLogs" class="text-gray-500 hover:text-gray-300 transition-colors flex items-center justify-center">
                <x-heroicon-m-arrow-path class="h-4 w-4" wire:loading.class="animate-spin" />
            </button>
        </div>
    </div>
    
    <div x-ref="logContainer" class="terminal-body larakube-logs-body">
        <pre class="output-text">{{ $logs }}</pre>
    </div>
</div>
