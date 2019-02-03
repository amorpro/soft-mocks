<?php
namespace Qa;
/**
 * Created by PhpStorm.
 * User: ZyManch
 * Date: 08.11.2015
 * Time: 17:33
 */
trait UnitTest  {

    private $callsAssertions = [];
    private $mocks = [];

    protected function _before() {
        parent::_before();
        $this->callsAssertions = $this->mocks = [];
    }

    protected function _after() {
        parent::_after();
        SoftMocks::restoreAll();

        $this->validateCallAssertions();
    }

    /**
     * @param $class
     * @param $methodName
     * @return MockMethod
     */
    public function mockMethod($class, $methodName) {
        $key = $class . $methodName;
        if(!isset($this->mocks[$key])) {
            $this->mocks[$key] = MockMethod::create( $class, $methodName );
        }

        return $this->mocks[$key];
    }

    public function mockFunction($function, $args, $code) {
        SoftMocks::redefineFunction(
            $function,
            $args,
            $code
        );
    }

    public function mockClassConstant($class, $constantName, $value) {
        SoftMocks::redefineConstant(
            $class.'::'.$constantName,
            $value
        );
    }

    public function mockConstant($constantName, $value) {
        SoftMocks::redefineConstant(
            $constantName,
            $value
        );
    }

    public function mockConstructor($class, $constructorFunction) {
        SoftMocks::redefineNew(
            $class,
            $constructorFunction
        );
    }


    /**
     *
     * $this->mockMethods([
     *     FirstClass::class => 'goFirst',
     *     SomeClass::class => [ '__construct' ],
     *     AnotherOneClass::class => [ 'send', 'processResponse' => true ],
     *     FakeClass::class => [ 'getName' => function(){ return $this->name; } ],
     * ])
     *
     * @param $classMethods
     */
    public function mockMethods($classMethods){
        foreach($classMethods as $class => $methods){
            if(!is_array($methods)){
                $classMethods[$class] = $methods = array($methods);
            }
            foreach($methods as $k => $v){
                $method = is_numeric($k) ? $v : $k;
                $result = is_numeric($k) ? null : $v;

                is_callable($result) ?
                    $this->mockMethod($class, $method)->callback($result):
                    $this->mockMethod($class, $method)->returns($result);
            }
        }
    }

    /**
     *
     * $this->assertCallOnce([
     *     SomeClass::class => '__construct',
     *     AnotherOneClass::class => [ 'send', 'processResponse' ],
     * ], function(){
     *     $s = new SomeClass();
     *     $b = new AnotherOneClass();
     *     $b->send();
     * })
     *
     * @param array $calls
     * @param callable $code
     */
    public function assertCallOnce(array $calls)
    {
        $this->assertCall($this->_mapCallTimes($calls, 'once'));
    }

    /**
     *
     * $this->assertCall([
     *     SomeClass::class => ['__construct' => 'once'],
     *     AnotherOneClass::class => [ 'send' => 'once', 'processResponse' => 'never' ],
     * ], function(){
     *     $s = new SomeClass();
     *     $b = new AnotherOneClass();
     *     $b->send();
     * })
     *
     * @param array $calls
     */
    public function assertCall(array $calls)
    {
        foreach($calls as $class => $methods){
            foreach(array_keys($methods) as $method){
                $this->mockMethod($class, $method);
            }
        }
        $this->callsAssertions = array_merge($this->callsAssertions, $calls);
    }

    /**
     *
     * $this->assertCallNever([
     *     SomeClass::class => '__construct',
     *     AnotherOneClass::class => [ 'send', 'processResponse' ],
     * ], function(){
     *     $b = new B();
     * })
     *
     * @param array $calls
     */
    public function assertCallNever(array $calls)
    {
        $this->assertCall($this->_mapCallTimes($calls, 'never'));
    }

    /**
     * @param array $calls
     * @param $times
     * @return array
     */
    private function _mapCallTimes(array $calls, $times)
    {
        $newCalls = array();
        foreach ($calls as $class => $methods) {
            if (!is_array($methods)) {
                $calls[$class] = $methods = array($methods);
            }
            foreach ($methods as $method) {
                $newCalls[$class][$method] = $times;
            }
        }
        return $newCalls;
    }


    public function validateCallAssertions()
    {
        $calls = $this->callsAssertions;
        foreach($calls as $class => $methods){
            foreach($methods as $method => $times) {
                if (is_numeric($times)) {
                    $this->assertEquals(
                        $times,
                        $this->mockMethod($class, $method)->getCallCount(),
                        sprintf('%s.%s must called %s times', $class, $method, $times)
                    );
                } else {
                    $callValidator = sprintf('isCall%s', ucfirst(strtolower($times)));
                    $this->assertTrue(
                        $this->mockMethod($class, $method)->$callValidator(),
                        sprintf('%s.%s must called %s', $class, $method, $times)
                    );
                }
            }
        }

    }
}