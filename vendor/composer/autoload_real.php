<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit241769ecc86d9d596fd842f620d20f00
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit241769ecc86d9d596fd842f620d20f00', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit241769ecc86d9d596fd842f620d20f00', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit241769ecc86d9d596fd842f620d20f00::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
