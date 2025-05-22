<?php

namespace Mateffy\Introspect\Reflection;

use Exception;
use Illuminate\Support\Facades\Log;

class ClassReflector
{
    public static function extends(string $class, string $extends): bool
    {
        $parents = ClassReflector::getNestedParents($class);

        return in_array($extends, $parents, true);
    }

    public static function getNestedParents(string $class): array
    {
        try {
            $parents = class_parents($class);
        } catch (Exception $e) {
            Log::error('Error getting class parents: '.$e->getMessage());

            $parents = false;
        }

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

    public static function getNestedInterfaces(string $class): array
    {
        try {
            $interfaces = class_implements($class);
        } catch (Exception $e) {
            Log::error('Error getting class interfaces: '.$e->getMessage());

            $interfaces = false;
        }

        if ($interfaces === false) {
            $interfaces = [];
        }

        return collect($interfaces)
            ->map(fn (string $interface) => ltrim($interface, '\\'))
            ->unique()
            ->values()
            ->all();
    }

    public static function getNestedTraits(string $class): array
    {
        try {
            $traits = class_uses_recursive($class);
        } catch (Exception $e) {
            Log::error('Error getting class traits: '.$e->getMessage());

            $traits = false;
        }

        if ($traits === false) {
            $traits = [];
        }

        return collect($traits)
            ->map(fn (string $trait) => ltrim($trait, '\\'))
            ->unique()
            ->values()
            ->all();
    }
}
