<?php
/**
 * Created by PhpStorm.
 * User: AmorPro
 * Date: 02.02.2019
 * Time: 16:49
 */

namespace Qa;

include_once 'UnitTest.php';

/**
 * Class SoftMock
 * @package QA
 */
class SoftMock
{
    use UnitTest;

    /**
     * @var \Closure
     */
    protected static $assertTrueHandler;

    public static function setAssertTrueHandler($callback)
    {
        if(!is_callable($callback)){
            throw new \InvalidArgumentException('AssertTrue handler must be callable');
        }
        self::$assertTrueHandler = $callback;
    }

    public static function create()
    {
        SoftMocks::restoreAll();
        return new self();
    }

    public function __destruct()
    {
        SoftMocks::restoreAll();

        $this->validateCallAssertions();
    }

    /**
     * @param $expected
     * @param $actual
     * @param $message
     * @return $this
     */
    public function assertEquals($expected, $actual, $message)
    {
        $this->assertTrue($expected !== $actual, $message);
        return $this;
    }

    /**
     * @param $true
     * @param $message
     * @return $this
     */
    public function assertTrue($true, $message)
    {
        // Third-Party assertTrue handler
        if(self::$assertTrueHandler){
            /** @var \Closure $assertTrueHandler */
            $handler = self::$assertTrueHandler;
            $handler($true, $message);
            return;
        }

        // Default assertTrueHandler if was not configured
        if (!$true) {
            throw new \RuntimeException($message);
        }

        return $this;
    }

}