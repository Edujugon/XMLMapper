# XMLMapper

API to interact with XML data

##  Installation

##### Type in console:

```
composer require edujugon/xml-mapper
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

#### Get value

> You must know the tags path. Otherwise you should use [findValue](https://github.com/edujugon/XMLMapper#findvalue).

```php
$value = $mapper->getvalue(['first-tag',second-tag','my-tag']);
```

The above example takes the value of the tag with name **my-tag**.

> If no parameter passed to the method it looks up the value of the first (parent) tag.

#### Get attribute

> You must know the tags path. Otherwise you should use [findAttribute](https://github.com/edujugon/XMLMapper#findattribute).

```php
$att = $mapper->getAttribute('id',['first-tag',second-tag','my-tag']);
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
> [Check allowed where operators](https://github.com/edujugon/XMLMapper#whereoperators)

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
> [Check allowed where operators](https://github.com/edujugon/XMLMapper#whereoperators)

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
> [Check allowed where operators](https://github.com/edujugon/XMLMapper#whereoperators)

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
```
[['name','!=','john'],['id','!=',7]]
```

They can be combined
```
['id' => 1,['name','!=','john']]
```