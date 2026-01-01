<?php

namespace App\Application\User\Commands;

readonly class RegisterProviderCommand
{
    public function __construct(
        public string $name,
        public string $email,
        public string $phone,
        public string $password,
        public string $businessName,
        public string $address,
        public string $city,
        public string $region,
        public ?string $bio = null,
        public ?string $businessRegistrationNumber = null,
        public ?array $preferences = null,
    ) {}
}

