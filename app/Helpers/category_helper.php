<?php

if (!function_exists('getCategoryIcon')) {
    function getCategoryIcon(string $name, array $iconMap): array
    {
        $nameLower = strtolower($name);
        foreach ($iconMap as $keyword => $iconData) {
            if (strpos($nameLower, $keyword) !== false) return $iconData;
        }
        return ['fa-box-open', '#457b9d'];
    }
}