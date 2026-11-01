<?php

/**
 * Greet the specified name or fallback to world.
 *
 * @param  string|null  $name
 * @return void
 */
function hello($name)
{
    if ($name) {
        echo 'Hello '.$name;
    } else {
        echo 'Hello World';
    }
}
hello('John');
