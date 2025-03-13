# Laravel Inertia Static Props

## Introduction

Laravel Inertia Static Props optimizes your Inertia.js application by loading static data only once during the initial
page load. After that, static props are cached in the frontend and injected into the page props on every subsequent
visit.

This is particularly useful for data that rarely or never changes during a user session, such as:

- Translations and localization strings
- Application-wide constants and configuration settings
- Permission definitions and feature flags
- Dropdown options and select lists (countries, currencies, etc.)
- UI theme configuration and global styling variables
- Form validation rules and schema definitions
- Navigation menu structures and sidebar configurations

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
\JesseGall\InertiaStaticProps\ServiceProvider::class
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

The most common place is in your `HandleInertiaRequests` middleware, but this is not required:

```php
use JesseGall\InertiaStaticProps\StaticProp;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
  
            // Using a StaticProp instance
            'translations' => new StaticProp(fn() => [
                'pages' => Lang::get('pages'),
                'exceptions' => Lang::get('exceptions'),
                'components' => Lang::get('components'),
            ]),
            
            // Using the Inertia helper
            'enums' => Inertia::static(fn() => [
                'roleType' => RoleType::cases(),
                'userStatus' => UserStatus::cases(),   
            ]),
        ];
    }
}
```

### Manually Refreshing Static Props

Sometimes you need to refresh static props after certain actions, like changing the users locale, or when the user
permissions change.

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

### Adding Static Props to Component Renders

You can include static props when rendering components:

```php
return Inertia::render('Component', [
    'regularProp' => 'value',
    'staticPropExample' => new StaticProp(fn() => 'static value'),
]);
```

***Note:***
Static props are only sent to the client during the initial page load. If your controller is accessed after the
initial page load (e.g., navigating to a different route), you'll need to reload the static props to ensure
the static props are sent to the client. For this reason, it's generally better to share static props globally through
middleware or a service provider where they'll be consistently available.

```php
// Make sure to reload static props if the controller is accessed after the initial page load
Inertia::reloadStaticProps(); 

return Inertia::render('Component', [
    'staticProp' => new StaticProp(...),
]);
```

### How It Works

Behind the scenes, the package:

1. Identifies props wrapped in `StaticProp` during the initial page load
2. Evaluates these props and sends them to the client
3. Caches them in the frontend (browser)
4. On subsequent requests, these props will NOT be resolved on the server and are removed from the response.
5. The client-side adapter injects the cached props back into the page props before Inertia processes them, creating a seamless experience as if the server had sent them.

This results in smaller payloads and reduced server processing time for subsequent requests.

### License

MIT
