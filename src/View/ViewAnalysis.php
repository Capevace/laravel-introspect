<?php

namespace Mateffy\Introspect\View;

use Illuminate\View\FileViewFinder;

class ViewAnalysis
{
    public const VIEW_SEGMENT_PATTERN = '[a-zA-Z0-9_.-]+';

	public function viewIncludesView(string $containerView, string $includedView, bool $lax = false, bool $allowWildcards = true): bool
	{
		/** @var FileViewFinder $viewFinder */
		$viewFinder = app('view')->getFinder();
		$file = $viewFinder->find($containerView);

		// To check if the view is used by another view, we check if the file contains strings like
		// @include('view.name'
		// @component('view.name'
		// <x-view.name...

		// Note: @include and @component may be in one line, but may also be in multiple lines, for example with large data props
		// @include(
		//	'view.name'

		// To check the file, we use regex patterns to match the strings.
		// We first check for the x- version and then for the @include and @component versions.

		$contents = file_get_contents($file);

		// If lax is true, we only check if the file contains the included view. This can have false positives, but may catch some more complex cases too.
		if ($lax) {
			return str_contains($contents, $includedView);
		}

		// Remove 'components.' to normalize the view.
		// Watch out: we also need to support namespaces, so we check for '::' in the view name.
		if (str_contains($includedView, '::')) {
			$before = str($includedView)->before('::');
			$after = str($includedView)->after('::');
			$filtered = str_replace('components.', '', $after);

			$includedViewWithoutComponentPrefix = $before . '::' . $filtered;
		} else {
			$includedViewWithoutComponentPrefix = str_replace('components.', '', $includedView);
		}

		return $allowWildcards
			? $this->checkWithWildcard($contents, $includedView, $includedViewWithoutComponentPrefix)
			: $this->checkLiteral($contents, $includedView, $includedViewWithoutComponentPrefix);
	}

	protected function checkLiteral(string $contents, string $name, string $nameWithoutPrefix): bool
	{
		// 1. Check if the file contains the <x- version
		if (str_contains($contents, '<x-' . $nameWithoutPrefix)) {
			return true;
		}

		// 2. Check if the file contains the @include version.
		// Note: we use $name instead of $nameWithoutPrefix because @include does work with the full view name, while @component only works with components and strips the prefix.
		if (preg_match('/@include\s*\(\s*[\'"]' . preg_quote($name, '/') . '[\'"]/', $contents)) {
			return true;
		}

		// 3. Check if the file contains the @component version
		if (preg_match('/@component\s*\(\s*[\'"]' . preg_quote($nameWithoutPrefix, '/') . '[\'"]/', $contents)) {
			return true;
		}

		return false;
	}

	/**
	 * Check for wildcards in the contained view names.
	 *
	 * For example, $name can be 'components::foo.*.bar' and $nameWithoutPrefix can be 'foo.*.bar'.
	 * Multiple wildcards are supported, so $name can be 'components::foo.*.bar.*' and $nameWithoutPrefix can be 'foo.*.bar.*'.
	 * This will match contained views like 'components::foo.baz.bar.baz' and 'components::foo.baz.bar.baz.baz'.
	 */
	protected function checkWithWildcard(string $contents, string $name, string $nameWithoutPrefix): bool
	{
		// Convert wildcard view names to regex patterns
		// preg_quote will escape the wildcard '*' to '\*'. We replace '\*' with a pattern matching a view segment.
		$nameRegex = str_replace('\\*', self::VIEW_SEGMENT_PATTERN, preg_quote($name, '/'));
		$nameWithoutPrefixRegex = str_replace('\\*', self::VIEW_SEGMENT_PATTERN, preg_quote($nameWithoutPrefix, '/'));

		// 1. Check for <x- version with wildcard
		// We match <x- followed by the component name (with wildcards), and then either a space, >, or / (for self-closing tags)
		$xComponentRegex = '/<x-' . $nameWithoutPrefixRegex . '([\s>\/>])/';
		if (preg_match($xComponentRegex, $contents)) {
			return true;
		}

		// 2. Check for @include version with wildcard
		if (preg_match('/@include\s*\(\s*[\'"]' . $nameRegex . '[\'"]/', $contents)) {
			return true;
		}

		// 3. Check for @component version with wildcard
		if (preg_match('/@component\s*\(\s*[\'"]' . $nameWithoutPrefixRegex . '[\'"]/', $contents)) {
			return true;
		}

		return false;
	}
}
