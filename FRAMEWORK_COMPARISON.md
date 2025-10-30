# Framework Comparison: Custom vs Laravel

## Overview

This document compares the old custom PHP framework with the new Laravel implementation.

## Architecture Comparison

### Routing

**Before (FastRoute):**
```php
// app/Core/Application.php
$dispatcher = simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute(['GET', 'POST'], '/api/auth', [AuthController::class, 'authenticate']);
    $r->addRoute(['GET', 'POST'], '/api/items/get', [PosController::class, 'getItems']);
});

$routeInfo = $this->dispatcher->dispatch($requestMethod, $requestUri);
```

**After (Laravel):**
```php
// routes/api.php
Route::match(['get', 'post'], '/auth', [AuthController::class, 'authenticate']);
Route::match(['get', 'post'], '/items/get', [PosController::class, 'getItems']);
```

### Database Access

**Before (Raw SQL):**
```php
$result = mysqli_query($GLOBALS['config']['db'], 
    "SELECT * FROM sales WHERE locationid = " . mysqli_real_escape_string($locationid)
);
while ($row = mysqli_fetch_assoc($result)) {
    $sales[] = $row;
}
```

**After (Eloquent ORM):**
```php
$sales = Sale::where('locationid', $locationid)->get();
```

### Authentication

**Before (Custom):**
```php
// app/Auth.php
class Auth {
    private function validate() {
        $token = $_REQUEST['token'] ?? '';
        $query = "SELECT * FROM auth WHERE token = '" . 
                 mysqli_real_escape_string($token) . "'";
        // ... more SQL
    }
}
```

**After (Laravel):**
```php
// Using Laravel Sanctum
if (Auth::check()) {
    $user = Auth::user();
}
```

### Request Handling

**Before:**
```php
public function getItems() {
    $locationid = $_REQUEST['locationid'] ?? null;
    $search = $_REQUEST['search'] ?? '';
    
    if (empty($locationid)) {
        echo json_encode(['error' => 'Location required']);
        return;
    }
}
```

**After:**
```php
public function getItems(Request $request) {
    $validated = $request->validate([
        'locationid' => 'required|integer',
        'search' => 'nullable|string'
    ]);
    
    return response()->json([
        'data' => $items
    ]);
}
```

### Configuration

**Before:**
```php
// bootstrap/app.php
function config($key = null, $default = null) {
    if (is_null($key)) {
        return App\Core\Config::all();
    }
    return App\Core\Config::get($key, $default);
}

// config/app.php - returns array
return [
    'db' => [
        'host' => getenv('DB_HOST'),
        'name' => getenv('DB_NAME')
    ]
];
```

**After:**
```php
// Using .env and Laravel config
config('database.connections.mysql.host');
config('app.name');
env('DB_HOST');
```

### Models

**Before (Plain PHP Objects):**
```php
class StoredItem extends \stdClass {
    public $code = "";
    public $name = "";
    public $price = "";
    
    function __construct($data) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
```

**After (Eloquent):**
```php
class StoredItem extends Model {
    protected $table = 'stored_items';
    public $timestamps = false;
    
    protected $fillable = [
        'code', 'name', 'price'
    ];
    
    // Relationships
    public function supplier() {
        return $this->belongsTo(StoredSupplier::class, 'supplierid');
    }
}
```

## Feature Comparison

| Feature | Custom Framework | Laravel |
|---------|-----------------|---------|
| **Routing** | FastRoute library | Built-in Router |
| **ORM** | None (Raw SQL) | Eloquent ORM |
| **Authentication** | Custom class | Built-in Auth + Sanctum |
| **Validation** | Manual checks | Form Request validation |
| **Migrations** | SQL files | PHP migrations |
| **Seeding** | SQL inserts | Database seeders |
| **Templates** | Mustache | Blade (though not used yet) |
| **Testing** | None | PHPUnit built-in |
| **CLI** | None | Artisan commands |
| **Queue** | None | Built-in queue system |
| **Cache** | None | Multiple cache drivers |
| **Events** | None | Event system |
| **Logging** | Custom | Monolog integration |
| **Sessions** | PHP sessions | Multiple drivers |
| **File Storage** | Direct filesystem | Flysystem integration |

