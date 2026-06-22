<?php

// SignUpAccount should know HOW to make the API requests
final class SignUpAccount
{
    public function __construct(
        private string $baseUrl = 'https://api.mail.tm'
    ) {
    }


    private function request(string $method, string $path, ?array $body = null, ?string $token = null): array
    {
        $url = rtrim($this->baseUrl, '/') . $path;

        $headers = [
            'Accept: application/json',
        ];

        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
        }

        if ($token !== null) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $ch = curl_init($url);


        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 15,
        ]);

        if ($body !== null) {
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode($body, JSON_THROW_ON_ERROR)
            );
        }

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);

            throw new RuntimeException('Curl error: ' . $error);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        $data = $response !== '' ? json_decode($response, true, 512, JSON_THROW_ON_ERROR) : [];

        if ($statusCode < 200 || $statusCode >= 300) {
            $message = $data['detail']
                ?? $data['hydra:description']
                ?? 'Unknown API error';

            throw new RuntimeException(
                "Mail.tm API error {$statusCode}: {$message}"
            );
        }

        return $data;
    }


    public function fetchDomain(int $page = 1): array
    {
        $query = http_build_query([
            'page' => $page,
        ]);

        $data = $this->request('GET', '/domains?' . $query);

        try {

            // Case 1: API returns Hydra collection:
            if (isset($data['hydra:member']) && is_array($data['hydra:member'])) {
                return $data['hydra:member'];
            }

            // Case 2: API returns direct list:
            if (array_is_list($data)) {
                return $data;
            }

            throw new RuntimeException('Unexpected API response format for domains.');

        } catch (Throwable $exception) {
            throw new RuntimeException('Unexpected API response format for domains.', 0, $exception);
        }
    }

    
    // POST /accounts
    public function createAccount(string $address, string $password): array 
    {

        return $this->request('POST', '/accounts', [
            'address' => $address,
            'password' => $password,
        ]);

    }

    public function authenticate(string $address, string $password): string 
    {
        $data = $this->request('POST', '/token', [
                'address' => $address,
                'password' => $password,
            ]);

            if (!isset($data['token'])) {
                throw new RuntimeException('Token was not returned by the API.');
            }

        return $data['token'];
    }

    public function getMe(string $token): array 
    {
        return $this->request('GET', '/me', null, $token);
    }

    public function authorizeAccount(string $address, string $password): array
    {
        $token = $this->authenticate($address, $password);

        $account = $this->getMe($token);

        return [
            'token' => $token,
            'account' => $account,
        ];
    }

    public function fetchMessages(string $token, int $page = 1): array
    {
        $data = $this->request('GET', '/messages?page=' . $page, null, $token);

        if (isset($data['hydra:member']) && is_array($data['hydra:member'])) {
            return ['messages' => $data['hydra:member'],'totalItems' => $data['hydra:totalItems'] ?? count($data['hydra:member'])];
        }

        if (array_is_list($data)) {
            return ['messages' => $data,'totalItems' => count($data),];
        }

        return [
            'messages' => [],
            'totalItems' => 0,
        ];
    }

}

?>
