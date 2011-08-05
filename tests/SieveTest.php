<?php
/**
 * This file contains the PHPUnit test case for Net_Sieve.
 *
 * PHP version 4
 *
 * +-----------------------------------------------------------------------+
 * | All rights reserved.                                                  |
 * |                                                                       |
 * | Redistribution and use in source and binary forms, with or without    |
 * | modification, are permitted provided that the following conditions    |
 * | are met:                                                              |
 * |                                                                       |
 * | o Redistributions of source code must retain the above copyright      |
 * |   notice, this list of conditions and the following disclaimer.       |
 * | o Redistributions in binary form must reproduce the above copyright   |
 * |   notice, this list of conditions and the following disclaimer in the |
 * |   documentation and/or other materials provided with the distribution.|
 * |                                                                       |
 * | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
 * | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
 * | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
 * | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
 * | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
 * | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
 * | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
 * | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
 * | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
 * | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
 * | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
 * +-----------------------------------------------------------------------+
 *
 * @category  Networking
 * @package   Net_Sieve
 * @author    Anish Mistry <amistry@am-productions.biz>
 * @copyright 2006 Anish Mistry
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD
 * @version   SVN: $Id$
 * @link      http://pear.php.net/package/Net_Sieve
 */

require_once dirname(__FILE__) . '/password.inc.php';
require_once dirname(__FILE__) . '/../Sieve.php';
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * PHPUnit test case for Net_Sieve.
 *
 * @category  Networking
 * @package   Net_Sieve
 * @author    Anish Mistry <amistry@am-productions.biz>
 * @copyright 2006 Anish Mistry
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Net_Sieve
 */
class SieveTest extends PHPUnit_Framework_TestCase
{
    // contains the object handle of the string class
    protected $fixture;

    protected function setUp()
    {
        // Create a new instance of Net_Sieve.
        $this->fixture = new Net_Sieve();
        $this->scripts = array(
            'test script1' => "require \"fileinto\";\n\rif header :contains \"From\" \"@cnba.uba.ar\" \n\r{fileinto \"INBOX.Test1\";}\r\nelse \r\n{fileinto \"INBOX\";}",
            'test script2' => "require \"fileinto\";\n\rif header :contains \"From\" \"@cnba.uba.ar\" \n\r{fileinto \"INBOX.Test\";}\r\nelse \r\n{fileinto \"INBOX\";}",
            'test"scriptäöü3' => "require \"vacation\";\nvacation\n:days 7\n:addresses [\"matthew@de-construct.com\"]\n:subject \"This is a test\"\n\"I'm on my holiday!\nsadfafs\";",
            'test script4' => file_get_contents(dirname(__FILE__) . '/largescript.siv'));

        // Clear all the scripts in the account.
        $this->login();
        $scripts = $this->check($this->fixture->listScripts());
        foreach ($scripts as $script) {
            $this->check($this->fixture->removeScript($script));
        }
        $this->logout();
    }
    
    protected function tearDown()
    {
        // Delete the instance.
        unset($this->fixture);
    }
    
    protected function login()
    {
        $result = $this->fixture->connect(HOST, PORT);
        $this->assertTrue($this->check($result), 'Can not connect');
        $result = $this->fixture->login(USERNAME, PASSWORD, null, '', false);
        $this->assertTrue($this->check($result), 'Can not login');
    }

    protected function logout()
    {
        $result = $this->fixture->disconnect();
        $this->assertFalse(PEAR::isError($result), 'Error on disconnect');
    }

    protected function check($result)
    {
        if (PEAR::isError($result)) {
            throw new Exception($result->getMessage());
        }
        return $result;
    }

    public function testConnect()
    {
        $result = $this->fixture->connect(HOST, PORT);
        $this->assertTrue($this->check($result), 'Can not connect');
    }
    
    public function testLogin()
    {
        $result = $this->fixture->connect(HOST, PORT);
        $this->assertTrue($this->check($result), 'Can not connect');
        $result = $this->fixture->login(USERNAME, PASSWORD, null, '', false);
        $this->assertTrue($this->check($result), 'Can not login');
    }

    public function testDisconnect()
    {
        $result = $this->fixture->connect(HOST, PORT);
        $this->assertFalse(PEAR::isError($result), 'Can not connect');
        $result = $this->fixture->login(USERNAME, PASSWORD, null, '', false);
        $this->assertFalse(PEAR::isError($result), 'Can not login');
        $result = $this->fixture->disconnect();
        $this->assertFalse(PEAR::isError($result), 'Error on disconnect');
    }

    public function testListScripts()
    {
        $this->login();
        $scripts = $this->fixture->listScripts();
        $this->logout();
        $this->assertFalse(PEAR::isError($scripts), 'Can not list scripts');
    }

