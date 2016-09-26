# Bundle-dependent configuration loading

# Configuring multiple kernels for one main endpoint

To be able to support a kernel with different sets of configurations, in 1.4 a feature was introduced to construct the 
AppKernel with a name.

For example:

```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new MyCommonBundle()
        ];
        
        // ....
        switch ($this->name) {
            case 'site':
                $bundles[] = new MyOnlyUsefulOnTheFrontendBundle();            
                break;
            case 'admin':
                $bundles[] = new MyAdminBundle();            
                break;
        }
        
        return $bundles;
    }
}
```


Now, based on the request uri, you can configure different appkernels:

```php
<?php

// web/index.php

require_once __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/AppKernel.php';

if (preg_match('!^/admin/!', $_SERVER['REQUEST_URI'])) {
    (new AppKernel('admin'))->web();
} else {
    (new AppKernel('site'))->web();
}
```

Or, when utilizing NginX:

```
location ^/admin {
     fastcgi_param APPLICATION_NAME admin;
     /* ... */
}
location ^/ {
     fastcgi_param APPLICATION_NAME site;
     /* ... */
}
```

These different kernels have their own caches. This proves easier than maintaining multiple app instances separately.

Examples of practical uses for this are:
 
- Not having to initialize update/change logic such as Doctrine listeners on "read-only" portions of the website
- Not having to initialize bundles that only serve purpose in a specific scope (e.g. "admin") of the site.
- Introducing kernel parameters which hint the backend code about the current scope of their usage. Use this with care
  though, it has the tendency to become unclear which parameters are loaded when.

# Kernel configuration
Since [only configurations which are part of the kernel are loaded](bundle-config.md), this should be relatively easily
implementable. But, in case you need kernel-specific configurations (such as routing), you can add `kernel_[NAME].yml`
files to your config directory, which are loaded on top of your environment configuration.

For example (`kernel_admin.yml`):

```
framework:
    router:
        resource: "%kernel.root_dir%/config/routing_admin.yml"
```

Where the routing configuration in turn loads all routes which are necessary for the admin to work.
