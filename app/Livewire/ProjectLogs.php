<?php

namespace App\Livewire;

use App\Models\Project;
use App\Services\KubernetesService;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ProjectLogs extends Component
{
    public ?Project $record = null;

    public string $logs = 'Initializing logs...';

    protected array $queryString = [
        'selectedPod' => ['except' => ''],
    ];

    public ?string $selectedPod = null;

    public bool $hideSelector = false;

    public function mount(?string $selectedPod = null, bool $hideSelector = false): void
    {
        $this->hideSelector = $hideSelector;

        if ($selectedPod) {
            $this->selectedPod = $selectedPod;
        }

        $this->loadPods();
    }

    public function loadPods(): void
    {
        $pods = $this->record?->pods;

        if ($pods?->isNotEmpty()) {
            if (! $this->selectedPod) {
                $this->selectedPod = data_get($pods->first(), 'metadata.name');
            }
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
        } catch (Exception $e) {
            $this->logs = 'Failed to load logs: '.$e->getMessage();
        }
    }

    public function render(): View|Factory
    {
        return view('livewire.project-logs');
    }
}
