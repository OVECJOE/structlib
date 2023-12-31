<?php

declare(strict_types=1);

namespace Structlib\Structures;

/**
 *  @author OVECJOE <ovecjoe123@gmail.com>
 *  @file LinkedNodes.php: Implements a robust interface for LinkedList with some additional flavors.
 */

use Structlib\Types\Base\Node;

class LinkedNodes {
    /**
     *  @var int
     *  @access private
     */
    public $length = 0;

    /**
     *  @var Node|null
     *  @access private
     */
    private $head;
    
    /**
     *  @var Node|null
     *  @access private
     */
    private $tail;

    public function __construct( ...$elements )
    {
        foreach ( $elements as $element ) {            
            // add the node to the list
            $this->append( $element );
        }
    }

    public function __get( $name ) {
        if ( property_exists( $this, $name ) ) {
            return $this->$name;
        }

        throw new \OutOfBoundsException("Property '$name' does not exist");
    }

    public function __debugInfo()
    {
        return $this->to_array();
    }

    public function __toString()
    {
        // start the output
        $output = "LinkedNodes(\n";

        $trav = $this->head;

        while ( $trav ) {
            $json_data = $trav->to_json();

            // add the node's json representation to the output
            if ( $json_data !== false ) {
                $output .= "\t{$trav->pos}: {$json_data}\n";
            } else {
                $output .= "\t{$trav->pos}: [COULD_NOT_BE_DISPLAYED]\n";
            }

            $trav = $trav->next;
        }

        // end the output
        $output .= ")[{$this->length}]\n";

        return $output;
    }

    /**
     *  Converts the nodes list into an array
     * 
     *  @return array an array representation of the nodes list
     */
    protected function to_array()
    {
        $items = [];

        $trav = $this->head;
        while ( $trav ) {
            // add the node's data to the array
            array_push( $items, $trav->data );

            // move to the next node
            $trav = $trav->next;
        }

        return $items;
    }

    /**
     *  Get traversal info based on the index provided.
     * 
     *  This method is called internally when traversing the list is expedient.
     *  It determines from what direction the traversal should go to produce quicker results
     * 
     *  @param int $index Serves as the stopping point for the traversal
     * 
     *  @return array An array containing the traversal info, which are:
     *  - midpoint: the floor value of the midpoint of the list length
     *  - trav: the starting point for the traversal
     *  - no_cycles: The number of traversals to perform
     *  - direction: the direction of the traversal (prev or next)
     */
    private function cal_trav_pos($index)
    {
        // get the midpoint of the list length
        $midpoint = floor($this->length / 2);
    
        // get the starting node for traversal
        $trav = $index <= $midpoint ? $this->head : $this->tail;
    
        // calculate the number of cycles needed to get to the node of insertion
        $no_cycles = $index <= $midpoint ? $index : $this->length - $index - 1;
    
        // Determine the direction for traversal
        $direction = $index <= $midpoint ? 'next' : 'prev';
    
        return [
            $trav,
            $no_cycles,
            $direction,
        ];
    }    

    /**
     *  Add an element to the end of the list.
     * 
     *  See the extend() method if you want to join two NodeList instances together.
     * 
     *  @param mixed $node
     * 
     *  @return LinkedNodes
     */
    public function append( $node )
    {
        if ( $node instanceof Node ) {
            $node->set_pos( $this->length );
        } else {
            $node = new Node( $node, $this->length );
        }

        if ( ! $this->head ) {
            $this->head = $node;
            $this->tail = $node;
        } else {
            // set the tail's next to the node
            $this->tail->set_next( $node );
            // set the node's prev to the tail
            $node->set_prev( $this->tail );
            // set the list's tail to the node
            $this->tail = $node;
        }

        // increment the length of the list
        $this->length++;

        return $this;
    }

