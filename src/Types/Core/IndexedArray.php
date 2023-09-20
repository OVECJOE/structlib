<?php
declare(strict_types=1);

namespace Structlib\Types\Core;

/**
 * @author OVECJOE <ovecjoe123@gmail.com>
 * @file IndexedArray.php: Implements a wrapper around indexed arrays
 */

use Structlib\Types\Base\BaseArray;

class IndexedArray extends BaseArray
{
    public function __construct( $max_length, ...$items )
    {
        // ensure that $max_length is not negative
        if ( abs($max_length) === $max_length ) {
            $this->max_length = $max_length;
        }

        // count number of items
        $items_count = count( $items );
        
        if ( $this->max_length !== -1 && $items_count > $this->max_length ) {
            throw new \InvalidArgumentException('Number of items exceeds the maximum length specified.');
        }

        $this->items = $items;
        $this->length = $items_count;
    }

    /**
     *  Add an item to the end of the array.
     * 
     *  @param mixed $item The item to add.
     * 
     *  @return bool True if the item was added successfully, else false.
     */
    public function push( $item )
    {
        if ( $this->is_full() ) {
            return false;
        }

        $this->items[] = $item;
        $this->length++;

        return true;
    }

    /**
     *  Add an item to the start of the array.
     * 
     *  @param mixed $item The item to add
     * 
     *  @return boolean true if the item was added successfully, false otherwise.
     */
    public function unshift( $item )
    {
        if ( $this->is_full() ) {
            return false;
        }

        $new_length = array_unshift( $this->items, $item );
        // update the length of the array
        $this->length = $new_length;

        return true;
    }

    /**
     *  Shift an item off the start of the array
     * 
     *  @return mixed The shifted item
     */
    public function shift()
    {
        $shifted_item = array_shift( $this->items );

        if ( $shifted_item !== null ) {
            $this->length--;
        }

        return $shifted_item;
    }

    /**
     *  Remove an item at a given index from the array.
     * 
     *  Without $index given, will remove last item
     * 
     *  @param int $index Optional - The index of the item to remove
     * 
     *  @throws \OutOfRangeException if the index is out of range
     * 
     *  @return mixed The removed item
     */
    public function pop( $index = -1 )
    {
        if ( $index === -1 ) {
            $popped_element = array_pop( $this->items );
        } else {
            if ( $index >= $this->length ) {
                throw new \OutOfRangeException( 'Given index is out of range' );
            }
    
            $popped_element = $this->items[$index];
            // delete the element from the array
            unset( $this->items[$index] );
        }

        // decrement the length of the array
        $this->length--;

        return $popped_element;
    }

    /**
     *  Check if the array has an item
     * 
     *  @param mixed $item The item to check
     *  @param bool $strict Optional - if true then does a strict comparison
     * 
     *  @return bool true if the array contains an item, false otherwise
     */
    public function has( $item, $strict = false )
    {
        return in_array( $item, $this->items, $strict );
    }

    /**
     *  Remove duplicates from the array.
     * 
     *  @return array Returns a new array with duplicates removed
     */
    public function unique() {
        return array_unique( $this->items, SORT_REGULAR );
    }

    /**
     *  Extend the array with a given array i.e. add the elements of the given array
     *  to the end of the array. It is the same as performing push operations multiple times
     * 
     *  @param array $other_arr the given array to extend $this->items by
     * 
     *  @throws \InvalidArgumentException if $other_arr is an associative array
     * 
     *  @return array the extended array.
     */
    public function extend( $other_arr )
    {
        if ( $this->is_map( $other_arr ) ) {
            throw new \InvalidArgumentException( '$other_arr must be an indexed array' );
        }

        foreach ( $other_arr as $item ) {
            $this->push( $item );
        }

        return $this->items;
    }

    /**
     *  merges two or more arrays together and returns a new array. Each array must be
     *  an instance of IndexedArray.
     * 
     *  @static
     *  @param array ...$indexed_arrs
     * 
     *  @return array a new array with all the elements of the arrays merged together
     */
    public static function merge( ...$indexed_arrs )
    {
        $unmerged_arrs = [];

        foreach ( $indexed_arrs as $arr ) {
            if ( ! $arr instanceof IndexedArray ) {
                throw new \InvalidArgumentException( '$indexedArray must be an array of IndexedArray instances' );
            }

            $unmerged_arrs[] = $arr->items;
        }

        return array_merge( ...$unmerged_arrs );
    }

