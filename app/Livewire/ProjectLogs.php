<?php

namespace App\Livewire;

use App\Models\Project;
use App\Services\KubernetesService;
use Livewire\Component;

class ProjectLogs extends Component
{
    public ?Project $record = null;

    public string $logs = 'Initializing logs...';

    public ?string $selectedPod = null;

    public function mount(): void
    {
        $this->loadPods();
    }

    public function loadPods(): void
    {
        $pods = $this->record?->pods;

        if ($pods?->isNotEmpty()) {
            $this->selectedPod = data_get($pods->first(), 'metadata.name');
            $this->refreshLogs();
        }
    }

    public function refreshLogs(): void
    {
        if (! $this->selectedPod) {
            return;
        }

        try {
            $this->logs = app(KubernetesService::class)->getPodLogs(
                $this->record->getNamespaceKey(),
                $this->selectedPod
            );
            $this->dispatch('log-updated');
        } catch (\Exception $e) {
            $this->logs = 'Failed to load logs: '.$e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.project-logs');
    }
}