    /**
     *  Add a node to the beginning of the list.
     * 
     *  See the extend() method if you want to join two NodeList instances together.
     * 
     *  @param mixed $node
     * 
     *  @return LinkedNodes
     */
    public function prepend( $node )
    {
        if ( $node instanceof Node ) {
            $node->set_pos(0);
        } else {
            $node = new Node( $node, 0 );
        }

        if ( ! $this->head ) {
            $this->head = $node;
            $this->tail = $node;
        } else {
            // set the head's prev to the node
            $this->head->set_prev( $node );
            // set the node's next to the head
            $node->set_next( $this->head );
            // set the head to the node
            $this->head = $node;
        }

        // increment the length of the list
        $this->length++;

        return $this;
    }

    /**
     *  Insert a node into the list at a given index.
     * 
     *  @param int $index the index of the node to insert
     *  @param mixed $element the element to insert at given index
     * 
     *  @return bool true if element was inserted successfully, false otherwise
     */
    public function insert( $index, $element ) {
        if ( $element instanceof Node ) {
            $new_node = $element->set_pos( $index );
        } else {
            $new_node = new Node( $element, $index );
        }

        if ( $index === 0 ) {
            // Insert at the beginning when the list is empty
            $this->prepend( $new_node );
        } else if ( $index === $this->length ) {
            $this->append( $new_node );
        } else {
            list( $trav, $no_cycles, $direction ) = $this->cal_trav_pos( $index );

            for ( $i = 0; $i < $no_cycles; $i++ ) {
                $trav = $trav->$direction;
            }
            
            if ( $trav ) {
                // Insert in the middle of the list
                $new_node->set_prev( $trav->prev );
                $new_node->set_next( $trav );
                
                $trav->prev->set_next( $new_node );
                $trav->set_prev( $new_node );
            } else {
               // Insert at the end when the index is beyond the list length
               $new_node->set_prev( $this->tail );
               $this->tail->set_next( $new_node );
               $this->tail = $new_node;
            }

            $this->length++;
        }

        return $this->get( $index ) === $new_node;
    }

    /**
     *  Get the node at the given index.
     * 
     *  @param int $index The index to get the node from. If negative, starts at the end.
     *  @param mixed $default The default value to return if no node is found
     * 
     *  @return mixed|Node The node at the given index or the default value if no node is found
     */
    public function get( $index, $default = null, $as_data = false )
    {
        // reset the index if negative
        if ( $index < 0 ) {
            $index = $this->length + $index;
        }

        if ( $index >= $this->length || $index < 0 ) {
            return $default;
        }

        // return the head of the list if index = 0, or the tail if index = $this->length - 1
        if ( $index == 0 ) {
            return $this->head;
        } else if ( $index == $this->length - 1 ) {
            return $this->tail;
        }

        // for any other value of the index, traverse the list to get the node at the index
        list( $trav, $no_cycles, $direction ) = $this->cal_trav_pos( $index );
        
        // traverse the list until the given index is reached
        for ( $i = 0; $i < $no_cycles; $i++ ) {
            $trav = $trav->$direction;
        }

        return $as_data ? $trav->data : $trav;
    }

    /**
     *  Find the index of the node with the given data.
     * 
     *  @param mixed $data The data to search for
     *  @param bool $strict true by default. Determine if comparison is strict or not.
     * 
     *  @return int the index of the node with the given data, or -1 if data is not found
     */
    public function find( $data, $strict = true )
    {
        if ( $data === null ) {
            return -1;
        }

        $trav = $this->head;

        // traverse the nodes in the list to find the node with the given data
        while ( $trav ) {
            $is_equal = $strict ? $trav->data === $data : $trav->data == $data;

            if ( $is_equal ) {
                return $trav->pos;
            }

            $trav = $trav->next;
        }

        return -1;
    }

    /**
     *  Removes a node from the end of the list.
     * 
     *  @return mixed the data associated with the node
     */
    public function pop()
    {
        if ( $this->length == 0 ) {
            return null;
        }

        // get the node at the end of the list
        $last = $this->get(-1);
        // set the tail of the list to its prev node
        $this->tail = $this->tail->prev;
        
        // set the next node of the tail to null
        if ( $this->tail ) {
            $this->tail->next = null;
        } else {
            $this->head = null;
        }

        // decrement the length of the list
        $this->length--;

        return $last->data;
    }

