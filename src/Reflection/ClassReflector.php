<?php

namespace Mateffy\Introspect\Reflection;

class ClassReflector
{
    public static function extends(string $class, string $extends): bool
    {
        $parents = ClassReflector::getNestedParents($class);

        return in_array($extends, $parents, true);
    }

    public static function getNestedParents(string $class): array
    {
        $parents = class_parents($class);

        if ($parents === false) {
            $parents = [];
        }

        $allParents = [];

        foreach ($parents as $parent) {
            $allParents[] = $parent;

            foreach (static::getNestedParents($parent) as $nestedParent) {
                $allParents[] = $nestedParent;
            }
        }

        return collect($allParents)
            ->map(fn (string $parent) => ltrim($parent, '\\'))
            ->unique()
            ->values()
            ->all();
    }
}
