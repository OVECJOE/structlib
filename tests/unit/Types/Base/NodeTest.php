<?php
declare(strict_types=1);

/**
 * @author OVECJOE <ovecjoe123@gmail.com>
 * @file NodeTest.php: Contains tests for the Node class
 */

use PHPUnit\Framework\TestCase;
use Structlib\Types\Base\Node;
use Structlib\Helpers\CodeAnalyser;

class NodeTest extends TestCase
{
    private static $code_analyser;
    private $node;
    private $private_properties = ['data', 'type', 'next', 'prev', 'label', 'pos', 'size'];

    public static function setUpBeforeClass(): void
    {
        self::$code_analyser = CodeAnalyser::analyseClass(Node::class);
    }

    protected function setUp(): void
    {
        $this->node = new Node('Pretty Face', 0);
    }

    public function test_node_can_initialize_with_two_args()
    {
        $no_required_args = self::$code_analyser->getNumberOfRequiredParameters();
        $this->assertEquals(2, $no_required_args);
    }

    public function test_node_constructor_can_take_up_to_four_args()
    {
        $analyser = self::$code_analyser->getAnalyser();
        $no_args = $analyser->getConstructor()->getNumberOfParameters();
        $this->assertEquals(4, $no_args);
    }

    public function test_can_get_node_private_properties()
    {
        $result = [];
        foreach ($this->private_properties as $property) {
            $result[] = $this->node->$property;
        }
        $this->assertCount(count($this->private_properties), $result);
    }

    public function test_can_set_node_data_via_public_method()
    {
        $this->node->setData([]);
        $this->assertIsArray($this->node->data);
    }

    public function test_node_type_and_size_change_when_node_data_changes()
    {
        $type = $this->node->type;
        $size = $this->node->size;
        $this->node->setData(['Pretty', 'Face']);
        $this->assertNotEquals($type, $this->node->type);
        $this->assertNotEquals($size, $this->node->size);
    }

    public function test_constructor_sets_label_with_correct_prefix()
    {
        $prefix = 'string_';
        $node = new Node('Test Data', 0, null, null);
        $this->assertStringStartsWith($prefix, $node->label);
    }

    public function test_clone_does_not_create_same_instance()
    {
        $clonedNode = clone $this->node;
        $this->assertNotSame($this->node, $clonedNode);
    }

    public function test_cloned_node_has_different_label()
    {
        $clonedNode = clone $this->node;
        $this->assertNotEquals($this->node->label, $clonedNode->label);
    }

    public function test_set_pos_updates_position()
    {
        $index = 10;
        $this->node->setPos($index);
        $this->assertEquals($index, $this->node->pos);
    }

    public function test_set_index_throws_exception_for_non_integer_index()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->node->setPos('invalid_index');
    }

    public function test_set_next_updates_next_property()
    {
        $nextNode = new Node('Next Node', 1);
        $this->node->setNext($nextNode);
        $this->assertSame($nextNode, $this->node->next);
    }

    public function test_set_next_throws_exception_for_non_node_argument()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->node->setNext('invalid_next_node');
    }

    public function test_set_prev_updates_prev_property()
    {
        $prevNode = new Node('Previous Node', -1);
        $this->node->setPrev($prevNode);
        $this->assertSame($prevNode, $this->node->prev);
    }

    public function test_set_prev_throws_exception_for_non_node_argument()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->node->setPrev('invalid_prev_node');
    }

    public function test_to_json_returns_valid_json_string()
    {
        $jsonString = $this->node->toJSON();
        $this->assertJson($jsonString);
    }

    public function test_json_representation_contains_all_properties()
    {
        $jsonString = $this->node->toJSON();
        $data = json_decode($jsonString, true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('label', $data);
        $this->assertArrayHasKey('pos', $data);
        $this->assertArrayHasKey('size', $data);
    }

    public function test_json_representation_is_correct()
    {
        $label = $this->node->label;
        $expectedJson = '{"data":"Pretty Face","size":1,"label":"' . $label . '","pos":0,"type":"string"}';

        $this->assertEqualsIgnoringCase($expectedJson, $this->node->toJSON());
    }

    public function test_invalid_data_type_in_constructor_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $node = new Node( new Node([], 0), 0 );
    }

    public function test_invalid_data_type_in_set_data_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->node->setData( new Node(-1, 0) );
    }

    public function test_out_of_bound_property_throws_exception()
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->node->non_existing_property;
    }
}