    public function shift()
    {
        if ( $this->length == 0 ) {
            return null;
        }

        // get the node at the start of the list
        $first = $this->get(0);
        // set the head of the list to its next node
        $this->head = $this->head->next;

        if ( $this->head ) {
            $this->head->prev = null;
        } else {
            $this->tail = null;
        }

        // decrement the length of the list
        $this->length--;

        return $first->data;
    }

    /**
     *  Check if the list is empty
     * 
     *  @return bool true if the list is empty, false otherwise
     */
    public function isEmpty()
    {
        return $this->length == 0;
    }

    /**
     *  Remove the first node from the list that has the given data.
     * 
     *  @param string $data The data to search node by
     * 
     *  @return bool true if the node is removed successfully, false otherwise
     */
    public function remove( $data )
    {
        if ( $this->length == 0 || $data === null ) {
            return false;
        }

        $trav = $this->head;

        while ( $trav ) {
            if ( $trav->data === $data ) {
                // if it is the last node in the list
                if ( ! $trav->next ) {
                    $this->tail = $trav->prev;

                    if ( $this->tail ) {
                        $this->tail->set_next(null);
                    }
                }
                
                // if it is the first node in the list
                if ( ! $trav->prev ) {
                    $this->head = $trav->next;

                    if ( $this->head ) {
                        $this->head->set_prev(null);
                    }
                }

                // if it is not the only node in the list
                if ( $trav->next && $trav->prev ) {
                    $trav->next->set_prev( $trav->prev );
                    $trav->prev->set_next( $trav->next );
                }

                // decrement the length of the list
                $this->length--;

                return true;
            }

            $trav = $trav->next;
        }

        return false;
    }

    /**
     *  Remove a node from the list at the given index.
     * 
     *  @param int $index The index of the node to remove
     * 
     *  @return bool true if the node was removed, false otherwise
     */
    public function remove_at( $index )
    {
        // get the node at the given index
        $node = $this->get( $index );
        if ( ! $node ) {
            return false;
        }

        // the node is the head of the list if its prev pointer is null
        if ( ! $node->prev ) {
            $this->head = $node->next;

            if ( $this->head ) {
                $this->head->prev = null;
            }
        }

        // the node is the tail of the list if its next pointer is null
        if ( ! $node->next ) {
            $this->tail = $node->prev;

            if ( $this->tail ) {
                $this->tail->next = null;
            }
        }

        // if the node has next and prev pointers
        if ( $node->next && $node->prev ) {
            $node->next->prev = $node->prev;
            $node->prev->next = $node->next;
        }

        // decrement the length of the list
        $this->length--;

        return true;
    }

    /**
     *  Remove all nodes from the list.
     * 
     *  @return void
     */
    public function clear()
    {
        // set both head and tail pointers to null to remove all nodes from the list
        $this->head = null;
        $this->tail = null;

        // set the length of the list to 0
        $this->length = 0;
    }

    /**
     *  Check if the list contains an element
     * 
     *  @param mixed $element The element to check for
     * 
     *  @return bool true if the list contains the element, false otherwise
     */
    public function contains( $element )
    {
        $data = $element instanceof Node ? $element->data : $element;

        // get the index of the element if it exists
        $index = $this->find( $data, false );

        return $index === -1 ? false : true;
    }

    /**
     *  Jsonify the list.
     * 
     * @return string the JSON representation of the list
     */
    public function jsonify()
    {
        $nodes = [];
        $trav = $this->head;

        while ( $trav ) {
            $nodes[] = $trav->to_json();
            $trav = $trav->next;
        }

        return '[' . implode( ', ', $nodes ) . ']';
    }

