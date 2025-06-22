<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific
| test case class. By default, that class is "PHPUnit\Framework\TestCase".
|
| For service-based testing, you can bind it to "Tests\TestCase" which is
| the Pest test case that ships with Laravel.
|
*/

uses(TestCase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain
| conditions. Pest provides a rich set of expectations that extend PHPUnit's
| assertions. Of course, you may extend the Expectation API at any time.
|
*/

// expect()->extend('toBeOne', function () {
//     return $this->toBe(1);
// });

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing
| helpers that you use frequently. Here, you can define global functions
| that will be available in all your test files.
|
*/

// function something()
// {
//     // ..
// } 