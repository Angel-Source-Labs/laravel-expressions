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

### package conflicts: `artisan expressions:doctor`
This package injects new database connection and grammar classes, so it potentially conflicts with other packages that inject database connections and grammar classes.

To test that the installation is working and is not experiencing conflicts from other packages, this package includes an `artisan expressions:doctor` command
that will run tests to verify that the database connections are resolving properly and expressions are building properly.

To run the doctor, type `php artisan expressions:doctor` at the command line at the base of your Laravel project.

# How to Create Expressions
## Expression (without bindings)
Create an expression by creating a new instance of [AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Expression](src/Database/Expression/Expression.php).
```php
    public function testSelectRawUsingExpression()
    {
        $expression = new Expression("price as price_before_tax");
        $sql = DB::table('orders')->selectRaw($expression)->toSql();
        $this->assertEquals('select price as price_before_tax from `orders`', $sql);
    }
```

## Expression (with bindings)
Create an expression with bindings by creating a new instance of [AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Expression](src/Database/Expression/Expression.php).  The first parameter is the
raw sql expression using `?` placeholders for the bindings.  The second parameter is an array of binding values.

```php
        $expression = new Expression("inet_aton(?)", ["192.168.0.1"]);
        DB::table('audits')->where('ip', $expression)->get();
```

This produces the SQL `'select * from `audits` where `ip` = inet_aton(?)'` with a PDO binding of `[1 => "192.168.0.1"]`

## Make Expressions Semantically Meaningful

You can create reusable expressions classes with semantic meaning.

```php
public class InetAtoN extends Expression
{
    public function __construct($address)
    {
        parent::__construct("inet_aton(?)", $address);
    }
}

DB::table('audits')->where('ip', new InetAtoN("192.168.0.1"))->get();
```

## Eloquent - Assign Expressions to Model Attributes

Expressions can be stored in Eloquent model attributes and will be used in insert and update statements.

```php
public class Point extends Expression
{
    public function __construct($lat, $lng)
    {
        parent::__construct("ST_GeomFromText(?, ?)", [$lng, $lat]);
    }
}

$model->point = new Point(44.9561062,-93.1041534);
$model->save();
```

results in the following insert or update statement depending on whether the record is new or already existing:

```sql
# example insert statement result
insert into "test_models" ("point") values (ST_GeomFromText(?, ?)) returning "id";

# example update statement result
update "test_models" set "point" = ST_GeomFromText(?, ?) where "id" = ?;
```

## Make Existing Classes into Expressions: `IsExpression` interface and `ProvidesExpression` trait 

When building domain classes, a class may already extend from another class and may not always be able to extend from
`Expression`.

You can turn any class into an expression by implementing the `IsExpression` interface.

You can also use the trait `ProvidesExpression` to add the default implementation to your class.

```php
class ClassIsExpression implements IsExpression
{
    use ProvidesExpression;
}

function testSelectRawUsingExpression()
{
    $expression = new ClassIsExpression("price as price_before_tax");
    $sql = DB::table('orders')->selectRaw($expression)->toSql();
    $this->assertEquals('select price as price_before_tax from `orders`', $sql);
}
```

In fact the `Expression` class is implemented using the `IsExpression` interface and `ProvidesExpression` trait.
```php
use Illuminate\Database\Query\Expression as BaseExpression;

class Expression extends BaseExpression implements IsExpression
{
    use ProvidesExpression;
}
```

## `ExpressionGrammar`: Provide expressions with grammar differences by database

Sometimes SQL expressions need to provide different grammar for different databases and for different versions of databases.

This package provides an `ExpressionGrammar` class that will produce the appropriate expression for the database and version in use.

For example, when working with `ST_GeomFromText()` between MySQL 8.0 vs MySQL 5.7 and Postgres, the order of latitude and longitude is different,
and when switching between databases you might want your code base to work the same without changes.  MySQL 8.0 provides an option
for `ST_GeomFromText()` to change the axis order. So while the grammar for Postgres will look like `ST_GeomFromText(?, ?)`,
the grammar for MySql 8.0 will look like `ST_GeomFromText(?, ?, 'axis-order=long-lat')`.

Creating an `Expression` with an `ExpressionGrammar` to support these three different grammars would look like this:
```php
$grammar = ExpressionGrammar::make()
        ->mySql("ST_GeomFromText(?, ?)")
        ->mySql("ST_GeomFromText(?, ?, 'axis-order=long-lat')", "8.0")
        ->postgres("ST_GeomFromText(?, ?)");
$expression = new Expression($grammar, [$lon, $lat]);
```
This will resolve to the following expressions for the specified databases and versions:

| database | version | result |
|----------|---------|--------|
| MySQL    | default | ST_GeomFromText(?, ?) |
| MySQL    | 8.0 and higher | ST_GeomFromText(?, ?, 'axis-order=long-lat') |
| Postgres | default | ST_GeomFromText(?, ?) |

### Available Methods
The `ExpressionGrammar` class provides a fluent interface for adding grammar expressions and has methods for each built-in Laravel driver as well
as a generic `grammar` method that allows specifying a driver string for other databases.

