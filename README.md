# XMLMapper for Laravel and PHP

Are you working with xml data? then this package is for you. This is 
the simplest API to interact with XML data.

##  Installation

type in console:

```
composer require edujugon/xml-mapper
```

## Laravel 5.*

**Laravel 5.5 or higher?**

Then you don't have to either register or add the alias, this package uses Package Auto-Discovery's feature, and should be available as soon as you install it via Composer.

(Laravel < 5.5) Register the XMLMapper service by adding it to the providers array.
```php
'providers' => array(
    ...
    Edujugon\XMLMapper\Providers\XMLMapperServiceProvider::class
)
```

(Laravel < 5.5) Let's add the Alias facade, add it to the aliases array.
```php
'aliases' => array(
    ...
    'XMLMapper' => Edujugon\XMLMapper\Facades\XMLMapper::class,
)
```

##  Usage samples

```php
$mapper = new Edujugon\XMLMapper\XMLMapper();
$mapper->loadXML($xmlData);
```

or

```php
$mapper = new Edujugon\XMLMapper\XMLMapper($xmlData);
```

or with Laravel Facade

```php
$mapper = XMLMapper::loadXML($xmlData);
```
> Don't forget to use the facade use statement at the
top of your class: `use Edujugon\XMLMapper\Facades\XMLMapper;`

#### Get value

> You must know the tags path. Otherwise you should use [findValue](https://github.com/edujugon/XMLMapper#find-value).

```php
$value = $mapper->getvalue(['first-tag','second-tag','my-tag']);
```

The above example takes the value of the tag with name **my-tag**.

> If no parameter passed to the method it looks up the value of the first (parent) tag.

#### Get attribute

> You must know the tags path. Otherwise you should use [findAttribute](https://github.com/edujugon/XMLMapper#find-attribute).

```php
$att = $mapper->getAttribute('id',['first-tag','second-tag','my-tag']);
```

The above example returns the value of the **id** attribute in **my-tag**.

#### Get element

Get a new instance of XMLMapper but with the **tag-name** element as base xml.

```php
$newXmlMapper = $mapper->getElement('tag-name');
```

#### Get elements

Get an array of XMLMapper objects based on the **tag-name** xml element.

```php
$arrayOFXmlMappers = $mapper->getElement('tag-name');
```

#### Find value

```php
$value = $mapper->findValue('my-tag');
```

It looks for the first tag called **my-tag** and returns its value.

#### Find attribute

Get the attribute value of a tag.

```php
$att = $mapper->findAttribute('my-att','my-tag');
```

It looks for the first tag called **my-tag**, then try to find **my-att** as attribute and returns its value.

If no tag passed, it takes the first attribute matching the provided attribute name:

```php
$att = $mapper->findAttribute('my-att');
```

#### Find attribute by condition

Loop through all elements trying to match the condition/s.
When found, returns the value of the provided attribute.

```php
$att = $mapper->findAttributeWhere('my-att',['id'=>1,'dev'=> 'edu',['name','!=','john']])
```
> [Check allowed where operators](https://github.com/edujugon/XMLMapper#where-operators)

#### Find attributes of a tag

Get an object with those attributes as object properties.
First it searches the tag and then retrieves the requested attributes.

```php
$obj = $mapper->findAttributes(['att-1','att-2'],'my-tag')

$name = $obj->name;
$dev = $obj->dev;
```

If no tag provided, it takes the first tag that has those attributes and return the values.

#### Find attributes by condition

Loop through all elements trying to match the condition/s.
When found, returns an object with those attributes as object properties.

```php
$obj = $mapper->findAttributesWhere(['att-1','att-2'],['dev'=> 'edu',['name','!=','john']])
```
> [Check allowed where operators](https://github.com/edujugon/XMLMapper#where-operators)

#### Find all attributes of a tag

Get an array of objects with the tag attributes as properties

```php
$list = $mapper->findAllAttributesOf('tag-name');
```

#### Find all attributes of a tag by condition

Get an array of objects with attributes as properties matching the provided tag name and condition.

```php
$list = $mapper->findAllAttributesOfWhere('tag-name',['dev'=> 'edu',['name','!=','john']])
```
> [Check allowed where operators](https://github.com/edujugon/XMLMapper#where-operators)

#### Where operators

Allowed where syntax.

Default:
*   **key => value** pair. Will be treated as **==**
```
 ['id' => 1,'name' => 'my name']
```

Custom:
*   !=
*   !==
*   ===
*   contains
*   containsCaseInsensitive
```
[['name','!=','john'],['id','!=',7]]
[['name','contains','john']]
```

They can be combined
```
['id' => 1,['name','!=','john']]
```

#### Replace tag names

You can easily replace any tag name of the xml for an easier access.

```php
$mapper->replaceTagName(
    [
        'a10:author' => 'author',
        'a10:name' => 'name',
        'a10:updated' => 'updated'
    ]
);
```

The above snippet replaces all tags with names matching the keys and sets their values as new tag names.
Also updates the underlying object based on the new xml.

#### Merge a new xml into the existing one

You can easily merge a new xml into the existing one. It sets it as child of the provided tag.

```php
$mapper->mergeXML($newXml, 'desiredParentTag');
```

Enjoy :)