    public function testInstallScript()
    {
        $this->login();

        // First script.
        $scriptname = 'test script1';
        $before_scripts = $this->fixture->listScripts();
        $result = $this->fixture->installScript($scriptname, $this->scripts[$scriptname]);
        $this->assertFalse(PEAR::isError($result), 'Can not install script ' . $scriptname);
        $after_scripts = $this->fixture->listScripts();
        $diff_scripts = array_values(array_diff($after_scripts, $before_scripts));
        $this->assertTrue(count($diff_scripts) > 0, 'Script not installed');
        $this->assertEquals($scriptname, $diff_scripts[0], 'Added script has a different name');

        // Second script (install and activate)
        $scriptname = 'test script2';
        $before_scripts = $this->fixture->listScripts();
        $result = $this->fixture->installScript($scriptname, $this->scripts[$scriptname], true);
        $this->assertFalse(PEAR::isError($result), 'Can not install script ' . $scriptname);
        $after_scripts = $this->fixture->listScripts();
        $diff_scripts = array_values(array_diff($after_scripts, $before_scripts));
        $this->assertTrue(count($diff_scripts) > 0, 'Script not installed');
        $this->assertEquals($scriptname, $diff_scripts[0], 'Added script has a different name');
        $active_script = $this->fixture->getActive();
        $this->assertEquals($scriptname, $active_script, 'Added script has a different name');
        $this->logout();
    }

    /**
     * There is a good chance that this test will fail since most servers have
     * a 32KB limit on uploaded scripts.
     */
    public function testInstallScriptLarge()
    {
        $this->login();
        $scriptname = 'test script4';
        $before_scripts = $this->fixture->listScripts();
        $result = $this->fixture->installScript($scriptname, $this->scripts[$scriptname]);
        $this->assertFalse(PEAR::isError($result), 'Unable to upload large script (expected behavior for most servers)');
        $after_scripts = $this->fixture->listScripts();
        $diff_scripts = array_diff($before_scripts, $after_scripts);
        $this->assertEquals($scriptname, $diff_scripts[0], 'Added script has a different name');
        $this->logout();
    }

    /**
     * See bug #16691.
     */
    public function testInstallNonAsciiScript()
    {
        $this->login();

        $scriptname = 'test"scriptäöü3';
        $before_scripts = $this->fixture->listScripts();
        $result = $this->fixture->installScript($scriptname, $this->scripts[$scriptname]);
        $this->assertFalse(PEAR::isError($result), 'Can not install script ' . $scriptname);
        $after_scripts = $this->fixture->listScripts();
        $diff_scripts = array_values(array_diff($after_scripts, $before_scripts));
        $this->assertTrue(count($diff_scripts) > 0, 'Script not installed');
        $this->assertEquals($scriptname, $diff_scripts[0], 'Added script has a different name');

        $this->logout();
    }

    public function testGetScript()
    {
        $this->login();
        $scriptname = 'test script1';
        $before_scripts = $this->fixture->listScripts();
        $result = $this->fixture->installScript($scriptname, $this->scripts[$scriptname]);
        $this->assertFalse(PEAR::isError($result), 'Can not install script ' . $scriptname);
        $after_scripts = $this->fixture->listScripts();
        $diff_scripts = array_values(array_diff($after_scripts, $before_scripts));
        $this->assertTrue(count($diff_scripts) > 0);
        $this->assertEquals($scriptname, $diff_scripts[0], 'Added script has a different name');
        $script = $this->fixture->getScript($scriptname);
        $this->assertEquals(trim($this->scripts[$scriptname]), trim($script), 'Script installed it not the same script retrieved');
        $this->logout();
    }

    public function testGetActive()
    {
        $this->login();
        $active_script = $this->fixture->getActive();
        $this->assertFalse(PEAR::isError($active_script), 'Error getting the active script');
        $this->logout();
    }

    public function testSetActive()
    {
        $scriptname = 'test script1';
        $this->login();
        $result = $this->fixture->installScript($scriptname, $this->scripts[$scriptname]);
        $result = $this->fixture->setActive($scriptname);
        $this->assertFalse(PEAR::isError($result), 'Can not set active script');
        $active_script = $this->fixture->getActive();
        $this->assertEquals($scriptname, $active_script, 'Active script does not match');

        // Test for non-existant script.
        $result = $this->fixture->setActive('non existant script');
        $this->assertTrue(PEAR::isError($result));
        $this->logout();
    }

    public function testRemoveScript()
    {
        $scriptname = 'test script1';
        $this->login();
        $result = $this->fixture->installScript($scriptname, $this->scripts[$scriptname]);
        $result = $this->fixture->removeScript($scriptname);
        $this->assertFalse(PEAR::isError($result), 'Error removing active script');
        $this->logout();
    }
}