    /**
     *  Shuffle the nodes in the list.
     * 
     *  Note: This method is a modifier because it changes the order of nodes in the list.
     * 
     *  @return LinkedNodes the list of nodes shuffled.
     */
    public function shuffle()
    {
        $nodes = []; // an array to store the nodes' data
        $trav = $this->head;

        // traverse the list and add each node's data into the $nodes array
        while ( $trav ) {
            $nodes[] = $trav->data;
            $trav = $trav->next;
        }

        // randomly shuffle the elements in the array
        shuffle( $nodes );

        // clear the nodes in the list
        $this->clear();

        // Rebuild list using shuffled array elements
        foreach ( $nodes as $node ) {
            $this->append( $node );
        }

        return $this;
    }

    /**
     *  Copy the nodes from this list into a new list.
     * 
     *  @return LinkedNodes the newly created list with a copy of the nodes in this list
     */
    public function copy()
    {
        return new LinkedNodes( $this->to_array() );
    }

    /**
     *  Join all the nodes' data with a separator string
     * 
     *  @param string $separator The separator string to join with
     * 
     *  @return string The string containing all the nodes' data separated by separator.
     */
    public function join( $separator = ' ' )
    {
        $result = '';
        $trav = $this->head;

        while ( $trav ) {
            // json encode the data before concatenating
            $result .= json_encode($trav->data);

            // move to the next node
            $trav = $trav->next;

            // add the separator if trav is not null
            if ( $trav ) {
                $result .= $separator;
            }
        }

        return $result;
    }

    /**
     *  Maps a callback function to each node in the list
     * 
     *  @param callable $callback  callback function to execute on each node
     * 
     *  @return LinkedNodes a new list of nodes where each node has been mapped to the callback function.
     *  
     *  If callback is not a function, returns the current list unchanged.
     */
    public function map( $callback )
    {
        $new_list = new LinkedNodes();

        if ( ! is_callable( $callback ) ) {
            return $this;
        }

        $trav = $this->head;
        while ( $trav ) {
            $new_list->append( $callback( $trav->data ) );
            $trav = $trav->next;
        }

        return $new_list;
    }

    /**
     *  Create a new LinkedNodes containing only the elements that pass a certain test defined by
     *  the callback function.
     * 
     *  @param callable $callback
     * 
     *  @return LinkedNodes a new list containing only the nodes that pass a certain test defined,
     *  else empty list
     */
    public function filter( $callback )
    {
        $new_list = new LinkedNodes();

        $trav = $this->head;

        if ( is_callable( $callback ) ) {
            while ( $trav ) {
                if ( $callback($trav) ) {
                    $new_list->append( $trav->data );
                }
            }
        }

        return $new_list;
    }

    /**
     *  Reduce the list to a single value using the callback function that accumulates values
     *  successively.
     * 
     *  @param callable $callback The callback function to be called to reduce the list
     *  @param mixed $initial The initial or default value to start with
     * 
     *  @return mixed The accumulated value from the reduction process
     */
    public function reduce( $callback, $initial = null )
    {
        $accumulator = $initial;
        $trav = $this->head;

        while ( $trav ) {
            $accumulator = $callback( $accumulator, $trav->data );
            $trav = $trav->next;
        }

        return $accumulator;
    }

    /**
     *  Create an iterator object that can be used in loops to iterate over the list items
     * 
     *  @return \Generator
     */
    public function getIterator()
    {
        return $this->iterate();
    }

    /**
     *  A generator function that traverses the list of elements and returns their data.
     *  @access private
     * 
     * @return \Generator
     */
    private function iterate()
    {
        $trav = $this->head;

        while ( $trav ) {
            // yield the node's data
            yield $trav->data;
            // move to the next node
            $trav = $trav->next;
        }
    }

    /**
     *  Extend the list with a given array of items (or list of nodes)
     * 
     *  @param array|LinkedNodes $elements List of nodes or array of items to extend the list with
     * 
     *  @return LinkedNodes the list extended.
     */
    public function extend( $elements, $prepend = false )
    {
        foreach ( $elements as $element ) {
            if ( $prepend ) {
                $this->prepend( $element );
            } else {
                $this->append( $element );
            }
        }

        return $this;
    }

