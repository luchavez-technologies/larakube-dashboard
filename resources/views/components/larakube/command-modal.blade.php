@props([
    'commands' => [],
    'path' => null,
])

<div class="space-y-6 py-2">
    @if($path)
        <div class="flex flex-col gap-2">
            <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;" class="text-gray-500 dark:text-gray-400">Target Directory</span>
            <div class="group relative flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50/50 p-2.5 dark:border-white/10 dark:bg-white/5">
                <code class="flex-1 font-mono text-xs text-gray-700 dark:text-gray-300 break-all select-all">{{ $path }}</code>
                <button 
                    type="button"
                    x-on:click="window.navigator.clipboard.writeText('{{ $path }}'); $tooltip('Path copied', { timeout: 1500 })"
                    style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                    class="ml-3 rounded-md text-gray-400 hover:bg-gray-200 dark:hover:bg-white/10 hover:text-primary-500 transition-all"
                    title="Copy path"
                >
                    <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
            <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;" class="text-gray-500 dark:text-gray-400">Terminal Commands</span>
            <button 
                type="button"
                x-on:click="window.navigator.clipboard.writeText('{{ implode("\n", $commands) }}'); $tooltip('Commands copied', { timeout: 1500 })"
                style="display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;"
                class="text-primary-600 dark:text-primary-400 hover:text-primary-500 transition-colors"
            >
                <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                </svg>
                <span>Copy All</span>
            </button>
        </div>
        
        <div class="larakube-terminal-container overflow-hidden rounded-xl border border-gray-800 bg-[#0a0a0a] shadow-xl">
            <div class="terminal-header flex items-center justify-between border-b border-gray-800 bg-[#171717]/50 px-4 py-2.5">
                <div class="flex items-center gap-2">
                    <div style="display: flex; gap: 6px;">
                        <div style="height: 10px; width: 10px; background: #ff5f56;" class="rounded-full"></div>
                        <div style="height: 10px; width: 10px; background: #ffbd2e;" class="rounded-full"></div>
                        <div style="height: 10px; width: 10px; background: #27c93f;" class="rounded-full"></div>
                    </div>
                    <span style="margin-left: 8px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em;" class="text-gray-500">LaraKube CLI</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span style="height: 6px; width: 6px; background: #22c55e;" class="rounded-full"></span>
                    <span style="font-size: 9px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.1em;" class="text-green-500/80">Ready</span>
                </div>
            </div>
            <div class="terminal-body !h-auto min-h-[80px] max-h-[300px] overflow-y-auto p-5 font-mono text-[13px] leading-relaxed">
                @foreach($commands as $command)
                    <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 4px;">
                        <span style="user-select: none; font-weight: 700; color: #22c55e;">$</span>
                        <span style="color: #e5e5e5; word-break: break-all;">{{ $command }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <div style="display: flex; align-items: flex-start; gap: 12px; padding: 16px;" class="rounded-xl bg-amber-500/5 border border-amber-500/10">
        <div style="flex-shrink: 0; padding-top: 2px;" class="text-amber-500">
            <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; gap: 4px;">
            <h4 style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;" class="text-amber-600 dark:text-amber-400">Observer Mode</h4>
            <p style="font-size: 12px; line-height: 1.5;" class="text-amber-700/80 dark:text-amber-300/60">
                Commands must be executed manually in your host terminal to maintain security and correct file ownership.
            </p>
        </div>
    </div>
</div>
