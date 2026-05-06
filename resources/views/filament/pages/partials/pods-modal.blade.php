<div class="flex flex-col gap-4">
    @if(empty($pods))
        <div class="text-center py-4 text-neutral-500">
            No pods found in this namespace.
        </div>
    @else
        <div class="overflow-hidden rounded-lg border border-neutral-200 dark:border-white/10">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-50 dark:bg-white/5 border-b border-neutral-200 dark:border-white/10 text-neutral-500 dark:text-neutral-400">
                    <tr>
                        <th class="px-4 py-2 font-medium">Name</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                        <th class="px-4 py-2 font-medium">Restarts</th>
                        <th class="px-4 py-2 font-medium">Age</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-white/10 bg-white dark:bg-white/5">
                    @foreach($pods as $pod)
                        @php
                            $status = $pod['status']['phase'] ?? 'Unknown';
                            $restarts = collect($pod['status']['containerStatuses'] ?? [])->sum('restartCount');
                            
                            $startTime = $pod['status']['startTime'] ?? null;
                            $age = $startTime ? \Carbon\Carbon::parse($startTime)->diffForHumans(null, true) : 'N/A';

                            $statusColor = match($status) {
                                'Running' => 'text-success-500 bg-success-50 dark:bg-success-500/10',
                                'Pending' => 'text-warning-500 bg-warning-50 dark:bg-warning-500/10',
                                'Failed', 'Error' => 'text-danger-500 bg-danger-50 dark:bg-danger-500/10',
                                default => 'text-neutral-500 bg-neutral-50 dark:bg-white/10',
                            };
                        @endphp
                        <tr class="hover:bg-neutral-50 dark:hover:bg-white/5">
                            <td class="px-4 py-2 font-medium text-neutral-900 dark:text-white">
                                {{ $pod['metadata']['name'] ?? 'Unnamed' }}
                            </td>
                            <td class="px-4 py-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColor }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-neutral-500">
                                {{ $restarts }}
                            </td>
                            <td class="px-4 py-2 text-neutral-500">
                                {{ $age }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
