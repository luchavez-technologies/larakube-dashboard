<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // 1. Construct the CLI command from form data
        $command = "new \"{$data['name']}\"";

        if ($data['blueprint'] !== 'laravel') {
            $command .= " --{$data['blueprint']}";
        }

        if ($data['serverVariation'] !== 'frankenphp') {
            $command .= " --{$data['serverVariation']}";
        }

        $command .= " --database={$data['database']}";

        if (! empty($data['features'])) {
            foreach ($data['features'] as $feature) {
                $command .= " --{$feature}";
            }
        }

        Notification::make()
            ->title('Apply Changes')
            ->body("Please run the following command in your terminal to create the project:\n\nlarakube {$command}")
            ->success()
            ->persistent()
            ->send();

        // 2. Create a placeholder record in the Console database
        return Project::create([
            'name' => $data['name'],
            'path' => $data['path'],
            'blueprint' => $data['blueprint'],
            'uuid' => (string) Str::uuid(),
            'config' => $data,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
