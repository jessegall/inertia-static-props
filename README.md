# Laravel Inertia Static Props

## Introduction

Laravel Inertia Static Props optimizes your Inertia.js application by loading static data only once during the initial
page load. After that, static props are cached in the frontend and injected into the page props on every subsequent
visit.

This is particularly useful for data that rarely or never changes during a user session, such as:

- Translations
- Enum values
- Configuration settings

By using static props, you can significantly reduce the payload size and processing time for subsequent requests,
leading to improved performance and a better user experience.

## Installation

### Backend

Install the package via Composer:

```bash
composer require jessegall/inertia-static-props
```

The package will auto-register its service provider if you're using Laravel's package auto-discovery.

Otherwise, you can manually register the service provider:

```php
JesseGall\InertiaStaticProps\ServiceProvider::class;
```

### Frontend

1. Install the frontend adapter via npm:

```bash
npm i inertia-static-props
```

2. Set up the plugin in your Inertia application:

```js
// Import the plugin
import {inertiaStaticPropsPlugin} from "inertia-static-props";

createInertiaApp({
    setup({el, App, props, plugin}) {
        createApp({render: () => h(App, props)})
            .use(plugin)
            // Add other plugins...
            .use(inertiaStaticPropsPlugin) // Register the static props plugin
            .mount(el);
    },
});
```

## Usage

### Basic Usage

You can share static props from anywhere in your application.
As long as you make sure that the static props are shared on the initial page load.

The most common place is in your `HandleInertiaRequests` middleware, but this is not required:

```php
use JesseGall\InertiaStaticProps\StaticProp;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            
            // Regular prop (sent on every request)
            'user' => fn() => UserResource::from($request->user()),
            
            // Static prop (sent only once)
            'translations' => new StaticProp(fn() => [
                'pages' => Lang::get('pages'),
                'exceptions' => Lang::get('exceptions'),
                'components' => Lang::get('components'),
            ]),
            
            // Another static prop example
            'enums' => new StaticProp(fn() => [
                'roleType' => RoleType::case(),
                'userStatus' => UserStatus::case(),   
            ]),
        ];
    }
}
```

### How It Works

Behind the scenes, the package:

1. Identifies props wrapped in `StaticProp` during the initial page load
2. Evaluates these props once and sends them to the client
3. Caches them in the frontend (browser)
4. On subsequent requests, these props are omitted from the server response
5. The client-side adapter injects the cached props back into the page props

This results in smaller payloads and reduced server processing time for subsequent requests.

## Configuration

No configuration is required to get started with Laravel Inertia Static Props.

### Manually Refreshing Static Props

Sometimes you need to refresh static props after certain actions:

```php
class LocaleController extends Controller
{
    public function update(...)
    {
        // Something that requires a static prop refresh
        ... 

        // Reload all static props
        Inertia::reloadStaticProps();
    }
}
```

### License

MIT