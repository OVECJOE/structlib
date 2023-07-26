<?php

/**
 *  @author OVECJOE <ovecjoe123@gmail.com>
 *  @file NList.php: Implements a robust interface for LinkedList with some additional flavors.
 * 
 *  @declare(strict_types=1);
 */

namespace Structlib\Collections;

use Structlib\Types\Node;

class NList {
    /**
     *  @var int
     *  @access private
     */
    private $length = 0;

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
    private function _calTraversalPosition( $index )
    {
        // get the midpoint of the list length
        $midpoint = floor($this->length / 2);

        // get the starting node for traversal
        $trav = $index <= $midpoint ? $this->head : $this->tail;
        // calculate the number of cycles needed to get to the node of insertion
        $no_cycles = $index <= $midpoint ? $index : $this->length - $index;

        return [
            'trav' => $trav,
            'no_cycles' => $no_cycles,
            'direction' => $index <= $midpoint ? 'next' : 'prev',
        ];
    }

    /**
     *  Add an element to the end of the list.
     *  
     *  Note that this method always creates a new node from the element
     *  data and the new position. If the element was a Node instance with
     *  a valid next or prev node, this next or prev node will be lost.
     * 
     *  See the extend() method if you want to join two NodeList instances together.
     * 
     *  @param mixed $node
     * 
     *  @return NList
     */
    public function append( $node )
    {
        if ( ! $node instanceof Node ) {
            $node = new Node( $node, $this->length );
        } else {
            $node = new Node( $node->data, $this->length );
        }

        if ( ! $this->head ) {
            $this->head = $node;
            $this->tail = $node;
        } else {
            // set the tail's next to the node
            $this->tail->setNext( $node );
            // set the node's prev to the tail
            $node->setPrev( $this->tail );
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
     *  Note that this method always creates a new node from the element
     *  data and the new position. If the element was a Node instance with
     *  a valid next or prev node, this next or prev node will be lost.
     * 
     *  See the extend() method if you want to join two NodeList instances together.
     * 
     *  @param mixed $node
     * 
     *  @return NList
     */
    public function prepend( $node )
    {
        if ( ! $node instanceof Node ) {
            $node = new Node( $node, 0 );
        } else {
            $node = new Node( $node->data, 0 );
        }

        if ( ! $this->head ) {
            $this->head = $node;
            $this->tail = $node;
        } else {
            // set the head's prev to the node
            $this->head->setPrev( $node );
            // set the node's next to the head
            $node->setNext( $this->head );
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
    public function insert( $index, $element )
    {
        if ( $index >= $this->length || $index < 0 ) {
            return false;
        }

        // if index is equal to last index of the list, insert element at the end,
        // otherwise insert element at the beginning of the list if index = 0
        if ( $index === $this->length - 1 ) {
            $this->append( $element );
        } else if ( $index === 0 ) {
            $this->prepend( $element );
        } else {
            // extract calculated traversal info
            list( $trav, $no_cycles, $direction ) = $this->_calTraversalPosition( $index );
    
            // loop until the position where new node will be inserted
            for ( $i = 0; $i < $no_cycles; $i++ ) {
                $trav = $trav->$direction;
            }

            // create a new node
            if ( $element instanceof Node ) {
                $element = $element->data;
            }
            $node = new Node( $element, $index, $trav, $trav->next );

            // update trav
            $trav->next->prev = $node;
            $trav->next = $node;

            // increment length
            $this->length++;
        }

        return true;
    }

    /**
     *  Get the node at the given index.
     * 
     *  @param int $index The index to get the node from. If negative, starts at the end.
     *  @param mixed $default The default value to return if no node is found
     * 
     *  @return mixed The node at the given index or the default value if no node is found
     */
    public function get( $index, $default = null )
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
        list( $trav, $no_cycles, $direction ) = $this->_calTraversalPosition( $index );
        
        // traverse the list until the given index is reached
        for ( $i = 0; $i < $no_cycles; $i++ ) {
            $trav = $trav->$direction;
        }

        return $trav;
    }

    /**
     *  Get the data of a node at the given index.
     * 
     *  @param int $index The index of the node
     * 
     *  @return mixed The data of the node
    */
    public function data( $index )
    {
        $node = $this->get( $index );
        if ( $node instanceof Node ) {
            return $node->data;
        }

        return $node;
    }

    /**
     *  Find the index of the node with the given data.
     * 
     *  @param mixed $data The data to search for
     *  @param bool $strict true by default. Determine if comparison is strict or not.
     * 
     *  @return int the index of the node with the given data, or -1 if data is not found
     */
    public function index( $data, $strict = true )
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

        return $last->data;
    }
}
