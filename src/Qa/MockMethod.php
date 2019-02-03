<?php
namespace Qa;
/**
 * Created by PhpStorm.
 * User: ZyManch
 * Date: 12.01.2017
 * Time: 16:52
 */
class MockMethod {


    protected $_class;
    protected $_method;

    protected $_callArgs = [];
    protected $_fakeValue;
    protected $_arrayOfFakeValue;
    protected $_callback;

    public function __construct($class, $method) {
        $this->_class = $class;
        $this->_method = $method;
        SoftMocks::redefineMethod(
            $this->_class,
            $this->_method,
            function() {
                $currentArgs = func_get_args();
                $this->_callArgs[] = $currentArgs;
                if ($this->_callback) {
                    return call_user_func_array($this->_callback, $currentArgs);
                }
                if (is_array($this->_arrayOfFakeValue)) {
                    return array_shift($this->_arrayOfFakeValue);
                }
                return $this->_fakeValue;
            }
        );
    }

    public static function create($class, $method) {
        return new self($class, $method);
    }


    public function callback($callback) {
        $this->_callback = $callback;
        return $this;
    }


    public function returns($fakeValue) {
        $this->_fakeValue = $fakeValue;
        return $this;
    }

    public function returnsValues($arrayOfFakeValue) {
        $this->_arrayOfFakeValue = $arrayOfFakeValue;
        return $this;
    }

    public function getCallCount() {
        return count($this->_callArgs);
    }

    public function isCallOnce() {
        return $this->getCallCount() === 1;
    }

    public function isCallNever() {
        return $this->getCallCount() === 0;
    }

    public function getArgsForFirstCall($position = null) {
        return is_null($position) ? reset($this->_callArgs) : reset($this->_callArgs)[$position];
    }

    public function getArgsForNextCall($position = null) {
        return is_null($position) ? next($this->_callArgs) : next($this->_callArgs)[$position];
    }

    public function getArgsForLastCall($position = null) {
        return is_null($position) ? end($this->_callArgs) : end($this->_callArgs)[$position];
    }

    public function getArgsForAllCall($position = null) {
        if ($position === null) {
            return $this->_callArgs;
        }
        $result = [];
        foreach ($this->_callArgs as $args) {
            $result[] = $args[$position];
        }
        return $result;
    }
    
    
}