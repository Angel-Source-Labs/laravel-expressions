- Provides expressions with bindings and expressions that can be used as column values in eloquent

# What is an Expression?
An Expression is a bit of raw sql that can be used in Laravel Query Builder statements.
The Laravel documentation presents the concept of [Raw Expressions](https://laravel.com/docs/8.x/queries#raw-expressions) as
raw SQL string expressions that can be provides via the `DB::raw` facade or using any of the [raw methods](https://laravel.com/docs/8.x/queries#raw-methods):
```
selectRaw
whereRaw / orWhereRaw
havingRaw / orHavingRaw
orderByRaw
groupByRaw
```

Laravel represents these expressions as an [Expression](https://laravel.com/api/8.x/Illuminate/Database/Query/Expression.html)
object that can be created using the [DB::raw](https://laravel.com/api/8.x/Illuminate/Database/Connection.html#method_raw) method.

shortcomings
- no bindings
- no grammar differences for different SQL grammars (ie MySql vs Postgres etc)
- expressions cannot be used as Eloquent attributes
- single inheritance.  Sometimes other classes need to be used as expressions.
- OOAD - expression reuse

How to use
# install package
# setup service provider
  - unit tests currently load
  - package auto-loading?  seems to be missing in composer.json currently.
# Create Expressions
## Expression
just use the `Illuminate\Database\Query\Expression` and create a new instance.
```
    public function testSelectRawUsingExpression()
    {
        $expression = new Expression("price as price_before_tax");
        $sql = DB::table('orders')->selectRaw($expression)->toSql();
        $this->assertEquals('select price as price_before_tax from `orders`', $sql);
    }
```

## ExpressionWithBindings

`ExpressionWithBindings($value, array $bindings)`

You create an expression with bindings by creating a new instance of ExpressionWithBindings.  The first parameter is the
raw sql expression using `?` placeholders for the bindings.  The second parameter is an array of binding values.

```php
        $expression = new ExpressionWithBindings("inet_aton(?)", ["192.168.0.1"]);
        DB::table('audits')->where('ip', $expression)->get();
```

This produces the SQL `'select * from `audits` where `ip` = inet_aton(?)'` with a PDO binding of `[1 => "192.168.0.1"]`

### Domain Driven Design: Make Expressions Semantically Meaningful

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

The `GeometryInterface` is an interface implemented by our geometry objects that provide accessors to produce the WKT (Well Known Text)
and SRID (Spatial Reference Identifiers) that are used by geometry query functions and might look like this:

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

    public function __construct($lat, $lng) {
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
```

You can then query a point (and also any other conforming geometry object) by using this expression like this:
```php
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

## IsExpression and HasBindings - Turn Your Classes into Expressions

When building domain classes, a class may already extend from another class and may not always be able to extend from
`ExpressionWithBindings`.

You can turn any class into an expression by implementing the `IsExpression` interface, and you can add expression bindings to
the class by implementing the `HasBindings` interface.

IsExpression
```php
interface IsExpression
{
    /**
     * Get the value of the expression.
     *
     * @return mixed
     */
    public function getValue();
}
```

HasBindings
```php
interface HasBindings
{
    public function getBindings() : array;
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
    private $lat;
    private $lng;
    private $srid = 4236;

    private $expression;

    public function __construct($lat, $lng) {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->expression = new SpatialExpression($this);
    }

    public function getValue()
    {
        return $this->expression->getValue();
    }

    public function getBindings(): array
    {
        return $this->expression->getBindings();
    }

    public function toWkt()
    {
        return "POINT({$this->lng} {$this->lat})";

    }

    public function getSrid()
    {
        return $this->srid;
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

Sometimes SQL expressions have different grammar between different databases.

This package provides a Grammar class that will produce the appropriate expression for the database in use.

For example, when working with ST_GeomFromText() between MySql and Postgres, the order of latitude and longitude is different.
Switching between databases you might want your code base to work the same.  MySql provides an option to change the axis order.
So while the grammar for Postgres will look like `ST_GeomFromText(?, ?)`, the grammar for MySql will look like
`ST_GeomFromText(?, ?, 'axis-order=long-lat')`.

Our expression for our geometry objects above is implemented using a `SpatialExpression` class.   Adding grammar to the class might
look like this:
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
