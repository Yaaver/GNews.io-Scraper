<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4b641de0847d88f3121cec0230be4f83
{
    public static $prefixLengthsPsr4 = array (
        'Y' => 
        array (
            'YK\\CustomPlugin\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'YK\\CustomPlugin\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit4b641de0847d88f3121cec0230be4f83::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit4b641de0847d88f3121cec0230be4f83::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit4b641de0847d88f3121cec0230be4f83::$classMap;

        }, null, ClassLoader::class);
    }
}
