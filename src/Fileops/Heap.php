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
 * File based heap with fixed size records.
 * 
 * Behaves a lot like PHP's arrays but not in memory.
 * Keys may be any scalar data but limited to $key_size.
 * Because records are fixed size read values will be null-padded to $payload_size.
 */
class Fileops_Heap extends Fileops_Abstract implements ArrayAccess
{

    private $key_size;
    private $payload_size;
    private $record_size;
    private $null_key;

    public function __construct($file_name, $key_size = 8, $payload_size = 8)
    {
        $this->key_size = (int) $key_size;
        $this->payload_size = (int) $payload_size;
        $this->record_size = (int) $key_size + (int) $payload_size;
        $this->null_key = str_repeat("\0", $key_size);
        parent::__construct($file_name);
    }

    protected function find($offset)
    {
        $index = 0;
        while (true) {
            $key = $this->readKey($index);
            $cmp = strcmp($offset, $key);
            if (($cmp === 0) || ($key === $this->null_key) || (strlen($key) === 0)) {
                break;
            }
            elseif ($cmp < 0) {
                $index = $index * 2 + 1;
            }
            else {
                $index = $index * 2 + 2;
            }
        }
        return $index;
    }

    protected function readKey($index)
    {
        $this->fseek($index * $this->record_size);
        return $this->fread($this->key_size);
    }

    protected function readPayload($index)
    {
        $this->fseek($index * $this->record_size + $this->key_size);
        return $this->fread($this->payload_size);
    }

    public function offsetExists($offset)
    {
        return (bool) $this->offsetGet($offset);
    }

    public function offsetGet($offset)
    {
        $offset = str_pad($offset, $this->key_size, "\0");
        $this->flock(LOCK_SH);
        $index = $this->find($offset);
        $result = $this->readPayload($index);
        $this->flock(LOCK_UN);
        return $result;
    }

    public function offsetSet($offset, $value)
    {
        $offset = str_pad($offset, $this->key_size, "\0");
        $value = str_pad($value, $this->payload_size, "\0");
        $this->flock(LOCK_EX);
        $index = $this->find($offset);
        $this->fseek($index * $this->record_size);
        $this->fwrite($offset, $this->key_size);
        $this->fwrite($value, $this->payload_size);
        $this->flock(LOCK_UN);
    }

    /*
     * Currently not available because there is no balancing algorithm.
     */
    public function offsetUnset($offset)
    {
        throw new BadMethodCallException('Method not implemented');
    }
}
