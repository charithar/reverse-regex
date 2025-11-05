<?php
namespace ReverseRegex\Generator;

use PHPStats\Generator\GeneratorInterface;
use ReverseRegex\Exception as GeneratorException;

/**
  *  Composite Scope for managing multiple lookahead assertions
  *
  *  This generator coordinates multiple lookahead constraints
  *  and generates strings that satisfy all of them.
  *
  *  @author Generated for reverse-regex
  *  @since 0.0.1
  */
class LookaheadComposite extends Scope
{
    /**
      *  @var array Collection of lookahead scopes
      */
    protected $lookaheads = [];

    /**
      *  @var Scope The main pattern to generate (e.g., .{8,20})
      */
    protected $mainPattern = null;


    /**
      *  Class Constructor
      *
      *  @access public
      */
    public function __construct()
    {
        parent::__construct('lookahead_composite');
    }

    /**
      *  Add a lookahead constraint
      *
      *  @access public
      *  @param LookaheadScope $lookahead
      */
    public function addLookahead(LookaheadScope $lookahead)
    {
        $this->lookaheads[] = $lookahead;
    }

    /**
      *  Set the main pattern
      *
      *  @access public
      *  @param Scope $pattern
      */
    public function setMainPattern(Scope $pattern)
    {
        $this->mainPattern = $pattern;
    }

    /**
      *  Generate a string that satisfies all lookahead constraints
      *
      *  @access public
      *  @param string $result
      *  @param GeneratorInterface $generator
      */
    public function generate(&$result, GeneratorInterface $generator)
    {
        $maxAttempts = 100;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $candidate = '';

            // Generate the main pattern
            if ($this->mainPattern !== null) {
                $this->mainPattern->generate($candidate, $generator);
            }

            // Check if candidate satisfies all lookahead constraints
            if ($this->satisfiesConstraints($candidate, $generator)) {
                $result .= $candidate;
                return $result;
            }

            $attempt++;
        }

        // If we couldn't generate a satisfying string, try a guided approach
        $result .= $this->generateGuided($generator);
        return $result;
    }

    /**
      *  Check if a candidate string satisfies all lookahead constraints
      *
      *  @access protected
      *  @param string $candidate
      *  @param GeneratorInterface $generator
      *  @return boolean
      */
    protected function satisfiesConstraints($candidate, GeneratorInterface $generator)
    {
        foreach ($this->lookaheads as $lookahead) {
            $constraintMet = $this->checkLookahead($candidate, $lookahead, $generator);

            if ($lookahead->isPositive() && !$constraintMet) {
                return false;
            }
            if (!$lookahead->isPositive() && $constraintMet) {
                return false;
            }
        }

        return true;
    }

    /**
      *  Check if a string matches a lookahead pattern
      *
      *  @access protected
      *  @param string $candidate
      *  @param LookaheadScope $lookahead
      *  @param GeneratorInterface $generator
      *  @return boolean
      */
    protected function checkLookahead($candidate, LookaheadScope $lookahead, GeneratorInterface $generator)
    {
        // Generate the pattern that the lookahead expects
        $expectedPattern = $lookahead->generateConstraintSatisfyingString($generator);

        // Check if the candidate contains the expected pattern
        return strpos($candidate, $expectedPattern) !== false;
    }

    /**
      *  Generate a string using guided approach that satisfies constraints
      *
      *  @access protected
      *  @param GeneratorInterface $generator
      *  @return string
      */
    protected function generateGuided(GeneratorInterface $generator)
    {
        $result = '';
        $parts = [];

        // Collect all positive lookahead requirements
        foreach ($this->lookaheads as $lookahead) {
            if ($lookahead->isPositive()) {
                $part = $lookahead->generateConstraintSatisfyingString($generator);
                if (!empty($part)) {
                    $parts[] = $part;
                }
            }
        }

        // Combine the parts
        $result = implode('', $parts);

        // If we have a main pattern with length requirements, pad as needed
        if ($this->mainPattern !== null) {
            $minLength = $this->mainPattern->getMinOccurances();
            $maxLength = $this->mainPattern->getMaxOccurances();

            while (strlen($result) < $minLength) {
                // Get a random character from the first child of mainPattern
                $filler = $this->getFillerChar($generator);
                $result .= $filler;
            }

            // Trim if too long
            if (strlen($result) > $maxLength) {
                $result = substr($result, 0, $maxLength);
            }
        }

        return $result;
    }

    /**
      *  Get a filler character for padding
      *
      *  @access protected
      *  @param GeneratorInterface $generator
      *  @return string
      */
    protected function getFillerChar(GeneratorInterface $generator)
    {
        // Default to lowercase letters if no pattern specified
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $index = (int) round($generator->generate(0, strlen($chars) - 1));
        return $chars[$index];
    }

}
/* End of File */