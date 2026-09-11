<?php

if (!function_exists('add_filter')) {
    function add_filter($tag, $callback, $priority = 10) {
        app('filter')->add($tag, $callback, $priority);
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value, ...$args) {
        return app('filter')->apply($tag, $value, ...$args);
    }
}
