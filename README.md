# Structure [![Build Status](https://travis-ci.org/3nr1c/structure.svg?branch=master)](https://travis-ci.org/3nr1c/structure)

**Structure** provides a set of classes to check the data type and format of your variables.

## Table of Contents
**[License](#license)**<br/>
**[Installation](#installation)**<br/>
**[Introduction](#introduction)**<br/>
**[Documentation](#documentation)**<br/>
**[Static shortcuts](#static-shortcuts)**<br/>
**[Class ScalarS](#class-scalars)**<br/>
**[Class NumericS](#class-numerics)**<br/>
**[Classes IntegerS and FloatS](#classes-integers-and-floats)**<br/>
**[Class StringS](#class-strings)**<br/>
**[Class ArrayS](#class-arrays)**<br/>
**[Working with Value Sets](#working-with-value-sets)**<br/>
**[Further info](#further-info)**<br/>
**[Changelog](#changelog)**<br/>
**[Planned features](#planned-features)**<br/>

## License

**Structure** is provided under the [MIT License](https://raw.githubusercontent.com/3nr1c/structure/master/LICENSE).

## Installation

You can install Structure into your project using [Composer](http://getcomposer.org). Using the latest release is
  highly recommendable. You can start using it by adding the following to your **composer.json**:
  
```json
"require":{
    "3nr1c/structure": "0.*"
}
```

You can also use the ```"dev-master"``` version at your own risk. The code in this version may change at any time.

# Introduction

Have you ever had to check the structure of some variable? (maybe it's an array that must have certain keys and
 value types, or an integer that must be within a certain range). If so, you probably have written some code like this:

```php
if (!is_array($c)) {
    throw new \Exception();
} else if (!isset($c["profile"])) {
    throw new \Exception();
} else if (!is_array($c["profile"])) {
    throw new \Exception();
} //...
```

And that's only the beginning! I suppose that for each key, you also have to check for other keys, and data types.
**Structure** helps you with these issues. The previous code becomes:

```php
$arrayCheck = new \Structure\ArrayS();
$arrayCheck->setFormat(array("profile" => "array");
if (!$arrayCheck->check($c)) {
    throw new \Exception();
} //...
```

Here are some more examples:

```php
$arrayCheck = new \Structure\ArrayS();
$arrayCheck->setFormat(array(
    "id" => "int",
    "rating" => "float[0,10]",
    "title" => array(
        "en" => "str",
        "es" => "str"
    ),
    "links" => "string[]",
    "subtitles" => "bool"
));
```

```php
$arrayCheck = new \Structure\ArrayS();
$arrayCheck->setFormat(array(
    "name" => "string",
    "customer_id" => "int",
    "is_admin" => "bool",
    "last_session" => "string"
));
```

# Documentation

All **Structure** classes have the same constructor:

```php
public function __construct($data = null, $null = false);
```

The $null argument allows the data to be null when running the ```check``` method.
All classes have also the following methods:

```php
public function setData($data);
public function getData();

// to avoid the data to be null:
public function setNull($null);
public function getNull();

// to check everything (type and range/format)
public function check($data = null, &$failed = null);

// to format things (specially powerfull in ArrayS)
public function format($data = null);

public static function getLastFail();
public static function clearLastFail();
```

The ```&$failed``` argument of ```check``` lets you create a variable that will tell you why this method returned false. You can access to the last error information, and erase it, with the static methods ```getLastFail``` and ```clearLastFail```. The possible values for this failure variables are specified in their section below.

## Static shortcuts

The main Structure class provides four static methods to quickly generate checkers:

```php
public static function ArrayS($format = null, $data = null, $countStrict = true, $null = false);
public static function NumericS($range = null, $data = null, $null = false);
public static function IntegerS($range = null, $data = null, $null = false);
public static function FloatS($range = null, $data = null, $null = false);
public static function StringS($data = null, $null = false);
```

All these methods return respectively an ArrayS, NumericS, IntegerS, FloatS or StringS object, with the
 properties set as passed by the arguments.

## Class ScalarS

This class runs the ```is_scalar()``` test to a variable. If the result is ```false```, the ```$failed``` var will show the type of the tested variable.

Usage:
```php
$scalar = new \Structure\ScalarS();
if (!$scalar->check($var, $failed)) {
  print "Error: expected _scalar_, got " . $failed;
}
```

This class and all its children have the ```setValueSet``` method, which lets you define possible values for the data to be checked. This method can take either a variable number of arguments, or a string with values separated by commas between curly brackets ```{``` and ```}```:

```php
$scalar->setFormat("value1", "value2", 10, 11, 12);
// or
$scalar->setFormat("{value1, value2, 10, 11, 12, commas and brackets can be escaped: \{\,}");
```

```true``` and ```false``` tokens evaluate to booleans If you need the strings "true" and "false", escape them

```php
$scalar->setFormat("{true, false}");
$scalar->check(true); //true
$scalar->check(false); //true
$scalar->check("true"); //false
$scalar->check("false"); //false

$scalar->setFormat("{\\true, \\false}");
$scalar->check(true); //false
$scalar->check(false); //false
$scalar->check("true"); //true
$scalar->check("false"); //true
```

If the tested variable is scalar but isn't in the defined set, the ```$failed``` var will be ```"scalar:value"```.

## Class NumericS

This class runs the ```is_numeric()``` test against a variable. A range property can be defined, with the following syntax:

```regexp
/^\s*[\(\[]\s*(-?\d+(\.\d+)?|-inf)\s*,\s*(-?\d+(\.\d+)?|\+?inf)\s*[\)\]]\s*$/
```

That is, it uses the [mathematical notation](https://en.wikipedia.org/wiki/Interval_(mathematics)#Including_or_excluding_endpoints):

* The '(' character indicates a strict (<) lower bound
* The '[' character indicates a non-strict (<=) lower bound
* The ')' character indicates a strict (>) upper bound
* The ']' character indicates a non-strict (>=) upper bound

The term ```inf``` is used to indicate infinities. It has limitations: the left value can only be ```-inf```, and the right
value either ```inf``` or ```+inf```. 

The parser will raise an ```\Exception``` if the syntax is not correct. Here are a couple of examples:
 
```php
$numeric = new \Structure\NumericS();
$numeric->setRange("(-2,5]");

$numeric->check(-4.2);// false
$numeric->check(-2);// false
$numeric->check(0.33);// true
$numeric->check(3);// true
$numeric->check(5);// true
$numeric->check(10.42);// false
```

The left number must be *less than or equal to* the right number. 

If the type of the tested variable is correct, but the range isn't, the ```$failed``` variable will have the value ```"numeric:range"```.

## Classes IntegerS and FloatS

They both inherit from ```NumericS```. The only difference is that the **check** method of IntegerS uses ```is_integer``` 
(is stricter), and ```FloatS``` uses ```is_float```. Notice this:

```php
$numeric = new \Structure\NumericS();
$numeric->check("3.2");// true
$numeric->check("5");// true

$float = new \Structure\FloatS();
$float->check("3.2");// false

$integer = new \Structure\IntegerS();
$integer->check("5");// false
```

As you can see in the examples above, ```FloatS``` and ```IntegerS``` are strict regarding ```string```s of numbers.
You can use the attribute ```$numeric``` (set to ```false``` by default) to avoid this strictness:

```php
$float = new \Structure\FloatS();
$integer = new \Structure\IntegerS();

$float->setNumeric(true);
$integer->setNumeric(true);

$float->check("3.2");// true
$integer->check("5");// true
```

If the type of the tested variable is correct, but the range isn't, the ```$failed``` variable will be either ```"integer:range"``` or ```"float:range"```.

## Class StringS

This class runs the ```is_string()``` test against a variable.

Usage:
```php
$string = new \Structure\StringS();
$string->check($var);
```

Min and max length can be required using ```setLength```:

```php
$string->setLength(4); // 4 characters or more
$string->setLength(4, 10); // 4 to 10 characters
$string->setLength(0, 10); // up to 10 characters
$string->setLength("(4..10)"); // 4 to 10 characters
```

If the test data is a string but does not match the length, the ```$fail``` var will have the value ```"string:length"```.

## Class ArrayS

This class has the following methods (plus all the methods inherited from ```Structure```):

```php
public function setFormat($format);
public function getFormat();
public function setCountStrict();
public function isCountStrict();

public static function isAssociative($data);
```
### setFormat

The $format argument can have many forms. The type must always be ```array``` or ```string```. The string type is used to 
define simple arrays, such as

```php
$array->setFormat("int[]");
$array->setFormat("int[10]");
$array->setFormat("bool[]");
$array->setFormat("MyClass[3]");
```

The characters ```+``` and ```*``` can also be used to test simple arrays. The following expressions are valid:

```php
$array->setFormat("string[*]"); // checks for 0 or more strings
$array->setFormat("integer[+]"); // checks for 1 or more integers
$array->setFormat("scalar[5+]"); // checks for 5 or more scalars
```

Types can be mixed using the vertical bar ```|```:

```php
$array->setFormat("(string|int|bool)[4+]");
$array->setFormat("(float|null)[]");
```

And the token ```[]``` can be "nested", to define multidimensional arrays

```php
$array->setFormat("integer[][]");
$array->setFormat("(integer[]|float[])[]");

// Beware of dimensions. This:
$array->setFormat("integer[2][3]");
// Would check true with this:
$array->check(array(
  array(1, 2),
  array(3, 4),
  array(5, 6)
)); // true

// this:
$array->setFormat("integer|float[]");
// is different from:
$array->setFormat("(integer|float)[]");
```

The array type is used to represent more complex array structures. If you expect an array to be sequential (i.e., not
key-value), the format should be an array of types. Again, if all array elements have to be of the same type, the syntax
above is recommended.

```php
$array->setFormat(array("integer", "string", "array"));
$array->setFormat(array(
    "int",
    "string",
    array("bool", "int", "float"),
    "numeric[]",
    "string(8..10)"
);
```

Finally, you can define required keys for an associative array. Warning: if the array has some keys you do not want
to check, make sure you run the ```$array->setCountStrict(false)``` command.
 
```php
$array->setFormat(array(
    "foo" => "string",
    "foo2" => "string{hello, bye}[]",
    "bar" => "int[3,10)",
    "bar2" => "int[3,10)[4+]",
    "abc" => "array",
    "xyz" => array(
        "integer[]",
        "string(..5)[]",
        array(
            "hello" => "bool"
        )
    )
));
```
As you can see, it has a recursive definition.

## Working with Value Sets

Sometimes you want to check if a variable has a value that you know. Let's imagine that we want to check if a number is
3 or 7. It's not possible using the mathematical notation supported by Structure (we'd need to use unions and intersections
of mathematical entities). Value Sets in Structure provide this feature.

```php
$numeric = new \Structure\NumericS();
$numeric->setValueSet(3, 7);

$numeric->check(3); //true
$numeric->check(7); //true
$numeric->check(542); //false

// Less efficient, but valid (and needed for ArrayS):
//  $numeric->setValueSet("{3,7}");
```

This feature is available for all Structure types. When ```ScalarS``` (```StringS```, ```NumericS```, ```IntegerS```,
```FloatS```, and ```BooleanS```) is inherited a ```setValueSet()``` method is available.

When working with arrays, the Value Set information can be embedded within the format declaration.

```php
$format = array(
    "string{a,b,c}",
    "integer{2, 4, 6}"
);

$array = \Structure\Structure::ArrayS($format);

$arrayA = array("a", 2);
$arrayB = array("a", 6);
$arrayC = array("c", 2);
$arrayD = array("f", 2);

$array->check($arrayA); //correct
$array->check($arrayB); //correct
$array->check($arrayC); //correct
$array->check($arrayD); //incorrect
```

This feature can also be used to check whether a variable matches a single value, which can be quite useful in an array context. For example, you could check bools, or integers:

```php
$format = array(
    "logged" => "bool{true}",
    "status" => "integer{0}"
);
$array = \Structure\Structure::ArrayS($format);

$test1 = array("logged" => true, "status" => 0);
$test2 = array("logged" => true, "status" => 1); //the status indicates some error code here

$array->check($test1);//true
$array->check($test2);//false
```

# Further info

You can read more documentation by running ```composer doc``` (phpdoc needed) and by looking at the test cases.

# Changelog

**0.6.0**

* ArrayS: format can be defined with a JSON string
* ArrayS: format can be defined with a path to a file containing a format in JSON
* ArrayS: fixed a bug with formats of the type "null|string[]"
* ArrayS: literal ```null``` can be used in place of the ```"null"``` string
* ArrayS: an array item can be "(string{a,b,c}|integer[3,10])[]"
* ArrayS: "string[3]" can now be used to describe an associative array
* Structure::typeof is now a public method

**0.5.0**

* Compilation of strings for ArrayS, ScalarS (value sets) and NumericS (integer ranges) to avoid re-parsing
* Failure reporting: all ```check``` methods accept as second argument a variable to be created to return what failed, if the test evaluates to ```false```. The last occurred error can be retrieved with ```Structure::getLastFail()``` and removed with ```Structure::clearLastFail()```
* ArrayS: quantifiers for simple array description: ```"type[*]"```, ```"type[+]"```, ```"type[10+]"```
* ArrayS: multiple types for a value: ```"integer|float"```, ```"string|null"```
* ArrayS: mixed simple arrays: ```"(integer|float)[]"```
* ArrayS: nested simple arrays: ```"integer[][]"```, ```"(integer[]|string[])[]"```
* ScalarS: ```true``` and ```false``` tokens can be escaped to become strings: ```"{\\true, \\false}"```
* StringS: length test. It can be defined with ```setLength```. For arrays, the syntax is ```"string(min..max)"```

# Planned features

* [ ] Date formats: timestamp, mysql date/time/datetime, standard times
* [ ] Email, ip, hash formats
* [ ] Objects: attributes (name, visibility) and methods (name, visibility, parameters)
* [ ] Regexp for strings
* [ ] ArrayS: mark some keys as optional
