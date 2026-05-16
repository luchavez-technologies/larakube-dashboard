<?php

namespace App\Mcp\Tools;

use App\Contracts\LaraKubeConsoleTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;

#[Name('search-documentation')]
#[Title('Search Documentation')]
#[Description('Search the official LaraKube documentation for answers about Kubernetes orchestration and blueprints.')]
#[IsOpenWorld]
class SearchDocumentationTool extends LaraKubeConsoleTool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $query = $request->get('query');
        $mcpUrl = 'https://L42693MTAB.algolia.net/mcp/1/ccdl9WLFT-mLi8OqSkmx8A/mcp';

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json, text/event-stream',
            ])->post($mcpUrl, [
                'jsonrpc' => '2.0',
                'method' => 'tools/call',
                'params' => [
                    'name' => 'algolia_search_index_LaraKube Documentation',
                    'arguments' => [
                        'query' => $query,
                        'userIntent' => 'Searching documentation via LaraKube Console',
                        'originalQuery' => $query,
                        'sessionId' => (string) Str::uuid(),
                    ],
                ],
                'id' => 1,
            ]);

            $raw = $response->body();
            if (! str_contains($raw, 'data: ')) {
                return Response::error('Unexpected response format from documentation server.');
            }

            $jsonStr = Str::after($raw, 'data: ');
            $data = json_decode($jsonStr, true);
            $algoliaResult = json_decode($data['result']['content'][0]['text'] ?? '{}', true);
            $hits = $algoliaResult['hits'] ?? [];

            if (empty($hits)) {
                return Response::text("No documentation matches found for '{$query}'.");
            }

            $results = "### Documentation Search Results for '{$query}':\n\n";
            foreach ($hits as $hit) {
                $title = $hit['hierarchy']['lvl1'] ?? $hit['hierarchy']['lvl0'] ?? 'Untitled Section';
                $url = $hit['url'] ?? '#';
                $snippet = strip_tags(str_replace(['<span class="algolia-docsearch-suggestion--highlight">', '</span>'], '', $hit['_snippetResult']['content']['value'] ?? $hit['content'] ?? 'No preview.'));
                $results .= "#### [{$title}]({$url})\n{$snippet}\n\n";
            }

            return Response::text($results);
        } catch (\Exception $e) {
            return Response::error('Search failed: '.$e->getMessage());
        }
    }

    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('The natural language query or keyword to search for.')
                ->required(),
        ];
    }
}