    /**
     *  Use a callback function to filter through the array
     * 
     *  @param callable $callback
     * 
     *  @return array a new array containing items that evaluate to true when the callback was called
     */
    public function filter( $callback )
    {
        $filtered_arr = [];

        foreach ( $this->items as $item ) {
            if ( $callback($item) ) {
                $filtered_arr[] = $item;
            }
        }

        return $filtered_arr;
    }

    /**
     *  Map the callback function to each item in the array.
     * 
     *  @param callable $callback
     * 
     *  @return array a new array with the callback applied to each item in the array
     */
    public function map( $callback )
    {
        return array_map( $callback, $this->items );
    }

    /**
     *  Iteratively reduce the array to a single value using a callback function
     * 
     *  @param callable $callback
     *  @param mixed $initial If the optional initial is available, it will be used at
     *  the beginning of the process, or as a final result in case the array is empty
     * 
     *  @return mixed the resulting value
     */
    public function reduce( $callback, $initial = null )
    {
        return array_reduce( $this->items, $callback, $initial );
    }

    /**
     *  Get the index of the given element if it exists in the array
     * 
     *  @param mixed $element The element to search for
     * 
     *  @return int The index of the element if it exists in the array, -1 otherwise
     */
    public function indexOf( $element )
    {
        $index = 0;

        foreach ( $this->items as $item ) {
            if ( $item === $element ) {
                return $index;
            }

            $index++;
        }

        return -1;
    }

    /**
     *  Get an item at the specified index
     * 
     *  @param int $index
     * 
     *  @throws \OutOfRangeException When index is out of range
     * 
     *  @return mixed Item
     */
    public function get( $index )
    {
        if ( $this->length <= $index ) {
            throw new \OutOfRangeException( 'Given index is out of range' );
        }

        return $this->items[$index];
    }

    /**
     *  Fill the array with $value from a given $start position to the end of the array
     * 
     *  @param int $start The starting position where the fill operation should be performed
     *  @param mixed $value The value to fill the array with
     * 
     *  @return bool True if the fill operation was successful, false otherwise
     */
    public function fill( $start, $value )
    {
        $count = $this->length - $start;
        if ( $count <= 0 ) {
            return false;
        }

        $this->items = array_fill( $start, $count, $value );
        return true;
    }

    /**
     *  Get a random item from the array
     * 
     *  @return mixed|null the random item, or null if array is empty
     */
    public function choice()
    {
        $idx = array_rand( $this->items );
        if ( gettype($idx) !== 'int' ) {
            return null;
        }

        return $this->items[$idx];
    }

    /**
     *  Get a random array of items from the item
     * 
     *  @param int $num the number of items to return
     * 
     *  @return array|null an array of random items if $num < length of array, null otherwise
     */
    public function choices( $num )
    {
        if ( $num === 1 ) {
            return $this->choice();
        }

        $indexes = array_rand( $this->items, $num );
        if ( $indexes === null ) {
            return null;
        }

        return array_map(function ( $index ) {
            return $this->get( $index );
        }, $indexes);
    }

    /**
     *  Get the difference between the array and a given array
     * 
     *  @param array $arr
     * 
     *  @return array an array containing all the entries from $this->items 
     *  that are not present in the other array.
     */
    public function diff( $arr )
    {
        return array_diff( $this->items, $arr );
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
        return array_chunk( $this->items, $length );
    }

    /**
     *  Slice the array of items from a given position and return the result.
     * 
     *  @param int $start The starting position of the slicing operation
     *  @param int $end The ending position of the slicing operation
     * 
     *  @return array An array of items from a given start position to the end position
     */
    public function slice( $start, $end )
    {
        $length = $end - $start;
        return array_slice( $this->items, $start, $length );
    }

    /**
     *  Converts the array into an associative array
     * 
     *  @param int $depth the depth of the conversion process (if there are nested arrays)
     * 
     *  @return array an associative array
     */
    public function to_map( $depth = -1 )
    {
        $a_array = [];
        $stack = [ [$this->items, &$a_array] ];

        while ( $stack ) {
            if ( $depth == 0 ) {
                break;
            }

            [$current, $current_a_array] = array_pop( $stack );

            foreach ( $current as $item ) {
                $key = uniqid();

                if ( is_array( $item ) ) {
                    $stack[] = [ $item, &$current_a_array[$key] ];
                } else {
                    $current_a_array[$key] = $item;
                }
            }

            // decrement $depth by 1 if greater than 0
            if ( $depth > 0 ) {
                $depth--;
            }
        }

        return $a_array;
    }
}
