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
     *  Get a method from the active analyser using the method name
     * 
     *  @param string $name
     * 
     *  @return callable|null The method if found, or null otherwise
     */
    private function get( string $name )
    {
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
}