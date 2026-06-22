<?php

// SignUpHandler should decide WHEN to call the methods
final class SignUpHandler
{
    public function __construct(
        private SignUpAccount $accountService
    ) {
    }

    public function handle(array $post): SignUpData
    {
        $username = trim($post['username'] ?? '');
        $password = trim($post['password'] ?? '');
        $displayName = trim($post['display_name'] ?? '');
        $description = trim($post['description'] ?? '');

        if ($username === '') {
            throw new InvalidArgumentException('Username is required');
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException(
                'Password must be at least 8 characters'
            );
        }

        $domains = $this->accountService->fetchDomain();

        if ($domains === []) {
            throw new RuntimeException('No mail.tm domains available');
        }

        $firstDomain = $domains[0]['domain'];

        $address = $username . '@' . $firstDomain;

        $this->accountService->createAccount($address, $password);

        $authorizedAccount = $this->accountService->authorizeAccount($address, $password);

        $account = $authorizedAccount['account'];

        return new SignUpData(
            username: $username,
            address: $address,
            displayName: $displayName !== '' ? $displayName : null,
            description: $description !== '' ? $description : null,
            accountId: $account['id'],
            quota: (int) $account['quota'],
            used: (int) $account['used'],
            token: $authorizedAccount['token']
        );
    }
}