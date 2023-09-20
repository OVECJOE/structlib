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
        $this->set_type();
        $this->set_size();

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

    /**
     *  Set the next node
     * 
     *  @param Node|null $next
     */
    public function set_next( $next )
    {
        if ( ! $next instanceof Node && $next !== null ) {
            throw new \InvalidArgumentException('$next must be null or a Node instance');
        }

        $this->next = $next;
    }

    /**
     *  Set the previous node
     * 
     *  @param Node|null $prev
     */
    public function set_prev( $prev )
    {
        if ( ! $prev instanceof Node && $prev !== null ) {
            throw new \InvalidArgumentException('$prev must be null or a Node instance');
        }

        $this->prev = $prev;
    }

    /**
     *  Set the data of the node
     * 
     *  @param mixed $data
     */
    public function set_data( $data )
    {
        if ( $data instanceof Node ) {
            throw new \InvalidArgumentException('$data cannot be a Node instance');
        }

        $this->data = $data;
        // set the new data type and size
        $this->set_type();
        $this->set_size();
    }

    /**
     *  Sets the position of the node.
     * 
     *  @param int $position
     * 
     *  @return Node
     */
    public function set_pos( $position )
    {
        if ( gettype( $position ) !== 'integer' ) {
            throw new \InvalidArgumentException('Position must be an integer');
        }

        $this->pos = $position;
        return $this;
    }

    /**
     *  Set the type of the node's data
     * 
     *  @return void
     */
    private function set_type()
    {
        $this->type = gettype($this->data);
    }

    /**
     *  Set the size of the data stored in the node
     * 
     *  @return void
     */
    private function set_size()
    {
        $this->size = is_countable($this->data) ? count($this->data) : 1;
    }

    /**
     *  jsonify the node
     * 
     *  @return string|bool the json string representation of the node, or false on failure
     */
    public function to_json()
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
