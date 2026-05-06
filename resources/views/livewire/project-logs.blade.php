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
        <div class="header-left">
            <div class="status-indicator"></div>
            <select wire:model.live="selectedPod" class="pod-selector">
                @foreach($record?->pods ?? [] as $pod)
                    <option value="{{ $pod['metadata']['name'] }}">{{ $pod['metadata']['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="header-right">
            <span class="title">Live Streaming</span>
            <button wire:click="refreshLogs" class="clear-btn">
                <x-heroicon-m-arrow-path class="h-3 w-3" wire:loading.class="animate-spin" />
            </button>
        </div>
    </div>
    
    <div x-ref="logContainer" class="terminal-body larakube-logs-body">
        <pre class="output-text">{{ $logs }}</pre>
    </div>
</div>
