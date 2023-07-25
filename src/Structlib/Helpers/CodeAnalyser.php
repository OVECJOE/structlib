<?php

/**
 * @author OVECJOE <ovecjoe123@gmail.com>
 * @file CodeAnalyser.php: Implements an interface for analysing classes and functions
 * 
 * @declare(strict_types=1);
 */

namespace Structlib\Helpers;

class CodeAnalyser
{

    private $funcAnalyser;
    private $classAnalyser;
    private $methodAnalyser;

    private $analysers = [ 'funcAnalyser', 'classAnalyser', 'methodAnalyser' ];
    private $currentER; // most current execution result

    private function __construct() {}

    /**
     *  Create a new CodeAnalyser instance for the given class
     * 
     *  @param string $className The name of the class
     * 
     *  @static
     * 
     *  @return CodeAnalyser instance for the class
     */
    public static function analyseClass( string $className ): CodeAnalyser
    {
        $analyser = new self();
        $analyser->classAnalyser = new \ReflectionClass( $className );
        
        return $analyser;
    }

    /**
     *  Create a new CodeAnalyser instance for the given method
     * 
     *  @param string $className The name of the class
     *  @param string $methodName The name of the method
     * 
     *  @static
     * 
     *  @return CodeAnalyser instance for the given method
     */
    public static function analyseMethod( string $className, string $methodName ): CodeAnalyser
    {
        $analyser = new self();
        $analyser->methodAnalyser = new \ReflectionMethod( $className, $methodName );

        return $analyser;
    }

    /**
     *  Create a new CodeAnalyser instance for the given function
     * 
     *  @param string $functionName The name of the function
     * 
     *  @static
     * 
     *  @return CodeAnalyser instance for the function
     */
    public static function analyseFunc( string $functionName ): CodeAnalyser
    {
        $analyser = new self();
        $analyser->funcAnalyser = new \ReflectionFunction( $functionName );

        return $analyser;
    }

    /**
     *  Get the active analyser.
     * 
     *  @return \ReflectionFunction|\ReflectionMethod|\ReflectionClass|null
     */
    public function getActiveAnalyser(): mixed
    {
        foreach ( $this->analysers as $analyser ) {
            if ( $this->$analyser ) {
                return $this->$analyser;
            }
        }

        return null;
    }

    /**
     *  Get a method from the active analyser using the method name
     * 
     *  @param string $name
     * 
     *  @return callable|null The method if found, or null otherwise
     */
    private function get( string $name )
    {
        if ( ! $name ) return null;

        foreach ( $this->analysers as $analyser ) {
            if ( $this->$analyser && method_exists( $this->$analyser, $name ) ) {
                return $this->$analyser->$name;
            }
        }
    }

    /**
     *  Get the name of the class, method, or function depending on the active analyser
     * 
     *  @return string The name of the class, method, or function.
     */
    public function getName(): string
    {
        $name = $this->get( 'getName' );
        return is_callable( $name ) ? $name() : '';
    }

    /**
     *  Get the short name of the class, method, or function the active analyser
     *  points to.
     * 
     *  @return string The short name of the class, method, or function,
     *  which is the name without the namespace
     */
    public function getSName(): string
    {
        $shortName = $this->get( 'getShortName' );
        return is_callable( $shortName ) ? $shortName() : '';
    }

    /**
     *  Get the namespace name of the class, method, or function depending
     *  on the active analyser.
     * 
     *  @return string The namespace name of the class, method, or function.
     */
    public function getns(): string
    {
        $ns_name = $this->get( 'getNamespaceName' );
        return is_callable( $ns_name ) ? $ns_name() : '';
    }

    /**
     *  Check if the class, method, or function is globally defined.
     * 
     *  @return bool True if the class, method, or function is globally defined, false otherwise.
     */
    public function isGlobal(): bool
    {
        return function_exists( '\\' . $this->getSName() );
    }

    /**
     *  Check if the class, method, or function the active analyser wraps is user defined
     * 
     *  @return bool True if the class, method, or function is user defined, false otherwise.
     */
    public function isUserDefined(): bool
    {
        $user_defined = $this->get( 'isUserDefined' );
        return is_callable( $user_defined ) && $user_defined();
    }

    /**
     *  Check if the function is an anonymous function if funcAnalyser is active.
     * 
     *  @return bool true if the function is anonymous, false otherwise.
     */
    public function isAnonymous(): bool
    {
        $is_anonymous = $this->get( 'isAnonymous' );
        return is_callable( $is_anonymous ) && $is_anonymous();
    }

    /**
     *  Get the file path of the function, method, or class
     * 
     *  @return string the absolute path to the file containing the function, method, or class
     */
    public function getFilePath(): string
    {
        $file_path = $this->get( 'getFileName' );
        return is_callable( $file_path ) ? $file_path() : '';
    }

