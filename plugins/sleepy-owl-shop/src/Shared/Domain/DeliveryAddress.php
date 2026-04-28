<?php

namespace SleepyOwl\Shared\Domain;

use SleepyOwl\Shared\Domain\Errors\DeliveryAddressException;

readonly class DeliveryAddress
{
    public function __construct(
        private readonly string $country,
        private readonly string $city,
        private readonly string $street,
        private readonly string $postalCode,
    )
    {
        if (empty($country)) {
            throw new DeliveryAddressException('Country is required.');
        }

        if (empty($city)) {
            throw new DeliveryAddressException('City is required.');
        }

        if (empty($street)) {
            throw new DeliveryAddressException('Street is required.');
        }

        if (empty($postalCode)) {
            throw new DeliveryAddressException('Postal code is required.');
        }
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function equals(self $other): bool
    {
        return $this->country === $other->country
            && $this->city === $other->city
            && $this->street === $other->street
            && $this->postalCode === $other->postalCode;
    }
}