<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb75cd351a4e8a053d515795e7c4823bf
{
    public static $prefixLengthsPsr4 = array (
        'E' => 
        array (
            'ET\\WPPluginAllowList\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ET\\WPPluginAllowList\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'ET\\WPPluginAllowList\\AdminNotices' => __DIR__ . '/../..' . '/src/AdminNotices.php',
        'ET\\WPPluginAllowList\\AdminPanel' => __DIR__ . '/../..' . '/src/AdminPanel.php',
        'ET\\WPPluginAllowList\\WPPluginAllowList' => __DIR__ . '/../..' . '/src/WPPluginAllowList.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb75cd351a4e8a053d515795e7c4823bf::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb75cd351a4e8a053d515795e7c4823bf::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb75cd351a4e8a053d515795e7c4823bf::$classMap;

        }, null, ClassLoader::class);
    }
}