    /**
     *  Get the start and end line of the function, method, or class from the file.
     * 
     *  @return array [start, end] if both exists, else empty array
     */
    public function getLines(): array
    {
        $start_line = $this->get( 'getStartLine' );
        $end_line = $this->get( 'getEndLine' );

        if ( is_callable($start_line) && is_callable($end_line) ) {
            return [ $start_line(), $end_line() ];
        }

        return [];
    }

    /**
     *  Check if the function, method, or class is abtract.
     * 
     *  @return bool true if the function, method, or class is abtract, false otherwise
     */
    public function isAbstract(): bool
    {
        $is_abstract = $this->get( 'isAbstract' );
        return is_callable( $is_abstract ) && $is_abstract();
    }

    /**
     *  Check if the function, method, or class is final.
     * 
     *  @return bool true if the function, method, or class is final, false otherwise
     */
    public function isFinal(): bool
    {
        $is_final = $this->get( 'isFinal' );
        return is_callable( $is_final ) && $is_final();
    }

    /**
     *  Checks if the function or method is deprecated
     * 
     *  @return bool true if the function or method is deprecated, false otherwise
     */
    public function isDeprecated(): bool
    {
        $is_deprecated = $this->get( 'isDeprecated' );
        return is_callable( $is_deprecated ) && $is_deprecated();
    }

    /**
     *  Get information about the parameters of the function, method, or class constructor
     * 
     *  @return array[] An array of associative arrays of parameters information
     */
    public function getParamsInfo(): array
    {
        if ( $this->classAnalyser ) {
            // get the parameters of the class constructor
            $params = $this->classAnalyser->getConstructor()->getParameters();
        } else {
            $params = $this->get( 'getParameters' )();
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
     */
    public function getVisibility(): string
    {
        if ( ! $this->funcAnalyser ) {
            if ( $this->get( 'isProtected' ) ) {
                return 'protected';
            } else if ( $this->get( 'isPrivate' ) ) {
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
    public function getTraits(): array|null
    {
        if ( $this->classAnalyser ) {
            $traits = $this->classAnalyser->getTraits();

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
     *  @param int cyclomatic complexity of the method or function.
     */
    public function calculateCComp(): int
    {
        // get the number of lines of code
        $lines = $this->getLines();

        if ( ! empty( $lines ) ) {
            // get file name
            $fileName = $this->getFilePath();
            // get the content of the file
            $fileContent = file( $fileName );
            
            // get function/method code
            $code = array_slice( $fileContent, $lines[0] - 1, $lines[1] - $lines[0] + 1 );
            // convert $code to string
            $code = implode( "\n", $code );

            // tokenize the code
            $tokens = token_get_all( "<?php\n" . $code . "\n" );
            
            // defaults to 1 signifying the function/method entry point
            $complexity = 1;

            $decisionPoints = [
                T_IF, T_ELSEIF, T_ELSE, T_SWITCH,
                T_CASE, T_DEFAULT, T_WHILE, T_DO,
                T_FOR, T_FOREACH, T_CATCH, T_BOOLEAN_AND,
                T_BOOLEAN_OR
            ];

            foreach ( $tokens as $token ) {
                if ( is_array( $token ) && in_array( $token, $decisionPoints ) ) {
                    $complexity++;
                }
            }

            return $complexity;
        }

        return -1;
    }

    /** 
     *  Execute the function, method, and class
     * 
     *  @param array $args the arguments to be passed to the function/method
     *  @param object|null $instance the class instance if analyser is available for method or class
     *  @param string $methodName the name of the method if analyser is available for class
     * 
     *  @return mixed the result of the execution of the function/method call
     */
    public function execute( array $args = [], object|null $instance, string $methodName = '' )
    {
        $analyser = $this->getActiveAnalyser();

        if ( $analyser instanceof \ReflectionFunction ) {
            return $analyser->invokeArgs( $args );
        } else if ( $analyser instanceof \ReflectionMethod ) {
            return $analyser->invokeArgs( $instance, $args );
        } else if ( $analyser instanceof \ReflectionClass ) {
            if ( method_exists( $instance, $methodName ) ) {
                return $instance->$methodName( $args );
            }
        }
    }

    /**
     *  Calculate the execution time of a class, method, or function
     * 
     *  @param object|null $instance class instance if class/method analyser is active
     *  @param string methodName method name to execute if class analyser is active
     *  @param array ...$args the arguments to pass to the function or method
     * 
     *  @return float in milliseconds
     */
    public function getExecutionTime( $instance = null, $methodName = '', ...$args )
    {
        // record the time before execution
        $startTime = microtime( true );

        // execute the function, method, or class
        $this->currentER = $this->execute( $args, $instance, $methodName );

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
    public function getMemoryUsage( $instance = null, $methodName = '', ...$args )
    {
        // backup the current memory usage
        $initialUsage = memory_get_usage();

        // execute the function, method, or class
        $this->currentER = $this->execute( $args, $instance, $methodName );

        // get the memory usage after execution
        $finalUsage = memory_get_usage();

        return $finalUsage - $initialUsage;
    }
}
