# Spiffy
The Simple PHP Framework (Spiffy) is a PHP MVC framework built for small projects.

## Why...
I realize that there isn't a whole lot of necessity for *another* PHP framework, but this was built from scratch to serve as a portfolio piece.

I am using it to build my website and a PHP service, but you're welcome to use it too if you wish.

# Installation with Composer
In order to use SPF with composer<a href="https://getcomposer.org/">Composer</a> simply add SPF as a dependency in your composer.json file:

```json
"require": {
    "davidhamp/Spiffy": "dev-master"
}
```
You should end up with a vendor folder in your project directory which contains SPF and it's required packages, <a href="https://github.com/mustache/mustache.github.com">Mustache</a> and <a href="https://github.com/symfony/Yaml">Symfony YAML</a>

# Setup
In order to use SPF, you'll need to set up a few things in your project.

## Apache configs
You'll want to set up a rewrite rule in your Apache configs or .htacces to redirect all requests to the index.php file in your root directory.

```
RewriteEngine On
RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-f
RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-d
RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-l
RewriteRule (.*) /index.php [QSA,L]
```

## Bootstrapping Application
A typical set up is to have a bare bones index.php file that simply includes a bootstrap file from a directory outside of the web root.  The bootstrap file should define the required application constants relevant to your project as well as define namespace and Dependency Management setup.

A typical bootstrap file looks like this:

```php
namespace ProjectNamespace;

use SPF;
use SPF\Dependency\DependencyManager;

// Required Constants
define('__BASE__', __DIR__);
define('__PROJECT_NAMESPACE__', 'ProjectNamespace');

// Required autoloader inclusion
$loader = require 'vendor/autoload.php';
// Project level namespace setup
$loader->addPsr4(__PROJECT_NAMESPACE__ . '\\', __BASE__ . '/ProjectNamespace');

// Project level Dependency Managemet set up.
DependencyManager::addProviderLocation(__PROJECT_NAMESPACE__, __PROJECT_NAMESPACE__ . '\\Providers');

// Application initialization
$application = new SPF\Application();
```
Once this set up has completed, you are ready to execute your application with:

```php
$application->run();
```

# MVC Structure
Once the application runs, it will attempt to match the current requested url to a resource defined in your routes.yaml file.

Once a match is found, the corresponding controller is instantiated, and the route-defined method on the controller is called.

If the method runs succesfully, the Controllers' getContent method is called and the output is assigned to the SPF\Response body.

The Response is then sent on destruct of the php script.

## routes.yaml
This file contains all of the relevant routes in your application along with the required controllers and methods to use on those controllers.

A routes file should be formatted like this:
```yaml
'/route/one':
    controller: 'Namespace\Path\To\ControllerOne'
    method:     'methodToCall'
    requestMethod: 'GET'
    contentType: 'application/json'
    description: 'Description for the humans'
'/route/two/':
    controller: 'Namespace\Path\To\ControllerTwo'
    method: 'methodToCallToo'
    requestMethod: 'GET'
    contentType: 'text/html'
    description: 'Humans require descriptions'
```

## SPF\Core\Model
Models are objects that will house your renderable data in the SPF\Core\View.  If the route is supposed to return JSON, or if no View is set, then the model will be run through json_encode.  Models use the JsonSerializable interface in order to leverage the Annotation Engine (more on that later) in order to be able to selectively ignore properties in the Model when it's serialized.

## SPF\Core\Views
SPF\Core\Views contain template information and provide the rendering interface to the template parsing engine (in this case, Mustache).  When content is fetched from the controller, the defined Model is passed to the View's render method which is then parsed through the defined template through Mustache.  When assigning the View to the Controller, either a SPF\Core\View instance or a template path can be used.  The Controller will create a new View object as needed prior to calling render.

If no view is defined, the Model will be JSON encoded.

## SPF\Core\Controller
Controllers you write to handle your routes will have to extend SPF\Core\Controller.  Each method in your controller should correspond to a single route defined in your routes.yaml file.

When this method is run, you should at the very least set model data for rendering.  You will likely want to define a template file or create an instance SPF\Core\View as well to handle HTML output.

