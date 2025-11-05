<?php
use ReverseRegex\Lexer;
use ReverseRegex\Parser;
use ReverseRegex\Generator\Scope;
use ReverseRegex\Random\MersenneRandom;

# require composer
require '../vendor/autoload.php';

# parse the regex - password pattern with lookaheads
# This pattern requires: at least one uppercase, one digit, one lowercase, length 8-20
$lexer = new Lexer("^(?=.*[A-Z])(?=.*[0-9])(?=.*[a-z]).{8,20}$");
$parser    = new Parser($lexer,new Scope(),new Scope());

try {
    $generator = $parser->parse()->getResult();

    # run the generator
    $random = new MersenneRandom(time());

    echo "Generating passwords with pattern: ^(?=.*[A-Z])(?=.*[0-9])(?=.*[a-z]).{8,20}$\n";
    echo "Requirements: At least 1 uppercase, 1 digit, 1 lowercase, length 8-20\n";
    echo str_repeat("-", 60) . "\n";

    for($i = 10; $i > 0; $i--) {
        $result = '';
        $generator->generate($result,$random);

        echo $result;
        echo PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Note: Lookahead support is complex and may need additional refinement.\n";
}