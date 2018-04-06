<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb7ce09b37821aec1efab93a2ece1b54b
{
    public static $files = array (
        'fc73bab8d04e21bcdda37ca319c63800' => __DIR__ . '/..' . '/mikecao/flight/flight/autoload.php',
        '5b7d984aab5ae919d3362ad9588977eb' => __DIR__ . '/..' . '/mikecao/flight/flight/Flight.php',
        '3a37ebac017bc098e9a86b35401e7a68' => __DIR__ . '/..' . '/mongodb/mongodb/src/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'MongoDB\\' => 8,
        ),
        'A' => 
        array (
            'App\\Controllers\\Api\\' => 20,
            'App\\Controllers\\Admin\\' => 22,
            'App\\Controllers\\' => 16,
            'App\\Config\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'MongoDB\\' => 
        array (
            0 => __DIR__ . '/..' . '/mongodb/mongodb/src',
        ),
        'App\\Controllers\\Api\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/controllers/api',
        ),
        'App\\Controllers\\Admin\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/controllers/admin',
        ),
        'App\\Controllers\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/controllers',
        ),
        'App\\Config\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/config',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb7ce09b37821aec1efab93a2ece1b54b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb7ce09b37821aec1efab93a2ece1b54b::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