#### `make()`
Creates a new Grammar instance and provides a fluent interface for adding grammar expressions.
#### `mySql($string, $version (optional))`
Add an expression for MySQL grammar.
#### `postgres($string, $version (optional))`
Add an expression for Postgres grammar.
#### `sqLite($string, $version (optional))`
Add an expression for SQLite grammar.
#### `sqlServer($string, $version (optional))`
Add an expression for SqlServer grammar.
#### `grammar($driver, $string, $version (optional))`
Add an expression for grammar for other database drivers.  `$driver` should match the driver string used by the Laravel query builder driver.
For example `$grammar->postgres("ST_GeomFromText(?, ?)")` is equivalent to `$grammar->grammar("pgsql", "ST_GeomFromText(?, ?)")`.

The `$version` parameter is optional.  When not specified, the grammar applies as the default.  When specified, the grammar applies to the specified version of the database or greater.

`ExpressionGrammar` will throw a `GrammarNotDefinedForDatabaseException` if the Query Builder attempts to resolve an Expression for a Grammar that has not been defined for that database driver.

### Example: Point with ExpressionGrammar
Revisiting the Point example from above using the ExpressionGrammar class to create appropriate grammar for MySql 5.7, MySql 8.0, and Postgres: 
```php
public class Point extends Expression
{
    public function __construct($lat, $lng)
    {
        parent::__construct(ExpressionGrammar::make()
            ->mySql("ST_GeomFromText(?, ?)")
            ->mySql("ST_GeomFromText(?, ?, 'axis-order=long-lat')", "8.0")
            ->postgres("ST_GeomFromText(?, ?)"), 
        [$lng, $lat]);
    }
}

$model->point = new Point(44.9561062,-93.1041534);
$model->save();
```

which will evaluate as an expression and result in the following SQL
```sql
# example insert statement result
insert into "test_models" ("point") values (ST_GeomFromText(?, ?)) returning "id";  # MySQL 5.7, postgis
insert into "test_models" ("point") values (ST_GeomFromText(?, ?, 'axis-order=long-lat')) returning "id";  # MySQL 8.0 and greater

# example update statement result
update "test_models" set "point" = ST_GeomFromText(?, ?) where "id" = ?; # MySQL 5.7, postgis
update "test_models" set "point" = ST_GeomFromText(?, ?, 'axis-order=long-lat') where "id" = ?; # MySQL 8.0 and greater
```

## Supported Query Builder Statements
### `select`
Example:
```php
    $expression = new Expression("price * ? as price_with_tax", [1.0825]);
    DB::table('orders')->select($expression)->get();
```
result:
```sql
    select price * ? as price_with_tax from `orders`; # bindings = [1 => 1.0825]
```

### `selectRaw`
Example 1:
```php
    $expression = new Expression("price * ? as price_with_tax", [1.0825]);
    DB::table('orders')->selectRaw($expression)->get();
```
result:
```sql
    select price * ? as price_with_tax from `orders`; # bindings = [1 => 1.0825]
```

Example 2:
```php
    $expression = new Expression("price * ? as price_with_tax, price * ? as profit", [1.0825]);
    DB::table('orders')->selectRaw($expression, [.20])->get();
```
result:
```sql
    select price * ? as price_with_tax, price * ? as profit from `orders`; # bindings = [1 => 1.0825, 2 => 0.20]
```

### `whereRaw` / `orWhereRaw`
Example 1:
```php
    $expression = new Expression('price > IF(state = "TX", ?, 100)', [200]);
    DB::table('orders')->whereRaw($expression)->get();
```
result:
```sql
    select * from `orders` where price > IF(state = "TX", ?, 100); # bindings = [1 => 200]
```

Example 2:
```php
    $expression = new Expression('price > IF(state = "TX", ?, ?)', [200]);
    DB::table('orders')->whereRaw($expression, [100])->get();
```
result:
```sql
    select * from `orders` where price > IF(state = "TX", ?, ?); # bindings = [1 => 200, 2 => 100]
```

### `havingRaw` / `orHavingRaw`
Example 1:
```php
    $expression = new Expression('SUM(price) > ?', [2500]);
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
        $expression = new Expression('SUM(price) > ? and AVG(price) > ?', [2500]);
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
    $expression = new Expression('field(id, ?, ?, ?, ?)', $ids);
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
    $expression = new Expression('field(id, ?, ?, ?, ?)', [12,23]);
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
    $expression = new Expression('price > ?', [100]);
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
        $expression = new Expression('price > ?, department > ?', [100]);
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
    $expression = new Expression("inet_aton(?)", ["192.168.0.1"]);
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

#### Currently Unimplemented / Untested Cases
These cases are currently not supported (or at least not tested) but likely could be added.
##### Array of Conditions (currently unimplemented / untested)
[Where Clauses](https://laravel.com/docs/8.x/queries#where-clauses)
```php
$users = DB::table('users')->where([
    ['status', '=', '1'],
    ['subscribed', '<>', '1'],
])->get();
```
##### Logical Grouping (currently unimplemented / untested)
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

##### Where Exists Clauses (currently unimplemented / untested)
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

##### Subquery Where Clauses (currently unimplemented / untested)
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

##### JSON Where Clauses (currently unimplemented / untested)
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
