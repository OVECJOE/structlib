<?php
declare(strict_types=1);

namespace Structlib\Helpers;

/**
 * @author OVECJOE <ovecjoe123@gmail.com>
 * @file CodeAnalyser.php: Implements an interface for analysing classes and functions
 */

class CodeAnalyser
{
    const VERSION = '1.0.1';

    const FLAGS = [
        'FUNC' => 1,
        'CLASS' => 1,
        'METHOD' => 2
    ];
    public $analyser;
    private $last_execution_result; // most current execution result

    /**
     *  Create a new CodeAnalyser instance for the given class, method, or function
     * 
     *  @param string $class_name The name of the class (to analyse if no other arguments are provided)
     *  @param string $method_name The name of the method (to analyse if class name is provided)
     *  @param string $function_name The name of the function (to analyse if no other arguments are provided)
     * 
     *  @throws \InvalidArgumentException if none of the arguments are provided
     * 
     *  @return void
     */
    public function __construct( string $class_name = '', string $method_name = '', string $function_name = '' ) {
        // check if it is a class or method that is being analysed
        $flag = (strlen( $class_name ) > 0) + (strlen( $method_name ) > 0);

        switch ( $flag ) {
            case self::FLAGS['CLASS']:
                $this->analyser = new \ReflectionClass( $class_name );
                break;
            case self::FLAGS['METHOD']:
                $this->analyser = new \ReflectionMethod( $class_name, $method_name );
                break;
            default:
                $flag = $flag + (strlen( $function_name ) > 0);

                // check if it is a function that is being analysed
                if ( $flag === self::FLAGS['FUNC'] ) {
                    $this->analyser = new \ReflectionFunction( $function_name );
                } else {
                    throw new \InvalidArgumentException('Invalid arguments');
                }

                break;
        }
    }

    /**
     *  Get the result of the method execution on the active analyser using the method name
     * 
     *  @param string $name The name of the method to call
     *  @param array $args The arguments to pass to the method call
     * 
     *  @return mixed The result of the method call, or null otherwise
     */
    private function get( string $name, ...$args )
    {
        if ( $name ) {
            if ( is_object( $this->analyser ) && method_exists( $this->analyser, $name ) ) {
                return $this->analyser->$name(...$args);
            }
        }

        return null;
    }

    /**
     *  Get the number of required parameters for a class constructor, method, or function
     * 
     *  @return int The number of required parameters or -1 if the analyser is not available
     */
    public function get_required_params_count()
    {
        if ( $this->analyser instanceof \ReflectionClass ) {
            $class_constructor = $this->analyser->getConstructor();

            if ( $class_constructor ) {
                return $class_constructor->getNumberOfRequiredParameters();
            }
        } else {
            $result = $this->get('getNumberOfRequiredParameters');
    
            if ( ! is_null($result) ) {
                return $result;
            }
        }

        return -1;
    }

    /**
     *  Get the name of the class, method, or function depending on the active analyser
     * 
     *  @return string The name of the class, method, function, or empty string if the name is not found
     */
    public function get_name(): string
    {
        return $this->get('getName') ?? '';
    }

    /**
     *  Get the short name of the class, method, or function the active analyser
     *  points to.
     * 
     *  @return string The short name of the class, method, or function,
     *  which is the name without the namespace
     */
    public function get_shortname(): string
    {
        return $this->get('getShortName') ?? '';
    }

    /**
     *  Get the namespace name of the class, method, or function being analysed.
     * 
     *  @return string The namespace name of the class, method, or function.
     */
    public function get_ns_name(): string
    {
        return $this->get('getNamespaceName') ?? '';
    }

    /**
     *  Check if the class or function is globally defined.
     * 
     *  @return bool True if the class or function is globally defined, false otherwise.
     */
    public function is_global(): bool
    {
        if ( $this->analyser instanceof \ReflectionClass ) {
            return class_exists( '\\' . $this->get_shortname() );
        }

        if ( $this->analyser instanceof \ReflectionFunction ) {
            return function_exists( '\\' . $this->get_shortname() );
        }

        return false;
    }

