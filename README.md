# Structure

**Structure** provides a set of classes to check the data type and format of your variables.

## License

**Structure** is provided under the [MIT License](https://raw.githubusercontent.com/3nr1c/structure/master/LICENSE).

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
$arrayCheck->setFormat(array("profile" => "[]");
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
All classes have also the following getters / setters:

```php
public function setData($data);
public function getData();
public function setNull($null);
public function getNull();

// Not the actual type, shouldn't be used outside the classes
public function setType($type);
public function getType();
```

## Class ScalarS

This class runs the ```is_scalar()``` test to a variable.

Usage:
```php
$scalar = new \Structure\ScalarS();
$scalar->checkType($var);
```

## Class NumericS

This class runs the ```is_numeric()``` test against a variable. A range property can be defined, with the following syntax:

```regexp
/^[\(\[]-?\d+(\.\d+)?,-?\d+(\.\d+)?[\)\]]$/
```

That is, it uses the [mathematical notation](https://en.wikipedia.org/wiki/Interval_(mathematics)#Including_or_excluding_endpoints):

* The '(' character indicates a strict (<) lower bound
* The '[' character indicates a non-strict (<=) lower bound
* The ')' character indicates a strict (>) upper bound
* The ')' character indicates a non-strict (>=) upper bound

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

## Class StringS

This class runs the ```is_string()``` test against a variable.

Usage:
```php
$string = new \Structure\StringS();
$string->checkType($var);
```

## Class ArrayS

This class has the following methods:

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

The array type is used to represent more complex array structures. If you expect an array to be sequential (i.e., not
key-value), the format should be an array of types. Again, if all array elements have to be of the same type, the syntax
above is recommended.

```php
$array->setFormat(array("integer", "string", "array"));
$array->setFormat(array(
    "int",
    "string",
    array("bool", "int", "float"),
    "numeric[]"
);
```

Finally, you can define required keys for an associative array. Warning: if the array has some keys you do not want
to check, make sure you run the ```$array->setCountStrict(false)``` command.
 
```php
$array->setFormat(array(
    "foo" => "string"
    "bar" => "int[3,10)"
    "abc" => "array",
    "xyz" => array(
        "integer[]"
        array(
            "hello" => "bool"
        )
    )
));
```
As you can see, it has a recursive definition

# Further info

You can read more documentation by running ```composer doc``` (phpdoc needed) and by looking at the test cases.

# Planned features

* [ ] Quick static functions for type testing (ArrayS::check())
* [ ] Date formats: timestamp, mysql date/time/datetime, standard times
* [ ] Email, ip, hash formats
* [ ] Improvement for ranges: infinities
* [ ] Objects: attributes (name, visibility) and methods (name, visibility, parameters)
* [ ] Regexp for strings