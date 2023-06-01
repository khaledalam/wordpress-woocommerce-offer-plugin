<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitd65ff1170aa46a7d39b276c13b0ac7ab
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

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInitd65ff1170aa46a7d39b276c13b0ac7ab', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitd65ff1170aa46a7d39b276c13b0ac7ab', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitd65ff1170aa46a7d39b276c13b0ac7ab::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}