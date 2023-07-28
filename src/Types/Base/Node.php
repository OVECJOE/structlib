<?php
declare(strict_types=1);

namespace Structlib\Types\Base;

/**
 * @author OVECJOE <ovecjoe123@gmail.com>
 * @file Node.php: Represents the node of any data structure
 */

class Node
{
    /**
     * @var mixed
     */
    private $data;

    /**
     * @var string
     */
    private $type;

    /**
     * @var Node|null
     */
    private $next;

    /**
     * @var Node|null
     */
    private $prev;

    /**
     * @var string
     */
    private $label;

    /**
     * @var int
     */
    private $pos;

    /**
     * @var int
     */
    private $size;

    public function __construct( $data, $pos, $prev = null, $next = null )
    {
        if ( $data instanceof Node ) {
            throw new \InvalidArgumentException('$data cannot be a Node instance');
        }

        $this->data = $data;
        $this->pos = $pos;

        // set the type and size of the node
        $this->setType();
        $this->setSize();

        // set label: {type}_{uniqid()}
        $this->label = uniqid( "{$this->type}_" );
    }

    public function __get( $name ) {
        if ( property_exists( $this, $name ) ) {
            return $this->$name;
        }

        throw new \OutOfBoundsException("Property '$name' does not exist");
    }

    public function __clone() {
        // add a new label to the cloned node
        $this->label = uniqid( get_class($this) . "[{$this->type}]::" );
        // set node's position as -1 to indicate it was cloned
        $this->pos = -1;
    }

    public function setIndex( $index )
    {
        if ( gettype($index) !== 'integer' ) {
            throw new \InvalidArgumentException('$index must be an integer');
        }

        if ( $this->pos && $this->pos !== -1 ) {
            throw new \BadMethodCallException('Modifying this node index is not allowed');
        }
        
        $this->pos = $index;
    }

    /**
     *  Set the next node
     * 
     *  @param Node|null $next
     */
    public function setNext( $next )
    {
        if ( ! $next instanceof Node ) {
            throw new \InvalidArgumentException('$next must be a Node instance');
        }

        $this->next = $next;
    }

    /**
     *  Set the previous node
     * 
     *  @param Node|null $prev
     */
    public function setPrev( $prev )
    {
        if ( ! $prev instanceof Node ) {
            throw new \InvalidArgumentException('$prev must be a Node instance');
        }

        $this->prev = $prev;
    }

    /**
     *  Set the data of the node
     * 
     *  @param mixed $data
     */
    public function setData( $data ) {
        if ( $data instanceof Node ) {
            throw new \InvalidArgumentException('$data cannot be a Node instance');
        }

        $this->data = $data;
        // set the new data type and size
        $this->setType();
        $this->setSize();
    }

    /**
     *  Set the type of the node's data
     * 
     *  @return void
     */
    private function setType() {
        $this->type = gettype($this->data);
    }

    /**
     *  Set the size of the data stored in the node
     * 
     *  @return void
     */
    private function setSize() {
        $this->size = is_countable($this->data) ? count($this->data) : 1;
    }

    /**
     *  jsonify the node
     * 
     *  @return string|bool the json string representation of the node, or false on failure
     */
    public function toJSON()
    {
        return json_encode([
            'data' => $this->data,
            'size' => $this->size,
            'label' => $this->label,
            'pos' => $this->pos,
            'type' => $this->type,
        ], JSON_FORCE_OBJECT);
    }
}
