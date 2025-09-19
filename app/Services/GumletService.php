<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;
use Exception;

class GumletService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $collectionId;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.gumlet.base_url'), '/');
        $this->apiKey = config('services.gumlet.api_key');
        $this->collectionId = config('services.gumlet.collection_id');
    }

    /**
     * Envia uma requisição HTTP para a API da Gumlet.
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @return array
     * @throws Exception
     */
    protected function request(string $method, string $endpoint, array $data = []): array
    {
        if (empty($this->apiKey)) {
            throw new Exception("Gumlet API Key not configured in the .env file.");
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->{$method}("{$this->baseUrl}{$endpoint}", $data);

            $response->throw();

            return $response->json();
        } catch (Throwable $e) {
            Log::error('Gumlet API error', [
                'endpoint' => $endpoint,
                'method' => $method,
                'params' => $data,
                'exception' => $e->getMessage(),
                'response_status' => isset($response) ? $response->status() : 'N/A',
                'response_body' => isset($response) ? $response->body() : 'N/A',
            ]);

            throw new Exception("Failed to communicate with Gumlet API: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Lista os assets da collection.
     *
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function listAssets(int $page = 1, int $limit = 20): array
    {
        return $this->request('get', "/video/assets/list/{$this->collectionId}", [
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    /**
     * Busca por todas as playlists da collection
     *
     * @return array
     */
    public function getAllPlaylist(): array
    {
        return $this->request('get', "/video/playlist", [
            "collection_id" => $this->collectionId
        ]);
    }

    /**
     * Busca um assets de uma playlist especifica
     *
     * @param string $playlistId
     * @return array
     */
    public function getPlaylistAssets(string $playlistId): array
    {
        return $this->request('get', "/video/playlist/{$playlistId}/assets");
    }

    /**
     * Busca um asset específico.
     *
     * @param string $assetId
     * @return array
     */
    public function getAsset(string $assetId): array
    {
        return $this->request('get', "/video/assets/{$assetId}");
    }
}
