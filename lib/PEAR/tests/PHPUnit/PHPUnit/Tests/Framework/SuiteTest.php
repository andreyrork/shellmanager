<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2008, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id: SuiteTest.php 3229 2008-06-16 08:28:29Z sb $
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/TestResult.php';
require_once 'PHPUnit/Framework/TestSuite.php';

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'InheritedTestCase.php';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'NoTestCaseClass.php';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'NoTestCases.php';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'NotPublicTestCase.php';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'NotVoidTestCase.php';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'OneTestCase.php';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'OverrideTestCase.php';

/**
 *
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: 3.3.1
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class Framework_SuiteTest extends PHPUnit_Framework_TestCase {
    protected $result;

    protected function setUp()
    {
        $this->result = new PHPUnit_Framework_TestResult;
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite;

        $suite->addTest(new Framework_SuiteTest('testAddTestSuite'));
        $suite->addTest(new Framework_SuiteTest('testInheritedTests'));
        $suite->addTest(new Framework_SuiteTest('testNoTestCases'));
        $suite->addTest(new Framework_SuiteTest('testNoTestCaseClass'));
        $suite->addTest(new Framework_SuiteTest('testNotExistingTestCase'));
        $suite->addTest(new Framework_SuiteTest('testNotPublicTestCase'));
        $suite->addTest(new Framework_SuiteTest('testNotVoidTestCase'));
        $suite->addTest(new Framework_SuiteTest('testOneTestCase'));
        $suite->addTest(new Framework_SuiteTest('testShadowedTests'));

        return $suite;
    }

    public function testAddTestSuite()
    {
        $suite = new PHPUnit_Framework_TestSuite(
          'OneTestCase'
        );

        $suite->run($this->result);

        $this->assertEquals(1, count($this->result));
    }

    public function testInheritedTests()
    {
        $suite = new PHPUnit_Framework_TestSuite(
          'InheritedTestCase'
        );

        $suite->run($this->result);

        $this->assertTrue($this->result->wasSuccessful());
        $this->assertEquals(2, count($this->result));
    }

    public function testNoTestCases()
    {
        $suite = new PHPUnit_Framework_TestSuite(
          'NoTestCases'
        );

        $suite->run($this->result);

        $this->assertTrue(!$this->result->wasSuccessful());
        $this->assertEquals(1, $this->result->failureCount());
        $this->assertEquals(1, count($this->result));
    }

    public function testNoTestCaseClass()
    {
        $suite = new PHPUnit_Framework_TestSuite(
          'NoTestCaseClass'
        );

        $suite->run($this->result);

        $this->assertTrue(!$this->result->wasSuccessful());
        $this->assertEquals(1, count($this->result));
    }

    public function testNotExistingTestCase()
    {
        $suite = new Framework_SuiteTest('notExistingMethod');

        $suite->run($this->result);

        $this->assertEquals(0, $this->result->errorCount());
        $this->assertEquals(1, $this->result->failureCount());
        $this->assertEquals(1, count($this->result));
    }

    public function testNotPublicTestCase()
    {
        $suite = new PHPUnit_Framework_TestSuite(
          'NotPublicTestCase'
        );

        $this->assertEquals(2, count($suite));
    }

    public function testNotVoidTestCase()
    {
        $suite = new PHPUnit_Framework_TestSuite(
          'NotVoidTestCase'
        );

        $this->assertEquals(1, count($suite));
    }

    public function testOneTestCase()
    {
        $suite = new PHPUnit_Framework_TestSuite(
          'OneTestCase'
        );

        $suite->run($this->result);

        $this->assertEquals(0, $this->result->errorCount());
        $this->assertEquals(0, $this->result->failureCount());
        $this->assertEquals(1, count($this->result));
        $this->assertTrue($this->result->wasSuccessful());
    }

    public function testShadowedTests()
    {
        $suite = new PHPUnit_Framework_TestSuite(
          'OverrideTestCase'
        );

        $suite->run($this->result);

        $this->assertEquals(1, count($this->result));
    }
}
?>
