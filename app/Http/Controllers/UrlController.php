<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Url;
use GuzzleHttp\Client;

class UrlController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $originalUrl = $request->url;

        // Check if the URL already exists in the database
        $existingUrl = Url::where('original_url', $originalUrl)->first();

        if ($existingUrl) {
            return response()->json([
                'short_url' => $existingUrl->short_url,
            ]);
        }

        // Check if the URL is safe using Google Safe Browsing API
        $isSafe = $this->checkSafeUrl($originalUrl);

        if (!$isSafe) {
            return response()->json([
                'error' => 'The URL is not safe.',
            ], 400);
        }

        $shortUrl = $this->generateShortUrl();

        // Save the URL in the database
        $url = Url::create([
            'original_url' => $originalUrl,
            'short_url' => $shortUrl,
        ]);

        return response()->json([
            'short_url' => $url->short_url,
        ]);
    }

    /**
     * Redirect to the original URL.
     *
     * @param  string  $shortUrl
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect($shortUrl)
    {
        $url = Url::where('short_url', $shortUrl)->firstOrFail();

        // Redirect and other logic
        return redirect()->away($url->original_url);
    }

    private function checkSafeUrl($url)
    {
        $client = new Client();
        $apiKey = env("SAFE_BROWSING_API_KEY", ""); 

        $response = $client->post('https://safebrowsing.googleapis.com/v4/threatMatches:find?key='.$apiKey, [
            'json' => [
                'client' => [
                    'clientId' => 'safe-check',
                    'clientVersion' => '1.0.0',
                ],
                'threatInfo' => [
                    'threatTypes' => ['MALWARE', 'SOCIAL_ENGINEERING'],
                    'platformTypes' => ['ANY_PLATFORM'],
                    'threatEntryTypes' => ['URL'],
                    'threatEntries' => [
                        ['url' => $url],
                    ],
                ],
            ],
        ]);

        //dd($response->getBody());

        $body = json_decode($response->getBody(), true);
        //dd($apiKey);

        return empty($body['matches']);
    }

    /**
     * Generate a unique short URL.
     *
     * @return string
     */
    private function generateShortUrl()
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $shortUrl = '';

        for ($i = 0; $i < 6; $i++) {
            $shortUrl .= $characters[rand(0, strlen($characters) - 1)];
        }

        // Check if URL already exists in the database
        $existingUrl = Url::where('short_url', $shortUrl)->first();

        if ($existingUrl) {
            return $this->generateShortUrl();
        }

        return $shortUrl;
    }
}
