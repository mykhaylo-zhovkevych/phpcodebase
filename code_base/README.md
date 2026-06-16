# Laravel + React + Vite

## Setup
`laravel new laravel-react-full-stack --react --database=sqlite --pest --npm --no-boost --no-interaction`
This command creates a Laravel application with React already integrated.
It is a Laravel starter kit where React is used as the frontend layer, usually through Inertia. 
Laravel’s official React starter kit is designed for building modern React interfaces while still using Laravel routing/controllers on the backend.

A framework gives you a ready-made structure for building web applications. Instead of writing everything manually, Laravel gives you tools for:

``` text
Routing
Controllers
Database access
Authentication
Validation
Migrations
Testing
Views / frontend integration
```

### Approach 1: Laravel + React with Inertia
This is the frontend that Laravel created automatically when you used:
``` text
Browser
  ↓
Laravel route: /dashboard
  ↓
Inertia::render('Dashboard')
  ↓
React component from resources/js
  ↓
Page appears
```

``` php
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        'userName' => 'test',
    ]);
});
```

```ts
type Props = {
        username: string;
};

export default function Dashboard({userName}:Props) {
    return (
        <div>
            <h1>Dashboard</h1>
        <p>Hello, {userName}</p>
        </div>
    )
}
```
Laravel sends page props directly to React.
React does not need axios/fetch for this first page data.
So called Server-driven SPA

### Approach 2: Separate React app + Laravel API

```text
Browser
  ↓
React app
  ↓
axios/fetch request
  ↓
Laravel API route
  ↓
Laravel controller
  ↓
JSON response
  ↓
React updates UI
```


```php
// In routes/api.php
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

// In app/Http/Controllers/AuthController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        return response()->json([
            'message' => 'Login request received',
            'email' => $data['email'],
        ]);
    }
}
```

``` ts
export default function LoginForm() {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    
    async function handleSubmit(event: React.FromEvent) {
        event.preventDefault();
        
        const response = await axiosClient.post("/login", {
            email,
            password,
        })
        //console.log(response.data)
    }
    
    return (
        <form onSubmit={handleSubmit}>
            <input
                value={email}
                onChange={(event) => setEmail(event.target.value)}
                placeholder="Email"
            />
        input
                value={password}
                onChange={(event) => setPassword(event.target.value)}
                placeholder="Password"
                type="password"
            />

            <button type="submit">Login</button>
        </form>
    );
}
```
