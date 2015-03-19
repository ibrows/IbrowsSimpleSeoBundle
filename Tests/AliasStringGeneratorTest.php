<?php

namespace IbrowsSimpleSeoBundle\Tests;

use Doctrine\ORM\Query;
use Ibrows\SimpleSeoBundle\Model\AliasStringGenerator;

class AliasStringGeneratorTest extends \PHPUnit_Framework_TestCase
{


    public function provideTokens()
    {
        return array(
            array(array('blah', 'foo', 'moo', 'ich', 'will', 'aber', 'lieber', 'nicht'), array('blah foo', 'moo', 'ich will, aber lieber nicht!')),
            array(array('abc'), array('abc')),
            array(array('a','b','c'), array(' a b c ')),
            array(array(), array('')),
            array(array('ichBinNichtDu'), array('ichBinNichtDu')),
            array(array('ichBin','NichtDu'), array('ichBin....NichtDu')),
        );
    }

    /**
     * @dataProvider provideTokens
     * @param $expected
     * @param $input
     */
    public function testTokens($expected, array $input)
    {
        $generator = new AliasStringGenerator();
        $method = new \ReflectionMethod(get_class($generator), 'getTokens');
        $method->setAccessible(true);
        $this->assertEquals($expected, $method->invoke($generator, $input));
    }


    public function provideUniquify()
    {
        return array(
            array( 1, 'a-0', 'a'),
            array( 2, 'a-1', 'a'),
            array( 101, 'a-100', 'a'),
            array( 1,'blahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafoo-0', 'blahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafoooo'),
            array( 1001,'blahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblaha-1000', 'blahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafoooo'),
        );
    }

    /**
     * @dataProvider provideUniquify
     * @param $expected
     * @param $input
     */
    public function testUniquify($aliasExistsCount, $expected, $input)
    {
        $generator = new AliasStringGenerator();
        $method = new \ReflectionMethod(get_class($generator), 'uniquify');
        $method->setAccessible(true);
        $aliasMock = $this->getMock('Ibrows\SimpleSeoBundle\Model\AliasExistsInterface');
        $count = 0;
        $func = function() use (&$count,$aliasExistsCount){$count++;return $count < $aliasExistsCount;};
        $aliasMock->expects($this->exactly($aliasExistsCount))->method('aliasExists')->will(
            $this->returnCallback($func)
        );
        $this->assertEquals($expected, $method->invoke($generator, $input,$aliasMock));
    }


    public function provideGenerate()
    {
        return array(
            array('blah/foo', array('BlaH', 'Foo')),
            array('blah/foo', array('BlaH', 'Foo ')),
            array('blah/foo', array('BlaH', ' F o o ')),
            array('blah/foo', array('BlaH', ' F o o ')),
            array('blah/foo', array('BlaH', 'Föö')),
            array('blah/foo', array('BlaH', 'F/öö')),
            array('blah/foo/moo/ich/will/aber/lieber/nicht', array('blah', 'foo', 'moo', 'ich', 'will', 'aber', 'lieber', 'nicht')),
            array('blahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafooooblahafoooo', array('BlaHaFooOOBlaHaFooOOBlaHaFooOOBlaHaFooOOBlaHaFooOOBlaHaFooOOBlaHaFooOOBlaHaFooOOBlaHaFooOOBlaHaFooOOnotmorethan100')),
        );
    }

    /**
     * @dataProvider provideGenerate
     * @param $expected
     * @param $input
     */
    public function testGenerate($expected, array $input)
    {
        $generator = new AliasStringGenerator();
        $method = new \ReflectionMethod(get_class($generator), 'generate');
        $method->setAccessible(true);
        $this->assertEquals($expected, $method->invoke($generator, $input));
    }


}
