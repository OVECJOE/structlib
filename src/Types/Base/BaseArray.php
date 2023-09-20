<?php
declare(strict_types=1);


namespace Structlib\Types\Base;

/**
 * @author OVECJOE <ovecjoe123@gmail.com>
 * @file BaseArray.php: Implements a base class for future array classes.
 */

class BaseArray implements \IteratorAggregate {
    /**
     *  @var array
     *  @access private
     */
    protected $items = [];

    /**
     *  @var int
     *  @access private
     */
    protected $length = 0;

    /**
     *  @var int
     *  @access private
     */
    protected $max_length = -1;

    public function __toString() {
        return get_class( $this ) . '::' . $this->length;
    }

    public function __debugInfo()
    {
        return $this->items;
    }

    public function __get( $name ) {
        if ( property_exists( $this, $name ) ) {
            return $this->$name;
        }

        throw new \OutOfBoundsException("Property '$name' does not exist");
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator( $this->items );
    }

    /**
     *  Check if an array is associative.
     *  @static
     * 
     *  An associative array is an array that contains literal keys as indexes.
     *  An empty array is considered not associative
     * 
     *  @param array $arr The array to check
     * 
     *  @return bool true if an array is associative, false otherwise
     */
    public static function is_map( $arr )
    {
        if ( empty($arr) && ! is_array($arr) ) {
            return false;
        }

        $expectedIndex = 0;
        foreach ( $arr as $key => $value ) {
            if ( $key !== $expectedIndex ) {
                return false;
            }

            $expectedIndex++;
        }

        return true;
    }

    /**
     *  Return the length of the array
     * 
     *  @return int Length
     */
    public function count()
    {
        return $this->length;
    }

    /**
     *  Check if the array is empty
     * 
     *  @return bool True if the array is empty, false otherwise
     */
    public function is_empty()
    {
        return $this->length === 0;
    }

    /**
     *  Check if the array is full
     * 
     *  @return bool True if the array is full, false otherwise
     */
    public function is_full()
    {
        return $this->length === $this->max_length;
    }
}