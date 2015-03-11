<?php
/**
 * Copyright (C) 2015 Daniel Deady
 *
 * This file is part of big-binary-files.
 *
 * big-binary-files is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * big-binary-files is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with big-binary-files. If not, see <http://www.gnu.org/licenses/>.
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html
 */

class Fileops_HeapTest extends PHPUnit_Framework_TestCase
{

    private $filename;

    function setUp()
    {
        $this->filename = tempnam(sys_get_temp_dir(), 'heap');
    }

    function tearDown()
    {
        unlink($this->filename);
    }

    function testInitialiseEmptyFile()
    {
        $heap = new Fileops_Heap($this->filename);

        $this->assertTrue(is_readable($this->filename));
        $this->assertEquals(0, filesize($this->filename));
    }

    function testExpandFileByOneRecord()
    {
        $heap = new Fileops_Heap($this->filename);
        // payloads must be 8 bytes
        $heap['root'] = 'junkdata';

        $this->assertEquals('junkdata', $heap['root']);
        $this->assertEquals(16, filesize($this->filename));
    }

    function testTruncatePayloadTo8Bytes()
    {
        $heap = new Fileops_Heap($this->filename);
        $heap['root'] = 'truncated.';
        
        $this->assertEquals('truncate', $heap['root']);
        $this->assertEquals(16, filesize($this->filename));
    }

    function testExpandFileByTwoRecords()
    {
        $heap = new Fileops_Heap($this->filename);
        $heap['root'] = 'junkdata';
        $heap['second'] = 'junkdata';

        $this->assertEquals("\0\0\0\0\0\0\0\0", $heap['first']);
        $this->assertEquals('junkdata', $heap['second']);
        $this->assertEquals('junkdata', $heap['root']);
        $this->assertEquals(48, filesize($this->filename));
    }

    function testOverwriteRecordAndNotReadFirstValue()
    {
        $heap = new Fileops_Heap($this->filename);
        $heap['root'] = 'junkdata';
        $heap['root'] = 'new data';

        $this->assertEquals(16, filesize($this->filename));
        $this->assertEquals('new data', $heap['root']);
    }

    /**
     * @expectedException BadMethodCallException
     */
    function testUnsetNonexistentRecord()
    {
        $heap = new Fileops_Heap($this->filename);
        unset($heap['root']);
    }
}