    /**
     *  Create a new LinkedNodes containing a portion of the original list,
     *  starting from the given index and with an optional specified length.
     * 
     *  @param int $start The starting index of the slice
     *  @param int $length The length of the new portion to slice
     * 
     *  @return LinkedNodes A new LinkedNodes containing the sliced portion 
     */
    public function slice($start, $length = -1)
    {
        $sliced_portion = new LinkedNodes();
    
        // Get the node at the start position
        $node = $this->get( $start );
    
        for ( $i = 0; $node && ($i < $length || $length === -1); $i++ ) {
            $sliced_portion->append( $node->data );
    
            // Update node pointer
            $node = $node->next;
        }
    
        return $sliced_portion;
    }

    /**
     *  Create a new LinkedNodes containing only the unique elements from the original list.
     * 
     *  @return LinkedNodes A new LinkedNodes containing only the unique elements.
     */
    public function unique()
    {
        $unique_elements = [];
        $new_list = new LinkedNodes();

        foreach ( $this as $node ) {
            $data = $node->data;

            if ( ! isset( $unique_elements[$data] ) ) {
                $unique_elements[$data] = true;
                $new_list->append($data);
            }
        }

        return $new_list;
    }

    /**
     *  Sort the elements in asc/desc order. Optionally, a custom comparator function can
     *  be provided.
     * 
     *  @param callable|null $comparator=null Optional comparator function to use for sorting
     *  @param bool $desc=false whether to sort by descending or ascending
     * 
     *  @return LinkedNodes A current sorted list.
     */
    public function sort( $comparator = null, $desc = false )
    {
        // convert the list into a regular array for sorting
        $elements = $this->to_array();

        // Sort the array using the specified comparator or the default
        if ( is_callable( $comparator ) ) {
            usort( $elements, $comparator );
        } else {
            sort( $elements );
        }

        // Reverse the array if sorting is in descending order
        if ( $desc ) {
            $elements = array_reverse( $elements );
        }

        // reconstruct the sorted list from the sorted array
        $this->clear(); // Clear the existing list

        // Reconstruct the linked list from the sorted array
        foreach ( $elements as $element ) {
            $this->append( $element );
        }

        return $this;
    }

    /**
     *  Converts the list into a JSON string of array of nodes.
     * 
     *  @return string the JSON representation of the list
     */
    public function to_json()
    {
        $nodes = [];
        $trav = $this->head;

        while ( $trav ) {
            $nodes[$trav->label] = json_decode( $trav->to_json(), true );
            $trav = $trav->next;
        }

        return json_encode( $nodes );
    }

    /**
     *  Reverse the data order in the list, modifying the original list.
     * 
     *  This method is different from the reverse() method in that it does not reverse the nodes
     *  in the list, only the data in the nodes.
     * 
     *  @param callable|null $callable Optional callback function to call on each data
     * 
     *  @return LinkedNodes the reversed list of nodes.
     */
    public function flip( $callback = null )
    {
        $elements = $this->to_array();
        $trav = $this->head;

        // reverse the nodes in the current list.
        while ( $trav ) {
            $last_element = array_pop( $elements );

            // call the callback function if it exists
            if ( is_callable($callback) ) {
                $last_element = $callback( $last_element );
            }

            // reverse the order of elements
            $trav->set_data( $last_element );
            $trav = $trav->next;
        }

        return $this;
    }

    /**
     *  Reverse the order of elements in the list, modifying the original list
     * 
     *  @return LinkedNodes the reversed list of nodes.
     */
    public function reverse()
    {
        // list is empty or has one node only
        if ( ! $this->head || $this->head === $this->tail ) {
            return $this;
        }

        $current = $this->head;
        $prev = null;

        // reverse the links between the nodes
        while ( $current ) {
            // swap next and prev pointers for the current node
            $next = $current->next;
            $current->set_next( $prev );
            $current->set_prev( $next );

            // move to the next pointer in the original order
            $prev = $current;
            $current = $next;
        }

        // swap the head and tail pointers
        $temp = $this->head;
        $this->head = $this->tail;
        $this->tail = $temp;

        return $this;
    }
}
