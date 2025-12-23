<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ShiprocketService
{
    protected $baseUrl;
    protected $email;
    protected $password;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = 'https://apiv2.shiprocket.in/v1/external';
        $this->email = env('SHIPROCKET_EMAIL');
        $this->password = env('SHIPROCKET_PASSWORD');
    }

    public function login()
    {
        $response = Http::post("{$this->baseUrl}/auth/login", [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        if ($response->successful()) {
            $this->token = $response->json()['token'];
            return $this->token;
        }

        return null;
    }

    public function createOrder($data)
    {
        if (!$this->token) {
            $this->login();
        }

        $response = Http::withToken($this->token)->post("{$this->baseUrl}/orders/create/adhoc", $data);

        return $response->json();
    }

    public function trackOrder($awb)
    {
        if (!$this->token) {
            $this->login();
        }

        $response = Http::withToken($this->token)->get("{$this->baseUrl}/courier/track/awb/{$awb}");

        return $response->json();
    }
}
