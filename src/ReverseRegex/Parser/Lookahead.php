<?php
namespace ReverseRegex\Parser;

use ReverseRegex\Generator\Scope;
use ReverseRegex\Generator\LookaheadScope;
use ReverseRegex\Lexer;
use ReverseRegex\Parser;
use ReverseRegex\Exception as ParserException;

/**
  *  Parse lookahead assertions (?=...) and (?!...)
  *
  *  @author Generated for reverse-regex
  *  @since 0.0.1
  */
class Lookahead implements StrategyInterface
{

    /**
      *  Parse the lookahead assertion
      *
      *  @access public
      *  @return LookaheadScope
      *  @param Scope $head
      *  @param Scope $set
      *  @param Lexer $lexer
      *  @param boolean $isPositive
      */
    public function parse(Scope $head, Scope $set, Lexer $lexer, $isPositive = true)
    {
        // Create a lookahead scope
        $lookaheadScope = new LookaheadScope($isPositive);

        // Create a nested scope for the lookahead pattern
        $nestedScope = new Scope();
        $lookaheadScope->attach($nestedScope);

        // Parse the contents of the lookahead until we hit the closing )
        // We need to track group depth because there might be nested groups
        $groupDepth = 1; // We're already inside one group

        while ($lexer->moveNext()) {
            if ($lexer->isNextToken(Lexer::T_GROUP_OPEN)) {
                $groupDepth++;
                // Parse nested group
                $parser = new Parser($lexer, new Scope(), new Scope());
                $result = $parser->parse(true)->getResult();
                $nestedScope->attach($result);
            } elseif ($lexer->isNextToken(Lexer::T_GROUP_CLOSE)) {
                $groupDepth--;
                if ($groupDepth === 0) {
                    // End of lookahead
                    break;
                }
            } elseif ($lexer->isNextTokenAny(array(Lexer::T_LITERAL_CHAR, Lexer::T_LITERAL_NUMERIC))) {
                $literalScope = new \ReverseRegex\Generator\LiteralScope();
                $literalScope->addLiteral($lexer->lookahead['value']);
                $nestedScope->attach($literalScope);
            } elseif ($lexer->isNextToken(Lexer::T_SET_OPEN)) {
                $literalScope = new \ReverseRegex\Generator\LiteralScope();
                \ReverseRegex\Parser::createSubParser('character')->parse($literalScope, $nestedScope, $lexer);
                $nestedScope->attach($literalScope);
            } elseif ($lexer->isNextTokenAny(array(
                Lexer::T_DOT,
                Lexer::T_SHORT_D,
                Lexer::T_SHORT_NOT_D,
                Lexer::T_SHORT_W,
                Lexer::T_SHORT_NOT_W,
                Lexer::T_SHORT_S,
                Lexer::T_SHORT_NOT_S
            ))) {
                $literalScope = new \ReverseRegex\Generator\LiteralScope();
                \ReverseRegex\Parser::createSubParser('short')->parse($literalScope, $nestedScope, $lexer);
                $nestedScope->attach($literalScope);
            } elseif ($lexer->isNextTokenAny(array(
                Lexer::T_QUANTIFIER_OPEN,
                Lexer::T_QUANTIFIER_PLUS,
                Lexer::T_QUANTIFIER_QUESTION,
                Lexer::T_QUANTIFIER_STAR
            ))) {
                // Get the last attached scope and apply quantifier
                if ($nestedScope->count() > 0) {
                    $lastScope = $nestedScope->get($nestedScope->count());
                    \ReverseRegex\Parser::createSubParser('quantifer')->parse($lastScope, $nestedScope, $lexer);
                }
            } elseif ($lexer->isNextToken(Lexer::T_CHOICE_BAR)) {
                $nestedScope->useAlternatingStrategy();
                $newScope = new Scope();
                $lookaheadScope->attach($newScope);
                $nestedScope = $newScope;
            }
        }

        return $lookaheadScope;
    }

}
/* End of File */