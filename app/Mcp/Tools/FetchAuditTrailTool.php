<?php

namespace App\Mcp\Tools;

use App\Contracts\LaraKubeConsoleTool;
use App\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;
use Spatie\Activitylog\Models\Activity;

#[Name('fetch-audit-trail')]
#[Title('Fetch Audit Trail')]
#[Description('Queries the historical activity logs for a specific project or the entire fleet.')]
#[IsOpenWorld]
class FetchAuditTrailTool extends LaraKubeConsoleTool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $projectName = $request->get('project');
        $limit = $request->get('limit', 10);
        $query = Activity::latest();

        if ($projectName) {
            $project = $this->resolveProject($projectName);
            if (! $project) {
                return Response::error("Error: Project '{$projectName}' not found.");
            }
            $query->where('subject_id', $project->id)->where('subject_type', Project::class);
        }

        $logs = $query->limit($limit)->get();

        if ($logs->isEmpty()) {
            return Response::text('No activity logs found.');
        }

        $report = ['### LaraKube Audit Trail'];
        foreach ($logs as $log) {
            $report[] = "- [{$log->created_at}] **{$log->event}**: {$log->description}";
            if (! empty($log->properties->toArray())) {
                $report[] = '  - Details: '.json_encode($log->properties);
            }
        }

        return Response::text(implode("\n", $report));
    }

    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project' => $schema->string()
                ->description('Optional: Filter by project name or UUID.'),
            'limit' => $schema->number()
                ->description('Maximum number of log entries to return.')
                ->default(10),
        ];
    }
}
