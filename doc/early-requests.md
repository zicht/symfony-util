# Early request handling

Handling requests "early" means that in some cases, we have enough info to serve the request without having to boot
the entire kernel and/or container. An example of this is, if the requested URL is non-existent (which caused the 
request to route to the front-end), and it is part of `/media` (which contain uploaded files by convention), then the 
kernel can render a 404. This is implemented in the `StaticMediaNotFoundHandler`. 

Another example is, if there is no session, but some user specified information is requested in the form of an API-route
to, for example `/api/v1/user.json`, without having to boot the entire kernel, we already know how to respond to this.
For example by responding with a 404, or with an empty user object (whatever seems applicable in your case).

This can be implemented using the `RestHandler` as a basis, to route to specific controllers early, in case some specific
URL pattern is encountered. For example:

```php

public function registerEaryHandlers()
{
    $ret = parent::registerEarlyHandlers();
    $ret[]= new RestHandler(
        '/api/v2', [
            [
                'GET', 
                'status.json', 
                function (Request $request) {
                    return (new SessionStatusController())->indexAction($request);
                }
            ]
        )
    );
    return $ret;
}
```

Since the session is nearly always needed for requests like these, the Kernel boots a light weight container which 
initializes the session. To not be dependent on Yaml, XML or any other relatively heavy component, only plain PHP
is used to initialize the session. See `example/session.phps` for an example of this.