## Code Examples

### Creating a New Record

**Before:**
```php
$sql = "INSERT INTO customers (name, email, phone) VALUES (
    '" . mysqli_real_escape_string($name) . "',
    '" . mysqli_real_escape_string($email) . "',
    '" . mysqli_real_escape_string($phone) . "'
)";
mysqli_query($GLOBALS['config']['db'], $sql);
$customerId = mysqli_insert_id($GLOBALS['config']['db']);
```

**After:**
```php
$customer = Customer::create([
    'name' => $name,
    'email' => $email,
    'phone' => $phone
]);
$customerId = $customer->id;
```

### Updating a Record

**Before:**
```php
$sql = "UPDATE stored_items SET 
    name = '" . mysqli_real_escape_string($name) . "',
    price = '" . mysqli_real_escape_string($price) . "'
    WHERE id = " . intval($id);
mysqli_query($GLOBALS['config']['db'], $sql);
```

**After:**
```php
StoredItem::where('id', $id)->update([
    'name' => $name,
    'price' => $price
]);

// Or
$item = StoredItem::find($id);
$item->name = $name;
$item->price = $price;
$item->save();
```

### Querying with Joins

**Before:**
```php
$sql = "SELECT si.*, ss.name as supplier_name 
        FROM stored_items si 
        LEFT JOIN stored_suppliers ss ON si.supplierid = ss.id 
        WHERE si.categoryid = " . intval($categoryId);
$result = mysqli_query($GLOBALS['config']['db'], $sql);
```

**After:**
```php
$items = StoredItem::with('supplier')
    ->where('categoryid', $categoryId)
    ->get();

// Access supplier name
foreach ($items as $item) {
    echo $item->supplier->name;
}
```

## Benefits of Laravel

### 1. Security
- Built-in CSRF protection
- SQL injection prevention via Eloquent
- XSS protection in Blade templates
- Password hashing with bcrypt/argon2
- Rate limiting built-in

### 2. Developer Experience
- Artisan CLI for common tasks
- Built-in testing framework
- Database migrations version control
- Tinker REPL for debugging
- Laravel Telescope for debugging (optional)

### 3. Performance
- Query caching
- Route caching
- Config caching
- View caching
- Laravel Octane for production

### 4. Maintainability
- Clear MVC structure
- PSR standards compliance
- Dependency injection
- Service containers
- Facades for clean syntax

### 5. Ecosystem
- Large community
- Extensive packages
- Regular updates
- Security patches
- Long-term support versions

## Migration Challenges

### 1. Learning Curve
- Team needs to learn Laravel conventions
- Different patterns from custom framework
- Eloquent ORM concepts

### 2. Legacy Code
- Controllers need refactoring
- Database queries need conversion
- Authentication system replacement

### 3. Testing
- All endpoints need re-testing
- Frontend integration testing
- Performance benchmarking

### 4. Deployment
- Server configuration changes
- New deployment process
- Environment configuration

## Performance Impact

### Memory Usage
- Custom framework: ~2-5 MB per request
- Laravel: ~10-15 MB per request
- **Mitigation**: Use Laravel Octane, opcache optimization

### Response Time
- Custom framework: ~50-100ms average
- Laravel: ~100-200ms average
- **Mitigation**: Caching, database optimization, CDN

### Scalability
- Laravel provides better scalability with:
  - Queue workers for async processing
  - Cache layers (Redis, Memcached)
  - Session drivers for load balancing
  - Database connection pooling

## Conclusion

While the Laravel reimplementation introduces some overhead, the benefits far outweigh the costs:

**Pros:**
- ✓ Better security out of the box
- ✓ Easier maintenance and updates
- ✓ Built-in testing framework
- ✓ Modern PHP practices
- ✓ Large ecosystem and community
- ✓ Better documentation
- ✓ Easier onboarding for new developers

**Cons:**
- ✗ Slightly higher memory usage
- ✗ Learning curve for the team
- ✗ More complex deployment initially

The trade-off is worthwhile for long-term maintainability and scalability of the FreePOS project.