    /**
     *  Check if the class, method, or function the active analyser wraps is user defined
     * 
     *  @return bool True if the class, method, or function is user defined, false otherwise.
     */
    public function is_user_defined(): bool
    {
        return (bool) $this->get('isUserDefined');
    }

    /**
     *  Check that the function is anonymous if analyser is a function
     * 
     *  @return bool true if the function is anonymous, false otherwise.
     */
    public function is_anonymous(): bool
    {
        return (bool) $this->get('isAnonymous');
    }

    /**
     *  Get the file path of the function, method, or class
     * 
     *  @return string the absolute path to the file containing the function, method, or class
     */
    public function get_file_path(): string
    {
        return $this->get('getFileName') ?? '';
    }

    /**
     *  Get the start and end line of the snippet of the function, method, or
     *  class implementation from its file.
     * 
     *  @return array [start, end] if both exists, else empty array
     */
    public function get_lines(): array
    {
        $start_line = $this->get('getStartLine');
        $end_line = $this->get('getEndLine');

        if ( $start_line && $end_line ) {
            return [ $start_line, $end_line ];
        }

        return [];
    }

    /**
     *  Check if the function, method, or class is abtract.
     * 
     *  @return bool true if the function, method, or class is abtract, false otherwise
     */
    public function is_abstract(): bool
    {
        return (bool) $this->get('isAbstract');
    }

    /**
     *  Check if the function, method, or class is final.
     * 
     *  @return bool true if the function, method, or class is final, false otherwise
     */
    public function is_final(): bool
    {
        return (bool) $this->get('isFinal');
    }

    /**
     *  Checks if the function or method is deprecated
     * 
     *  @return bool true if the function or method is deprecated, false otherwise
     */
    public function is_deprecated(): bool
    {
        return (bool) $this->get( 'isDeprecated' );
    }

    /**
     *  Get information about the parameters of the function, method, or class constructor
     * 
     *  @return array[] An array containing an associative array of parameters information.
     */
    public function get_params_metadata(): array
    {
        if ( $this->analyser instanceof \ReflectionClass ) {
            $params = $this->analyser->getConstructor()->getParameters();
        } else {
            $params = $this->get('getParameters') ?? [];
        }

        return array_map( function ( $param ) {
            return [
                'name' => $param->getName(),
                'type' => $param->getType(),
                'pos' => $param->getPosition(),
                'default' => $param->getDefaultValue(),
            ];
        }, $params );
    }

    /**
     *  Get the visibility of a function, method, or class.
     * 
     *  @return string the visibility of the function, method, or class
     */
    public function get_visibility(): string
    {
        if ( ! $this->analyser instanceof \ReflectionFunction ) {
            if ( $this->get('isProtected') ) {
                return 'protected';
            } else if ( $this->get('isPrivate') ) {
                return 'private';
            }
        }

        return 'public';
    }

    /**
     *  Get traits used for a class instance
     * 
     *  @return array|null traits used by a class, or null if no traits
     */
    public function get_traits(): array|null
    {
        if ( $this->analyser instanceof \ReflectionClass ) {
            $traits = $this->analyser->getTraits();

            return array_map(function ($trait) {
                return [
                    'name' => $trait->getName(),
                    'constants' => $trait->getConstants(),
                    'methods' => $trait->getMethods(),
                    'in_ns' => $trait->inNamespace(),
                    'properties' => $trait->getProperties(),
                    'file_name' => $trait->getFileName()
                ];
            }, $traits);
        }

        return null;
    }

