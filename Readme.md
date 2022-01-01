# Enhanced Database Query Expressions for Laravel

### What is an Expression?
An Expression is a string of raw sql that can be used in Laravel Query Builder statements.
The Laravel documentation presents the concept of [Raw Expressions](https://laravel.com/docs/8.x/queries#raw-expressions) as
raw SQL strings that can be created via the `DB::raw` facade or by using any of the [raw methods](https://laravel.com/docs/8.x/queries#raw-methods):
- `selectRaw`
- `whereRaw` / `orWhereRaw`
- `havingRaw` / `orHavingRaw`
- `orderByRaw`
- `groupByRaw`

Laravel represents these expressions as an [Expression](https://laravel.com/api/8.x/Illuminate/Database/Query/Expression.html)
object that can be created using the [DB::raw](https://laravel.com/api/8.x/Illuminate/Database/Connection.html#method_raw) method.

This package enhances Expressions with the following features
- Add PDO-style bindings to Expressions
- Create `Expression` subclasses that are semantically meaningful
- Assign Expressions to Eloquent attributes
- Make any class into an `Expression` by implementing the `IsExpression` interface
- `ExpressionGrammar`: An Expression can produce the appropriate different grammar for each database by using the `ExpressionGrammar` helper class

### Laravel versions
The following Laravel versions are supported:

* Laravel 6.x
* Laravel 7.x
* Laravel 8.x

### install package
Install the package with composer
```shell script
composer require angel-source-labs/laravel-expressions
```

# How to Create Expressions
## Expression (without bindings)
Create a new instance of the Laravel class [Illuminate\Database\Query\Expression](https://laravel.com/api/8.x/Illuminate/Database/Query/Expression.html).
```php
    public function testSelectRawUsingExpression()
    {
        $expression = new Expression("price as price_before_tax");
        $sql = DB::table('orders')->selectRaw($expression)->toSql();
        $this->assertEquals('select price as price_before_tax from `orders`', $sql);
    }
```

## ExpressionWithBindings

`ExpressionWithBindings($value, array $bindings)`

Create an expression with bindings by creating a new instance of [AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionWithBindings](src/Database/Expression/ExpressionWithBindings.php).  The first parameter is the
raw sql expression using `?` placeholders for the bindings.  The second parameter is an array of binding values.

```php
        $expression = new ExpressionWithBindings("inet_aton(?)", ["192.168.0.1"]);
        DB::table('audits')->where('ip', $expression)->get();
```

This produces the SQL `'select * from `audits` where `ip` = inet_aton(?)'` with a PDO binding of `[1 => "192.168.0.1"]`

### Make Expressions Semantically Meaningful

You can create reusable expressions classes with semantic meaning.   For example, you may want to perform a geographic query using the `ST_GeomFromText`
function to query matching geometries.  You might create a SpatialExpression that takes a Geometry object as a parameter:

```php
class SpatialExpression extends ExpressionWithBindings
{
    public function __construct(GeometryInterface $geometry)
    {
        parent::__construct("ST_GeomFromText(?, ?)", [$geometry->toWkt(), $geometry->getSrid()]);
    }
}
```

In this example, the `GeometryInterface` is an interface implemented by geometry objects that provide accessors to produce the WKT (Well Known Text)
and SRID (Spatial Reference Identifiers) that are used by geometry query functions, and might look like this:

```php
interface GeometryInterface
{
    public function toWkt();
    public function getSrid();
}
```

A Geometry object can use the SpatialExpression to prepare an expression with bindings that can be used later in a geometry query.

```php
class Point implements GeometryInterface
{
    private $lat;
    private $lng;
    private $srid = 4236;

    private $expression;

    public function __construct($lat, $lng, $srid = 0) {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->expression = new SpatialExpression($this);
    }
    
    public function toWkt()
    {
        return "POINT({$this->lng} {$this->lat})";

    }

    public function getSrid()
    {
        return $this->srid;
    }
    
    public function expression()
    {
        return $this->expression;
    }
}

$point = new Point(44.9561062,-93.1041534);
DB::select($point->expression());
```

## Eloquent - Assigning Expressions to Attributes

Expressions can be stored in Eloquent model attributes and will be used in insert and update statements.

```php
    $point = new Point(44.9561062,-93.1041534);
    $model->point = $point->expression();
    $model->save();
```

results in the following insert or update statement depending on whether the record is new or already existing:

```sql
# example insert statement
insert into "test_models" ("point") values (ST_GeomFromText(?, ?)) returning "id";

# example update statement
update "test_models" set "point" = ST_GeomFromText(?, ?) where "id" = ?;
```

## Interfaces: `IsExpression` and `HasBindings` - Turn Your Classes into Expressions

When building domain classes, a class may already extend from another class and may not always be able to extend from
`ExpressionWithBindings`.

You can turn any class into an expression by implementing the `IsExpression` interface, and you can add expression bindings to
the class by implementing the `HasBindings` interface.

You can also use the trait `ProvidesExpression` or `ProvidesExpressionWithBindings` to add the default implementation to your class.

`IsExpression`
```php
interface IsExpression
{
    /**
     * Get the value of the expression.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Return getValue() as string
     * This function will typically be implemented as:
     * `return (string) $this->getValue();`
     *
     * @return string
     */
    public function __toString();
}
```

`HasBindings`
```php
interface HasBindings
{
    public function getBindings() : array;
}
```

### Traits: `ProvidesExpression` and `ProvidesExpressionWithBindings` - Turn Your Classes into Expressions
You can use the trait `ProvidesExpression` or `ProvidesExpressionWithBindings` to add the default implementation for `Expression` or `ExpressionWithBindings` to your class.

Often when creating an `Expression`, you might subclass the `Expression` class, but if your class already
extends a base class then you will not be able to.   Instead, you can use the `ProvidesExpression` trait:

```php
class PriceBeforeTax extends Price implements IsExpression
{
    use ProvidesExpression;
}

public function testSelectRawUsingExpression()
{
    $expression = new PriceBeforeTax("price as price_before_tax");
    $sql = DB::table('orders')->selectRaw($expression)->toSql();
    $this->assertEquals('select price as price_before_tax from `orders`', $sql);
}
```




As an example, you could extend the GeometryInterface above to implement IsExpression and HasBindings for all Geometry classes
```php
interface GeometryInterface extends IsExpression, HasBindings
{
    public function toWkt();
    public function getSrid();
}
```

and then implement the interface on the Point class:
```php
class Point implements GeometryInterface
{
    use ProvidesExpressionWithBindings;
    
    private $lat;
    private $lng;
    private $srid = 4236;

    public function __construct($lat, $lng, $srid = 0) {
        $this->lat = $lat;
        $this->lng = $lng;
    }

    public function toWkt()
    {
        return "POINT({$this->lng} {$this->lat})";

    }

    public function getSrid()
    {
        return $this->srid;
    }
    
    public function getBindings(): array
    {
        return [$this->toWKT(), $this->getSrid()];
    }

    public function getValue()
    {
        return "ST_GeomFromText(?, ?)";
    }
}
```

with the Point class now implementing IsExpression and HasBindings, it is now an expression and can be used where expressions
would be used.  You can now query a point (and also any other conforming geometry object) like this:
```php
    $point = new Point(44.9561062,-93.1041534);
    DB::select($point);
```

You can also store the IsExpression class in an Eloquent model attribute:

```php
    $point = new Point(44.9561062,-93.1041534);
    $model->point = $point;
    $model->save();
```

which will evalaute as an expression and result in the following SQL
```sql
# example insert statement
insert into "test_models" ("point") values (ST_GeomFromText(?, ?)) returning "id";

# example update statement
update "test_models" set "point" = ST_GeomFromText(?, ?) where "id" = ?;
```

## Grammar: Provide expressions with grammar differences by database

Sometimes SQL expressions need to provide different grammar for different databases.

This package provides a Grammar class that will produce the appropriate expression for the database in use.

For example, when working with `ST_GeomFromText()` between MySQL and Postgres, the order of latitude and longitude is different,
and when switching between databases you might want your code base to work the same without changes.  MySQL provides an option
for `ST_GeomFromText()` to change the axis order. So while the grammar for Postgres will look like `ST_GeomFromText(?, ?)`,
the grammar for MySql will look like `ST_GeomFromText(?, ?, 'axis-order=long-lat')`.

The Expression for the geometry object examples above is implemented using a `SpatialExpression` class.   The `Grammar` class
can be used to add the different expression grammars for each database:
```php
class SpatialExpression extends ExpressionWithBindings
{
    public function __construct(GeometryInterface $geometry)
    {
        $geomFromText = Grammar::make()
            ->mySql("ST_GeomFromText(?, ?, 'axis-order=long-lat')")
            ->postgres("ST_GeomFromText(?, ?)");

        parent::__construct($geomFromText, [$geometry->toWkt(), $geometry->getSrid()]);
    }
}
```

`Grammar` will throw a `GrammarNotDefinedForDatabaseException` if the Query Builder attempts to resolve an Expression for a Grammar that has not been defined for that database driver.

### Available Methods
The `Grammar` class provides a fluent interface for adding grammar expressions and has methods for each built-in Laravel driver as well
as a generic `grammar` method that allows specifying a driver string for other databases.

#### `make()`
Creates a new Grammar instance and provides a fluent interface for adding grammar expressions.
#### `mySql($string)`
Add an expression for MySQL grammar.
#### `postgres($string)`
Add an expression for Postgres grammar.
#### `sqLite($string)`
Add an expression for SQLite grammar.
#### `sqlServer($string)`
Add an expression for SqlServer grammar.
#### `grammar($driver, $string)`
Add an expression for grammar for other database drivers.  `$driver` should match the driver string used by the Laravel query builder driver.
For example `$grammar->postgres("ST_GeomFromText(?, ?)")` is equivalent to `$grammar->grammar("pgsql", "ST_GeomFromText(?, ?)")`.

## Supported Query Builder Statements
### `select`
Example:
```php
    $expression = new ExpressionWithBindings("price * ? as price_with_tax", [1.0825]);
    DB::table('orders')->select($expression)->get();
```
result:
```sql
    select price * ? as price_with_tax from `orders`; # bindings = [1 => 1.0825]
```

### `selectRaw`
Example 1:
```php
    $expression = new ExpressionWithBindings("price * ? as price_with_tax", [1.0825]);
    DB::table('orders')->selectRaw($expression)->get();
```
result:
```sql
    select price * ? as price_with_tax from `orders`; # bindings = [1 => 1.0825]
```

Example 2:
```php
    $expression = new ExpressionWithBindings("price * ? as price_with_tax, price * ? as profit", [1.0825]);
    DB::table('orders')->selectRaw($expression, [.20])->get();
```
result:
```sql
    select price * ? as price_with_tax, price * ? as profit from `orders`; # bindings = [1 => 1.0825, 2 => 0.20]
```

### `whereRaw` / `orWhereRaw`
Example 1:
```php
    $expression = new ExpressionWithBindings('price > IF(state = "TX", ?, 100)', [200]);
    DB::table('orders')->whereRaw($expression)->get();
```
result:
```sql
    select * from `orders` where price > IF(state = "TX", ?, 100); # bindings = [1 => 200]
```

Example 2:
```php
    $expression = new ExpressionWithBindings('price > IF(state = "TX", ?, ?)', [200]);
    DB::table('orders')->whereRaw($expression, [100])->get();
```
result:
```sql
    select * from `orders` where price > IF(state = "TX", ?, ?); # bindings = [1 => 200, 2 => 100]
```

### `havingRaw` / `orHavingRaw`
Example 1:
```php
    $expression = new ExpressionWithBindings('SUM(price) > ?', [2500]);
    $sql = DB::table('orders')
        ->select('department', DB::raw('SUM(price) as total_sales'))
        ->groupBy('department')
        ->havingRaw($expression)
        ->get();
```
result:
```sql
    select `department`, SUM(price) as total_sales from `orders` group by `department` having SUM(price) > ?; # bindings = [1 => 2500]
```

Example 2:
```php
        $expression = new ExpressionWithBindings('SUM(price) > ? and AVG(price) > ?', [2500]);
        $sql = DB::table('orders')
            ->select('department', DB::raw('SUM(price) as total_sales'))
            ->groupBy('department')
            ->havingRaw($expression, [100])
            ->get();
```
result:
```sql
    select `department`, SUM(price) as total_sales from `orders` group by `department` having SUM(price) > ? and AVG(price) > ?; # bindings = [1 => 2500, 2 => 100]
```

### `orderByRaw`
Example 1:
```php
    $ids = [12,23,34,45];
    $expression = new ExpressionWithBindings('field(id, ?, ?, ?, ?)', $ids);
    DB::table('orders')
        ->whereIn('id', $ids)
        ->orderByRaw($expression)
        ->get();
```
result:
```sql
    select * from `orders` where `id` in (?, ?, ?, ?) order by field(id, ?, ?, ?, ?); # bindings = [1 => 12, 2 => 23, 3 => 34, 4 => 45, 5 => 12, 6 => 23, 7 => 34, 8 => 45]
```

Example 2:
```php
    $ids = [12,23,34,45];
    $expression = new ExpressionWithBindings('field(id, ?, ?, ?, ?)', [12,23]);
    DB::table('orders')
        ->whereIn('id', $ids)
        ->orderByRaw($expression,[34,45])
        ->get();
```
result:
```sql
    select * from `orders` where `id` in (?, ?, ?, ?) order by field(id, ?, ?, ?, ?); # bindings = [1 => 12, 2 => 23, 3 => 34, 4 => 45, 5 => 12, 6 => 23, 7 => 34, 8 => 45]
```


### `groupByRaw`
Example 1:
```php
    $expression = new ExpressionWithBindings('price > ?', [100]);
    DB::table('orders')
        ->select('department', 'price')
        ->groupByRaw($expression)
        ->get();
```
result:
```sql
    select `department`, `price` from `orders` group by price > ?; # bindings = [1 => 100]
```

Example 2:
```php
        $expression = new ExpressionWithBindings('price > ?, department > ?', [100]);
        DB::table('orders')
            ->select('department', 'price')
            ->groupByRaw($expression, [1560])
            ->get();
```
result:
```sql
    select `department`, `price` from `orders` group by price > ?, department > ?; # bindings = [1 => 100, 2=> 1560]
```

### `where` / `orWhere`
Example:
```php
    $expression = new ExpressionWithBindings("inet_aton(?)", ["192.168.0.1"]);
    DB::table('audits')->where('ip', $expression)->get();
```

result:
```sql
    select * from `audits` where `ip` = inet_aton(?); # bindings = [1 => "192.168.0.1"]
```

#### Supported Cases
##### Basic Where Clauses
[Basic Where Clauses](https://laravel.com/docs/8.x/queries#basic-where-clauses)
```php
$users = DB::table('users')
                ->where('votes', '=', $expression)
                ->where('age', '>', $expression)
                ->get();
```

```php
$users = DB::table('users')->where('votes', $expression)->get();
```

##### Or Where Clauses
[Or Where Clauses](https://laravel.com/docs/8.x/queries#or-where-clauses)

##### Additional Where Clauses
[Additional Where Clauses](https://laravel.com/docs/8.x/queries#additional-where-clauses)

#### Currently Unsupported Cases
These cases are currently not supported (or at least not tested) but likely could be added.
##### Array of Conditions
[Where Clauses](https://laravel.com/docs/8.x/queries#where-clauses)
```php
$users = DB::table('users')->where([
    ['status', '=', '1'],
    ['subscribed', '<>', '1'],
])->get();
```
##### Logical Grouping
[Logical Grouping](https://laravel.com/docs/8.x/queries#logical-grouping)
```php
$users = DB::table('users')
           ->where('name', '=', 'John')
           ->where(function ($query) {
               $query->where('votes', '>', 100)
                     ->orWhere('title', '=', 'Admin');
           })
           ->get();
```

##### Where Exists Clauses
[Where Exists Clauses](https://laravel.com/docs/8.x/queries#where-exists-clauses)
```php
$users = DB::table('users')
           ->whereExists(function ($query) {
               $query->select(DB::raw(1))
                     ->from('orders')
                     ->whereColumn('orders.user_id', 'users.id');
           })
           ->get();
```

##### Subquery Where Clauses
[Subquery Where Clauses](https://laravel.com/docs/8.x/queries#subquery-where-clauses)
Case 1: Compare the results of subquery to a value:
```php
use App\Models\User;

$users = User::where(function ($query) {
    $query->select('type')
        ->from('membership')
        ->whereColumn('membership.user_id', 'users.id')
        ->orderByDesc('membership.start_date')
        ->limit(1);
}, 'Pro')->get();
```

Case 2: Compare a column to the results of a subquery
```php
use App\Models\Income;

$incomes = Income::where('amount', '<', function ($query) {
    $query->selectRaw('avg(i.amount)')->from('incomes as i');
})->get();
```

##### JSON Where Clauses
[JSON Where Clauses](https://laravel.com/docs/8.x/queries#json-where-clauses)
```php
$users = DB::table('users')
                ->where('preferences->dining->meal', 'salad')
                ->get();
```

## Run tests
The following tests are run with this pacakge's ExpressionsServiceProvider loaded:
* Unit tests from `vendor/laravel/framework/tests/Database`
* Integration tests from `vendor/laravel/framework/tests/Integration/Database`
* Unit tests from `tests/Unit` (this package)

```shell script
composer test
```
