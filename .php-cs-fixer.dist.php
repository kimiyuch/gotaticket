<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

/*
 * House style: Laravel conventions, indented with tabs.
 * Blade templates are excluded (PHP-CS-Fixer cannot parse them).
 */
$finder = Finder::create()
	->in([__DIR__.'/app', __DIR__.'/bootstrap', __DIR__.'/config', __DIR__.'/database', __DIR__.'/routes', __DIR__.'/tests', __DIR__.'/public'])
	->append([__DIR__.'/artisan', __FILE__])
	->exclude(['cache', 'build'])
	->notName('*.blade.php');

return (new Config)
	->setParallelConfig(ParallelConfigFactory::detect())
	->setRiskyAllowed(false)
	->setIndent("\t")
	->setLineEnding("\n")
	->setFinder($finder)
	->setRules([
		'@PSR12' => true,
		'array_indentation' => true,
		'array_syntax' => ['syntax' => 'short'],
		'binary_operator_spaces' => ['default' => 'single_space'],
		'braces_position' => ['anonymous_classes_opening_brace' => 'next_line_unless_newline_at_signature_end'],
		'blank_line_before_statement' => ['statements' => ['continue', 'return']],
		'class_attributes_separation' => ['elements' => ['method' => 'one']],
		'concat_space' => ['spacing' => 'none'],
		'fully_qualified_strict_types' => true,
		'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
		'method_chaining_indentation' => true,
		'new_with_parentheses' => ['anonymous_class' => false, 'named_class' => false],
		'no_extra_blank_lines' => true,
		'no_unused_imports' => true,
		'no_whitespace_in_blank_line' => true,
		'not_operator_with_successor_space' => true,
		'ordered_imports' => ['sort_algorithm' => 'alpha'],
		'phpdoc_align' => ['align' => 'left'],
		'phpdoc_separation' => true,
		'phpdoc_single_line_var_spacing' => true,
		'phpdoc_trim' => true,
		'single_quote' => true,
		'single_line_empty_body' => true,
		'single_trait_insert_per_statement' => false,
		'statement_indentation' => true,
		'trailing_comma_in_multiline' => ['elements' => ['arguments', 'arrays', 'match', 'parameters']],
		'unary_operator_spaces' => true,
		'whitespace_after_comma_in_array' => true,
	]);
