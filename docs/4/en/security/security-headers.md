<!-- anchor: security-header-middleware -->
# Security Header Middleware

The `SecurityHeaderMiddleware` layer allows you to apply security related
headers to your application. Once setup the middleware can apply the following
headers to responses:

- `X-Content-Type-Options`
- `X-Download-Options`
- `X-Frame-Options`
- `X-Permitted-Cross-Domain-Policies`
- `Referrer-Policy`

This middleware is configured using a fluent interface before it is applied to
your application's middleware stack

```php
use Cake\Http\Middleware\SecurityHeadersMiddleware;

$securityHeaders = new SecurityHeadersMiddleware();
$securityHeaders
    ->setCrossDomainPolicy()
    ->setReferrerPolicy()
    ->setXFrameOptions()
    ->setXssProtection()
    ->noOpen()
    ->noSniff();

$middlewareQueue->add($securityHeaders);

```

:title lang=en: Security Header Middleware
:keywords lang=en: x-frame-options, cross-domain, referrer-policy, download-options, middleware, content-type-options
