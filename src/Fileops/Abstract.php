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

/**
 * Binary equivalent of SplFileObject without fgets and CSV stuff.
 */
abstract class Fileops_Abstract extends SplFileInfo
{

    private $handle;

    public function __construct($file_name)
    {
        parent::__construct($file_name);
        $this->handle = fopen($file_name, 'c+');
    }

    public function eof()
    {
        return feof($this->handle);
    }

    public function fflush()
    {
        return fflush($this->handle);
    }

    public function fgetc()
    {
        return fgetc($this->handle);
    }

    public function flock($operation, &$wouldblock = null)
    {
        return flock($this->handle, $operation, $wouldblock);
    }

    public function fpassthru()
    {
        return fpassthru($this->handle);
    }

    public function fread($length)
    {
        return fread($this->handle, $length);
    }

    public function fseek($offset, $whence = SEEK_SET)
    {
        return fseek($this->handle, $offset, $whence);
    }

    public function fstat()
    {
        return fstat($this->handle);
    }

    public function ftell()
    {
        return ftell($this->handle);
    }

    public function ftruncate($size)
    {
        return ftruncate($this->handle, $size);
    }

    public function fwrite($string, $length = null)
    {
        return fwrite($this->handle, $string, $length);
    }

    /**
     * Size of file in bytes
     * 
     * Ancestor method is inaccurate because it caches stat.
     * 
     * @see SplFileInfo::getSize()
     */
    public function getSize()
    {
        $stat = $this->fstat();
        return (int) @$stat['size'];
    }
}
