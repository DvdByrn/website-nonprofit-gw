<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit708c6679917d30b2033983fae2615f74
{
    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'Ifsnop\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Ifsnop\\' => 
        array (
            0 => __DIR__ . '/..' . '/ifsnop/mysqldump-php/src/Ifsnop',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit708c6679917d30b2033983fae2615f74::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit708c6679917d30b2033983fae2615f74::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
