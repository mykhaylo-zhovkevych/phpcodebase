<?php

final class SignUpData 
{
    public function __construct(
        public readonly string $username,
        public readonly string $address,
        public readonly ?string $displayName,
        public readonly ?string $description,
        public readonly string $accountId,
        public readonly int $quota,
        public readonly int $used,
        public readonly ?string $token = null
    ) {
    }
}

?>