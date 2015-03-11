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
 * Array access for binary files with fixed sized records.
 * 
 * Only accepts integer indexes.
 * This is not a typical PHP array which is actually a dictionary.
 */
class Fileops_Array extends Fileops_Abstract implements ArrayAccess, Countable, SeekableIterator
{

    private $record_size;

    public function __construct($file_name, $record_size = 16)
    {
        parent::__construct($file_name, 'r+');

        $this->record_size = (int) $record_size;
    }

    public function getRecordSize()
    {
        return $this->record_size;
    }

    /// ArrayAccess //

    public function offsetExists($offset)
    {
        $offset = (int) $offset;
        return ($offset + 1) * $this->getRecordSize() <= $this->getSize();
    }

    public function offsetGet($offset)
    {
        $offset = (int) $offset;
        $this->flock(LOCK_SH);
        $this->fseek($offset * $this->getRecordSize());
        $result = $this->fread($this->getRecordSize());
        $this->flock(LOCK_UN);
        return $result;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $offset = $this->count();
        }
        $offset = (int) $offset;
        $value = str_pad($value, $this->getRecordSize(), "\0");
        $this->flock(LOCK_EX);
        $this->fseek($offset * $this->getRecordSize());
        $this->fwrite($value, $this->getRecordSize());
        $this->flock(LOCK_UN);
    }

    public function offsetUnset($offset)
    {
        $this->offsetSet($offset, null);
    }

    /// Countable ///

    public function count()
    {
        return floor($this->getSize() / $this->getRecordSize());
    }

    /// SeekableIterator ///

    /*
     * Do not rely on file pointer because array access should not affect iterator
     */
    private $position = 0;

    public function current()
    {
        return $this[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->position++;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function seek($position)
    {
        $this->position = $position;
    }

    public function valid()
    {
        return $this->position < $this->count();
    }
}
