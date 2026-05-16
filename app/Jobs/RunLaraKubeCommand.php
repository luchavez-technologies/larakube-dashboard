<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;

class RunLaraKubeCommand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $command,
        public ?string $projectPath = null,
        public ?string $projectUuid = null,
    ) {}

    public function handle(): void
    {
        $binary = '/usr/local/bin/larakube';

        $path = $this->projectPath;
        if ($path) {
            /**
             * 🌉 THE WORKSPACE HANDSHAKE
             *
             * We swap the host's workspace path for the container's mount path.
             */
            $hostWorkspace = env('LARAKUBE_HOST_WORKSPACE');

            if ($hostWorkspace && str_starts_with($path, $hostWorkspace)) {
                $path = str_replace($hostWorkspace, '/var/lib/larakube-workspace', $path);
            }
        }

        $process = Process::path($path ?? '/var/lib/larakube-workspace')
            ->timeout(600)
            ->start("{$binary} {$this->command} --no-interaction");

        // Here we would ideally broadcast the output via WebSockets
        // For now, we wait for completion
        $result = $process->wait();

        if ($result->successful()) {
            // Update project status or log success
        } else {
            // Log failure
        }
    }
}
