<?php
namespace ReverseRegex\Generator;

use PHPStats\Generator\GeneratorInterface;
use ReverseRegex\Exception as GeneratorException;

/**
  *  Scope for Lookahead Assertions
  *
  *  This generator stores lookahead requirements and ensures
  *  the final generated string satisfies all lookahead constraints.
  *
  *  @author Generated for reverse-regex
  *  @since 0.0.1
  */
class LookaheadScope extends Scope
{
    /**
      *  @var array Collection of lookahead constraints
      */
    protected $constraints = [];

    /**
      *  @var boolean Is this a positive lookahead (true) or negative (false)
      */
    protected $isPositive = true;


    /**
      *  Class Constructor
      *
      *  @access public
      *  @param boolean $isPositive true for (?=), false for (?!)
      */
    public function __construct($isPositive = true)
    {
        parent::__construct('lookahead');
        $this->isPositive = $isPositive;
    }

    /**
      *  Add a constraint pattern that must be satisfied
      *
      *  @access public
      *  @param Scope $constraint The pattern scope to check
      */
    public function addConstraint(Scope $constraint)
    {
        $this->constraints[] = $constraint;
    }

    /**
      *  Get all constraints
      *
      *  @access public
      *  @return array
      */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
      *  Check if this is a positive lookahead
      *
      *  @access public
      *  @return boolean
      */
    public function isPositive()
    {
        return $this->isPositive;
    }

    /**
      *  Generate method - lookaheads don't generate text themselves,
      *  they add constraints to be satisfied later
      *
      *  @access public
      *  @param string $result
      *  @param GeneratorInterface $generator
      */
    public function generate(&$result, GeneratorInterface $generator)
    {
        // Lookaheads are zero-width assertions and don't directly generate text
        // The parent scope will handle ensuring constraints are met
        return $result;
    }

    /**
      *  Generate a string that satisfies this lookahead constraint
      *
      *  @access public
      *  @param GeneratorInterface $generator
      *  @return string
      */
    public function generateConstraintSatisfyingString(GeneratorInterface $generator)
    {
        $result = '';

        // Generate content from the attached child scopes
        foreach ($this as $child) {
            $child->generate($result, $generator);
        }

        return $result;
    }

}
/* End of File */