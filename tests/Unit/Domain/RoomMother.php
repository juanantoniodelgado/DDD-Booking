<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain;

use App\Domain\Model\Room\Room;
use Faker\Factory;

final class RoomMother
{
    /**
     * @return Room
     * @throws \App\Infrastructure\Exception\InvalidParameterException
     */
    public static function random(): Room
    {
        $faker = Factory::create();

        return new Room(
            $faker->numberBetween(1, 1000),
            $faker->name
        );
    }
}