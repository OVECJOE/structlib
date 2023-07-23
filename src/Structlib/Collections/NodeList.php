<?php

/**
 *  @author OVECJOE <ovecjoe123@gmail.com>
 *  @file NodeList.php: Represents the node of any data structure
 *  @declare(strict_types=1);
 */

namespace Structlib\Collections;

use Structlib\Types\Node;

class NodeList {
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
     *  Add an element to the end of the list.
     *  
     *  Note that this method always creates a new node from the element
     *  data and the new position. If the element was a Node instance with
     *  a valid next or prev node, this next or prev node will be lost.
     * 
     *  See the extend() method if you want to join two NodeList instances together.
     * 
     *  @param Node $node
     * 
     *  @return NodeList
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
     *  @param Node $node
     * 
     *  @return NodeList
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

    public function insert( $index, $element )
    {

    }
}