# Reflection Pool
The Reflection Pool is used by both the Annotation Engine and the Depenednecy Manager to cache PHP Reflection Objects used to inspect annotations.  The Dependency Manager uses these reflections to create new instances of classes and is useful when your classes have one or more dependencies on other classes.

```php
ReflectionPool::get('Namespace\Class');
```

# Annotation Engine
SPF utilizes an Annotation Engine in order to achieve annotation based behavior.  The Annotation\Engine is a static class within the framework that will analyze a specific method or property and return and AnnotationSet of all annotations contained within that element's DocBlock.

The Annotation Engine is used primarly by the Dependency Manager, but it is available to use throughout your application as you see fit.

Annotations use a kind of "namespacing" and look like this in your DocBlock:
```
@SPF:AnnotationName
```

@ denotes it as an annotation, SPF is the configurable annotation namespace and the text after the colon is the name of the Annotation.

Annotations can have zero or more parameters.  These are text separated by a single space after the AnnotationName.
```
@SPF:AnnotationName ParameterString AnotherParameter
```

To get Annotations of a given class and element:

```php
SPF\Annotations\Engine::get($subject, $type, $element);
```

* $subject can be either a class name as a string, or an instance of a class.
* $type is either 'constructor', 'method', or 'property'
* If $type is 'method' or 'property', you'll need to pass in $element, which is the method or properties name.

When you receive the annotations of a given class and element it comes back as an SPF\Annotations\AnnotationSet.  The AnnotationSet has a 'has' and a 'get' method.

SPF\Annotations\AnnotationSet->has('AnnotationName') will return true/false if the named annotation exists.
SPF\Annotations\AnnotationSet->get('AnnotationName') will return an array of all annotations that match the AnnotationName

Each annotation in the array is an array of 0 or more elements corresponding to each of the Annotation paramters.

Example:
```php
/**
 * @SPF:AnnotationExample Param1a Param2a
 * @SPF:AnnotationExample Param1b Param2b
 */
public function __construct()
{}
```

```php
$annotationSet = SPF\Annotations\Engine->get($class, 'constructor');
$annotations = $annotations->get('AnnotationExample');

print_r($annotations);
```

would result in:
```
Array (
    [0] => Array (
        [0] => Param1a
        [1] => Param2a
    )
    [1] => Array (
        [0] => Param1b
        [1] => Param2b
    )
)
```

# Dependency Management
The Dependency Manager (DM) is a static class within SPF that handles creation of singleton objects and their dependencies.

It does this through either Annotations or through Providers.

## Providers
Providers are classes that are responsible for initializing classes and resolving their dependencies.
Providers should follow the naming convention of ClassNameProvider, where ClassName is the name of the class the provider is responsible for.

These providers are kept in the SPF\Dependency\Providers Namespace in SPF, but you will have to define your own project level provider location to use
providers in your project.  An example of this was shown earlier in the Application bootstrap process.

Providers will have to extend SPF\Provider and must implement the load method.  Inside the load method, you can use the DM to fetch any required dependencies
of your class.  The return of the load method should be an instance of your class.

## Annotations
You may also utilize annotations on the class' controller to define dependencies.  This method is preferred as it is more readable and easier to understand than having to inspect a Provider.

At minimum, your class should use the **@SPF:DmManaged** annotation.  This tells the Dependency Manager that you wish to use the Dm to resolve dependencies.

From here, you can actually define a Provider if you still wish.  This is useful if you do not wish to define a static Provider location in your bootstrap, or if you wish to define a Provider outside of the defined Provider location.

```php
/**
 * @SPF:DmManaged
 * @SPF:DmProvider Path\To\Provider
 */
```

If you do not wish to use a Provider, you can define all of your depdencies:

```php
/**
 * @SPF:DmManaged
 * @SPF:DmRequires NameSpace\Dependency\One $dependency1
 * @SPF:DmRequires NameSpace\Dependency\Two $dependency2
 */
public function __construct($dependency1, $dependency2)
{
    $this->dep1 = $dependency1;
    $this->dep2 = $dependency2;
}
```

Each SPF:DmRequires should correspond to a required parameter of your class.  If you use DmRequires but do not provide all of your required class parameters, DM will throw an exception.

Lastly, if you have a simple class that does not require a constructor