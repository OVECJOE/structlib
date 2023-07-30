<?php
declare(strict_types=1);

/**
 * @author OVECJOE <ovecjoe123@gmail.com>
 * @file LinkedNodesTest.php: Contains tests for the Node class
 */

use PHPUnit\Framework\TestCase;
use Structlib\Helpers\CodeAnalyser;
use Structlib\Structures\LinkedNodes;
use Structlib\Types\Base\Node;

class LinkedNodesTest extends TestCase
{
    private static $code_analyser;
    private $nodes_list;

    public static function setUpBeforeClass(): void
    {
        self::$code_analyser = CodeAnalyser::analyseClass(LinkedNodes::class);
    }

    public function setUp(): void
    {
        $this->nodes_list = new LinkedNodes();
    }

    public function test_constructor_can_take_infinite_num_of_args()
    {
        $analyser = self::$code_analyser->getAnalyser();
        // check if constructor can take an infinite number of args
        $is_variadic = $analyser->getConstructor()->isVariadic();

        $this->assertTrue( $is_variadic );
    }

    public function test_length_is_updated_when_the_list_is_modified()
    {
        $items = [1, 'two', true, ['foo' => 'bar']];
        
        // append each item to the list
        foreach ( $items as $item ) {
            $this->nodes_list->append( $item );
        }

        // assert that the length of the list equals the number of items added
        $this->assertEquals( count($items), $this->nodes_list->length );

        // remove the first item from the list
        $this->nodes_list->remove( $items[0] );
        // assert that the length of the list has decreased by 1
        $this->assertEquals( count($items) - 1, $this->nodes_list->length );

        // remove the last item from the list
        $this->nodes_list->remove( $items[2] );
        // assert that the length of the list has decreased by 1
        $this->assertEquals( count($items) - 2, $this->nodes_list->length );
    }

    public function test_append_method_adds_a_node_to_list()
    {
        // create a new node
        $node = new Node( ['foo' => 'bar'], 0 );

        // append the node to the list
        $this->nodes_list->append( $node );

        $this->assertEquals( 1, $this->nodes_list->length );
    }

    public function test_append_method_adds_non_node_to_list()
    {
        // append a non-node to the list
        $this->nodes_list->append( ['foo' => 'bar'] );

        $this->assertEquals( 1, $this->nodes_list->length );
    }

    public function test_append_method_returns_the_list()
    {
        $new_list = $this->nodes_list->append( 'Pretty Face' );
        $this->assertSame( $new_list, $this->nodes_list );
    }

    public function test_append_method_sets_the_list_head_if_the_list_is_empty()
    {
        $this->assertEquals( null, $this->nodes_list->head );

        // create a new node
        $node = new Node( 'Pretty Face', 0 );

        // append the node to the list
        $this->nodes_list->append( $node );

        $this->assertSame( $node, $this->nodes_list->head );
    }

    public function test_append_method_always_update_the_list_tail_after_adding()
    {
        $items = [1, 'two', true, ['foo' => 'bar']];

        foreach ( $items as $item ) {
            $this->nodes_list->append( $item );
        }

        $this->assertEquals( $items[3], $this->nodes_list->tail->data );
    }

    public function test_prepend_method_add_node_to_start_of_list()
    {
        $numbers = [ 1, 2, 3, 4, 5, 6, 7, 8, 9 ];

        foreach ( $numbers as $number ) {
            $this->nodes_list->prepend( $number );
        }

        $this->assertEquals( $numbers[count($numbers) - 1], $this->nodes_list->head->data );
    }

    public function test_prepend_method_update_list_length()
    {
        // prepend an element into the list
        $this->nodes_list->prepend(12);

        $this->assertEquals( 1, $this->nodes_list->length );
    }

    public function test_prepend_method_returns_the_list()
    {
        $new_list = $this->nodes_list->prepend( 'Pretty Face' );
        $this->assertSame( $new_list, $this->nodes_list );
    }

    public function test_insert_method_insert_element_at_the_given_index()
    {
        /* ===== insert element to the beginning of the list ===== */
        $element = [ 1, 2, 3, 4, 5, 6, 7 ];
        $this->nodes_list->insert( 0, $element );

        $this->assertEquals( $this->nodes_list->head->data, $element );

        /* ===== insert element to the end of the list ===== */
        $element = 'Happy Hour';
        $this->nodes_list->insert( $this->nodes_list->length, $element );

        $this->assertEquals( $this->nodes_list->tail->data, $element );
        $this->assertNotEquals( $this->nodes_list->head->data, $element );

        /* ===== insert element to any index other than the start or end of the list ===== */
        $element = new Node( 'Pretty Face', 2 );
        $this->nodes_list->insert( 1, $element );

        $this->assertTrue( $this->nodes_list->contains( $element->data ) );
        $this->assertNotEquals( $this->nodes_list->tail->data, $element );
        $this->assertNotEquals( $this->nodes_list->head->data, $element );
    }

    public function test_insert_method_returns_true_when_element_is_inserted()
    {
        $inserted = $this->nodes_list->insert( $this->nodes_list->length, 'Pretty Face' );
        $this->assertTrue( $inserted );
    }

    public function test_insert_method_runs_in_logarithmic_time()
    {
        foreach ( range( 1, 1000 ) as $item ) {
            $this->nodes_list->append( [$item, $item * 2, $item * 3] );
        }

        // implement an alternative function for the insertion
        function altInsert ( $nodes_list, $index, $element ) {
            $new_node = new Node( $element, $index );

            if ( $index === 0 ) {
                $nodes_list->prepend( $new_node );
            } else if ( $index === $nodes_list->length ) {
                $nodes_list->append( $new_node );
            } else {
                $trav = $nodes_list->head;
                $pos = 0;

                while ( $trav && $pos !== $index ) {
                    $trav = $trav->next;
                    $pos++;
                }

                if ( $trav ) {
                    // Insert in the middle of the list
                    $new_node->setPrev( $trav->prev );
                    $new_node->setNext( $trav );
                    
                    $trav->prev->setNext( $new_node );
                    $trav->setPrev( $new_node );
                } else {
                   // Insert at the end when the index is beyond the list length
                   $new_node->setPrev( $nodes_list->tail );
                   $nodes_list->tail->setNext( $new_node );
                   $nodes_list->tail = $new_node;
                }

                $nodes_list->length++;
            }

            return $nodes_list->get( $index ) === $new_node;
        };
        
        // create a func and method analysers
        $funcAnalyser = CodeAnalyser::analyseFunc( 'altInsert' );
        $analyser = CodeAnalyser::analyseMethod( LinkedNodes::class, 'insert' );
        
        // get the time it takes to execute the method and func
        $exec_time = $analyser->getExecutionTime( $this->nodes_list, 'insert', 600, 'Pretty Face' );
        $exec_func_time = $funcAnalyser->getExecutionTime( null, '', $this->nodes_list, 600, 'Pretty Func Face' );

        $this->assertGreaterThan( 1, $exec_func_time / $exec_time );
    }
}