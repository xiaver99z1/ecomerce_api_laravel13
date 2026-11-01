<?php

function hello($name)
{
    if ($name) {
        echo 'Hello '.$name;
    } else {
        echo 'Hello World';
    }
}
hello('John');
