<?php

/**
 *  @author OVECJOE <ovecjoe123@gmail.com>
 *  @file AArray.php: Implements an interface for associative arrays
 * 
 *  @declare(strict_types=1);
 */

namespace Structlib\Types;
use Structlib\Types\Base\BArray;

class AArray extends BArray
{
    public function __construct( $items, $max_length = -1 )
    {
        // ensure that $max_length is not negative
        if ( $max_length > 0 ) {
            $this->max_length = $max_length;
        }

        // count number of items
        $items_count = count( $items );
        
        if ( $this->max_length !== -1 && $items_count > $this->max_length ) {
            throw new \InvalidArgumentException('Number of items exceeds the maximum length specified.');
        }

        if ( ! $this->isAssociative( $items ) ) {
            throw new \TypeError('$items must be an associative array');
        }

        $this->items = $items;
        $this->length = $items_count;
    }

    /**
     *  Check if a key exists.
     * 
     *  @param string $key
     * 
     *  @return bool true if the key exists, false otherwise
     */
    public function has( $key )
    {
        return isset( $this->items[$key] );
    }

    /**
     *  Check if value exists.
     * 
     *  @param mixed $value
     * 
     *  @return bool true if value exists, false otherwise
     */
    public function hasValue( $value )
    {
        return in_array( $value, $this->values() );
    }

    /**
     *  Get the value associated with a key.
     * 
     *  @param string $key
     *  @param mixed $default Optional
     * 
     *  @return mixed the value associated with the key, default otherwise
     */
    public function get( $key, $default = null )
    {
        if ( isset($this->items[$key]) ) {
            return $this->items[$key];
        }

        return $default;
    }

    /**
     *  Set or update key-value pair.
     * 
     *  @param string $key
     *  @param mixed $value
     * 
     *  @throws \InvalidArgumentException if $key is not a string (which is the valid key)
     *  @throws \LengthException if the array is already full
     * 
     *  @return void
     */
    public function set( $key, $value )
    {
        if ( gettype($key) !== 'string' ) {
            throw new \InvalidArgumentException( '$key must be a string' );
        }

        if ( ! $this->has( $key ) ) {
            // check that array is not full
            if ( $this->isFull() ) {
                throw new \LengthException( 'Array is already full' );
            }

            $this->length++;
        }

        $this->items[$key] = $value;
    }

    /**
     *  delete an item from the array
     * 
     *  @param string $key
     * 
     *  @return bool true if the item was deleted, false otherwise
     */
    public function delete( $key )
    {
        if ( ! $this->has( $key ) ) {
            return false;
        }

        // delete the item from the array
        unset($this->items[$key]);
        // decrement the length
        $this->length--;

        return true;
    }

    /**
     *  Get an array of all the keys in the array
     * 
     *  @return array
     */
    public function keys()
    {
        return array_keys( $this->items );
    }

    /**
     *  Get an array of all the values in the array
     * 
     *  @return array
     */
    public function values()
    {
        return array_values( $this->items );
    }

    /**
     *  Clear the array
     * 
     *  @return void
     */
    public function clear()
    {
        // set the items to an empty array
        $this->items = [];
        // set the length of the items to zero
        $this->length = 0;
    }

    /**
     *  Perform an operation on each item using the callback function
     *  before removing the items from the array. Only those items on which
     *  the callback function was a success is removed.
     * 
     *  Note that $this->items is modified, hence use method with care.
     * 
     *  @param callable $callback
     * 
     *  @return array An array of items that were not removed
     */
    public function clean( $callback = null )
    {
        $clear = true;

        // remove all items from the array
        foreach ( $this->items as $key => $value ) {
            if ( is_callable($callback) ) {
                $clear = $callback($value);
            }

            if ( $clear ) {
                // remove the item from the array
                unset( $this->items[$key] );
                // reduce the length of the array by 1
                $this->length--;
            }
        }

        return $this->items;
    }

    /**
     *  Merge another array into the current array.
     * 
     *  @param array $other_a_array
     * 
     *  @return void
     */
    public function merge( $other_a_array )
    {
        if ( ! $other_a_array instanceof AArray ) {
            throw new \InvalidArgumentException( '$other_a_array must be an instance of the AArray class' );
        }

        foreach ( $other_a_array as $key => $value ) {
            if ( ! $this->isFull() || $this->has( $key ) ) {
                $this->set( $key, $value );
            }
        }
    }

    /**
     *  Convert the current array into a indexed array
     * 
     *  @param array $depth = -1 Optional:
     *  Decides by much depth the conversion should go.
     * 
     *  @return array
     */
    public function toIArray( $depth = -1 )
    {
        $i_array = [];
        $stack = [ [$this->values(), &$i_array] ];

        while ( $stack ) {
            if ( $depth == 0 ) {
                break;
            }

            [$current, $current_i_array] = array_pop( $stack );

            foreach ( $current as $idx => $item ) {
                if ( $this->isAssociative($item) ) {
                    $stack[] = [ array_values($item), &$current_i_array[$idx] ];
                } else {
                    $current_i_array[$idx] = $item;
                }
            }

            if ( $depth > 0 ) {
                $depth--;
            }
        }

        return $i_array;
    }

    /**
     *  Clone the current array and return the copy.
     * 
     *  @return AArray
     */
    public function clone()
    {
        return new AArray( $this->items, $this->max_length );
    }

    /**
     *  Filter the current array and return the filtered array
     * 
     *  @param callable $callback
     * 
     *  @return array the filtered array
     */
    public function filter( $callback ) {
        $filtered = [];

        if ( ! is_callable( $callback ) ) {
            throw new \InvalidArgumentException( 'Callback must be a callable' );
        }

        // check that the number of arguments expected from the callback is 2 (key, value)
        $funcReflection = new \ReflectionFunction( $callback );
        $numArgs = $funcReflection->getNumberOfParameters();

        if ( $numArgs != 2 ) {
            throw new \InvalidArgumentException( "Invalid number of arguments. Expected: 2, Got: $numArgs" );
        }

        foreach ( $this->items as $key => $value ) {
            if ( $callback( $key, $value ) ) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     *  Split $this->items into chunks of $length size.
     * 
     *  @param int $length the length of each chunk
     * 
     *  @return array an array of chunks of $length sizes (a multi-dimensional array)
     */
    public function chunk( $length )
    {
        return array_chunk( $this->items, $length, true );
    }
}
