## Installation

### app/config/config.yml imports
configure config parameters as needed in bundle's `config.yml`

```
imports:
    - { resource: "@GuserBundle/Resources/config/services.yml" }
    - { resource: "@GuserBundle/Resources/config/config.yml" }
```


### app/AppKernel.php load
```
	public function registerBundles()
	{
		$bundles = [
			new GuserBundle\GuserBundle(),
```


### app/config/routing.yml routing
```
guser:
    resource: '@GuserBundle/Controller/'
    type: annotation
```


### composer.json import
```
{
	"autoload": {
		"psr-4": {
			"GuserBundle\\": "src/GuserBundle"
```


app/config/security.yml intergration with app
```
    encoders:
        GuserBundle\Entity\User: 
            id: guser.crypt_password_encoder

    providers:
         guser_user_provider:
              entity:
                  class: GuserBundle:User
                  property: username

    firewalls:
            provider: guser_user_provider
            form_login:
                login_path: login
                check_path: login
                csrf_token_generator: security.csrf.token_manager
            logout:
                  path: /logout
                  target: /
```



