<?php

abstract class PowerState
{
    const OFF = 0;
    const ON = 1;
    const SUSPENDED = 2;
    const UNKNOWN = 3;

    const STATES = [
        'powered off' => self::OFF,
        'poweredoff' => self::OFF,
        'shut off' => self::OFF,

        'powered on' => self::ON,
        'poweredon' => self::ON,
        'running' => self::ON,

        'suspended' => self::SUSPENDED,
        'paused' => self::SUSPENDED,
    ];
}