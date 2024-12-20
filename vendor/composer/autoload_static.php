<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit241769ecc86d9d596fd842f620d20f00
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Flender\\OpenAI\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Flender\\OpenAI\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInit241769ecc86d9d596fd842f620d20f00::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit241769ecc86d9d596fd842f620d20f00::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit241769ecc86d9d596fd842f620d20f00::$classMap;

        }, null, ClassLoader::class);
    }
}
