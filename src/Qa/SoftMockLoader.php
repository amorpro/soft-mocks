<?php

namespace Qa;
/**
 * Created by PhpStorm.
 * User: ZyManch
 * Date: 10.01.2017
 * Time: 18:28
 */
class SoftMockLoader
{

    /**
     * SoftMockLoader constructor.
     * @param $root
     */
    public function __construct($root)
    {
        (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ?
            define('SOFTMOCKS_ROOT_PATH', '') :
            define('SOFTMOCKS_ROOT_PATH', '/');

        require_once(__DIR__ . "/../../vendor/PHP-Parser/lib/PhpParser/Autoloader.php");
        \PhpParser\Autoloader::register(true);
        $this->_autoloadFilesFromDirectory(__DIR__ . '/../../vendor/PHP-Parser/lib/PhpParser/');
        $this->_autoloadFilesFromDirectory(__DIR__ . '/../../src/Qa/');
        SoftMocks::setLockFilePath(sys_get_temp_dir() . '/soft_mocks_rewrite.lock');
        SoftMocks::setPhpunitPath(realpath($root . '/vendor/phpunit'));
        $this->ignorePath($root . '/tests');
        SoftMocks::init();
    }

    protected function _autoloadFilesFromDirectory($dir)
    {
        $out     = [];
        $command = sprintf(
            "find %s -type f -name '*.php'",
            escapeshellarg(realpath($dir))
        );
        @exec($command, $out);
        foreach ($out as $f) {
            if (substr($f, -strlen('SoftMockLoader.php')) !== 'SoftMockLoader.php') {
                require_once($f);
            }
        }
    }

    /**
     * Skip the rewriting all files from the path
     *
     * Path './tests' in the root of your project will be skipped by default
     *
     * @param $path
     * @return $this
     */
    public function ignorePath($path)
    {
        if (!is_array($path)) {
            $path = [$path];
        }
        SoftMocks::addIgnorePath(array_map('realpath', $path));

        return $this;
    }

    /**
     *
     * Base SoftMock configuring to start work with SoftMock
     *
     * SoftMockLoader::create()
     *      ->rewriteAndInclude('vendor/autoload.php')  # will rewrite all files from nested includes
     *      ->setMocksCachePath('/tmp/mocks')
     *
     * If you want to skip the rewriting some file
     *      ->ignoreFile($filePath)
     *
     * If you want to skip the rewriting of all files from some directory
     * (Path './tests' in the root of your project will be skipped by default)
     *      ->ignorePath($path)
     *
     *
     * @param $root
     * @return SoftMockLoader
     */
    public static function create($root)
    {
        return new self($root);
    }

    /**
     * This method is the entry point into the SoftMock.
     *
     * After you've added the file via rewriteAndInclude,
     * all nested include calls will already be "wrapped" by the system itself
     * and you will be ready to use the powerful testing tool
     *
     * In most of times you need to simply rewriteAndInclude only 'vendor/autoload.php' to wrap all the system.
     * But some times you also will be needed to include soome './bootstrap.php' or something else.
     * But be free to use it as many as needed to fully cover your system with SoftMock
     *
     *
     * @param $fileName
     * @param array $args
     * @return $this
     */
    public function rewriteAndInclude($fileName, $args = [])
    {
        extract($args, EXTR_OVERWRITE);
        require_once(SoftMocks::rewrite(realpath($fileName)));
        return $this;
    }

    /**
     * Skip the rewriting file
     *
     * @param $file
     * @return $this
     */
    public function ignoreFile($file)
    {
        if (!is_array($file)) {
            $file = [$file];
        }
        SoftMocks::ignoreFiles(array_map('realpath', $file));

        return $this;
    }

    /**
     * Configure the path where rewrited file will be stored
     * By Default uses '/tmp/mocks'
     *
     * @param string $path
     * @return $this
     */
    public function setMocksCachePath($path = '/tmp/mocks')
    {
        SoftMocks::setMocksCachePath($path);
        return $this;
    }
}