    /**
     *  Calculate cyclomatic complexity. 
     * 
     *  Cyclomatic complexity is calculated by counting the number of decision points
     *  (branches) in the code, such as if statements, loops (for, while, do-while),
     *  switch statements, and logical operators (&&, ||, ? :).
     * 
     *  @throws \Exception if there is an error while calculating the cyclomatic complexity
     * 
     *  @param int the cyclomatic complexity or -1 if not successful
     */
    public function cal_cyclomatic_comp(): int
    {
        // get the number of lines of code
        $lines = $this->get_lines();

        if ( ! empty( $lines ) ) {
            // get file name
            $fileName = $this->get_file_path();
            // get the content of the file
            $fileContent = file( $fileName, FILE_IGNORE_NEW_LINES );

            if ( ! $fileContent ) {
                throw new \Exception("Error reading file: $fileName");
            }

            // get function/method code
            $code = array_slice( $fileContent, $lines[0] - 1, (int)($lines[1] - $lines[0] + 1) );
            // convert $code to string
            $code = implode( "\n", $code );

            // tokenize the code
            $tokens = token_get_all( "<?php\n" . $code . "\n", TOKEN_PARSE );
            
            // defaults to 1 signifying the function/method entry point
            $complexity = 1;
            
            $decisionPoints = [
                T_IF, T_ELSEIF, T_ELSE, T_SWITCH,
                T_CASE, T_DEFAULT, T_WHILE, T_DO,
                T_FOR, T_FOREACH, T_CATCH, T_BOOLEAN_AND,
                T_BOOLEAN_OR
            ];
            
            foreach ( $tokens as $token ) {
                if ( is_array( $token ) && in_array( $token[0], $decisionPoints ) ) {
                    $complexity++;
                }
            }

            return $complexity;
        }

        return -1;
    }

    /** 
     *  Execute a method of the class, the function, or the method for a given instance
     * 
     *  @param array $args the arguments to be passed to the function/method
     *  @param object|null $instance a class instance if a method analyser.
     *  @param string $methodName the name of the method if analyser is available for class
     * 
     *  @return mixed the result of the execution of the function/method call
     */
    public function execute( array $args = [], object|null $instance, string $methodName = '' )
    {
        if ( gettype( $args ) !== 'array' ) {
            throw new \InvalidArgumentException('Invalid argument type');
        }

        if ( $this->analyser instanceof \ReflectionClass ) {
            if ( ! $instance ) {
                throw new \InvalidArgumentException('Instance cannot be null');
            }

            if ( ! method_exists( $instance, $methodName ) ) {
                throw new \InvalidArgumentException("Method '$methodName' does not exist");
            }

            return $instance->$methodName( ...$args );
        } else if ( $this->analyser instanceof \ReflectionMethod ) {
            if ( ! $instance ) {
                throw new \InvalidArgumentException('Instance cannot be null');
            }

            return $this->analyser->invoke( $instance, ...$args );
        } else if ( $this->analyser instanceof \ReflectionFunction ) {
            return $this->analyser->invoke( ...$args );
        }

        return null;
    }

    /**
     *  Calculate the execution time of a class, method, or function
     * 
     *  @param object|null $instance class instance if class/method analyser is active
     *  @param string methodName method name to execute if class analyser is active
     *  @param array ...$args the arguments to pass to the function or method
     * 
     *  @return float in seconds
     */
    public function get_exec_time( $instance = null, $methodName = '', ...$args )
    {
        // record the time before execution
        $startTime = microtime( true );

        // execute the function, method, or class
        $this->last_execution_result = $this->execute( $args, $instance, $methodName );

        // record the time after execution
        $endTime = microtime( true );

        return ($endTime - $startTime) * 1e3;
    }

    /**
     *  Get memory usage of the execution of the class, method, or function in bytes.
     * 
     *  @param object|null $instance class instance if class/method analyser is active
     *  @param string methodName method name to execute if class analyser is active
     *  @param array ...$args the arguments to pass to the function or method
     * 
     *  @return int memory used in bytes
     */
    public function get_memory_usage( $instance = null, $methodName = '', ...$args )
    {
        // backup the current memory usage
        $initialUsage = memory_get_usage();

        // execute the function, method, or class
        $this->last_execution_result = $this->execute( $args, $instance, $methodName );

        // get the memory usage after execution
        $finalUsage = memory_get_usage();

        return $finalUsage - $initialUsage;
    }
}
