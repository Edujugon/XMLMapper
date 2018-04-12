<?php

namespace Edujugon\XMLMapper;

use Edujugon\XMLMapper\Exceptions\XMLMapperException;

class XMLMapper
{

    /** @var  string */
    protected $xml;

    /** @var \SimpleXMLElement */
    protected $obj;

    /**
     * XMLMapper constructor.
     * @param null|string $xml
     */
    function __construct($xml = null)
    {
        if($xml) {
            $this->xml = $xml;
            $this->obj = simplexml_load_string($xml);
        }
    }

    /**
     * Load a xml string to be mapped
     *
     * @param string $xml
     * @return $this
     */
    public function loadXML($xml)
    {
        $this->xml = $xml;
        $this->obj = simplexml_load_string($xml);
        return $this;
    }

    /**
     * Load a SimpleXMLElement to be mapped
     *
     * @param \SimpleXMLElement $obj
     * @return $this
     */
    public function loadObj($obj)
    {
        $this->obj = $obj;
        $this->xml = $obj->asXML();
        return $this;
    }

    /**
     * Get the underlying xml string
     *
     * @return string
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * Get the underlying SimpleXMLElement
     *
     * @return \SimpleXMLElement
     */
    public function getObj()
    {
        return $this->obj;
    }

    /**
     * Replace tag names of a xml string
     * and update the XMLMapper obj value based on the resulting xml
     *
     * @param array $names
     * @return $this
     */
    public function replaceTagName(array $names)
    {
        $search = [];
        $replace = [];
        foreach ($names as $key => $value) {
            $search[] = "<$key";
            $search[] = "</$key>";
            $replace[] = "<$value";
            $replace[] = "</$value>";
        }

        $this->loadXML(str_replace($search, $replace, $this->getXml()));

        return $this;
    }
    /**
     * Get the value of the passed attribute name
     * It loops through the path tags if provided
     *
     * @param string $name
     * @param null|array $pathTags
     * @return string|null
     */
    public function getAttribute($name, $pathTags = null)
    {
        $element = $this->getXMLElement($pathTags);
        return $this->fetchAttr($name, $element);
    }

    /**
     * Get the value of a passed tag
     * It loops through the path tags if provided
     *
     * @param null|array $pathTags
     * @return string|null
     */
    public function getValue($pathTags = null)
    {
        $element = $this->getXMLElement($pathTags);
        return $this->fetchValue($element);
    }

    /**
     * Get a new XMLMapper element matching the provided tag
     *
     * @param $tag
     * @param null $obj
     * @return null|XMLMapper
     */
    public function getElement($tag, $obj = null)
    {
        $obj = $obj ?: $this->getObj();

        if (property_exists($obj, $tag))
            return (new static())->loadObj($obj->{$tag});

        if ($obj instanceof \SimpleXMLElement) {
            foreach ($obj->children() as $key => $element) {

                if ($key === $tag)
                    return (new static())->loadObj($element);

                if ($element instanceof \SimpleXMLElement) {
                    if ($found = $this->getElement($tag, $element)) {
                        return $found;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Get an array of XMLMapper elements matching the provided tag
     *
     * @param $tag
     * @param null $obj
     * @return array|XMLMapper[]
     */
    public function getElements($tag, $obj = null)
    {
        $obj = $obj ?: $this->getObj();
        $list = [];

        if ($obj instanceof \SimpleXMLElement) {
            foreach ($obj->children() as $var => $element) {
                if ($var === $tag) {
                    $list[] = (new static())->loadObj($element);
                } else {
                    if ($found = $this->getElements($tag, $element)) {
                        $list = array_merge($list, $found);
                    }
                }
            }
        }
        return $list;
    }

    /**
     * Find tag value
     *
     * @param string $tag
     * @param null|\SimpleXMLElement $obj
     * @return null|string
     */
    public function findValue($tag, $obj = null)
    {
        $obj = $obj ?: $this->getObj();

        if ($obj instanceof \SimpleXMLElement) {
            foreach ($obj->children() as $var => $element) {
                if ($tag === $var)
                    return $this->fetchValue($element);

                if ($element instanceof \SimpleXMLElement && $element->count() > 0) {
                    $found = $this->findValue($tag, $element);

                    if (is_string($found))
                        return $found;
                }
            }
        }
        return null;
    }

    /**
     * Find attribute value
     *
     * @param $name
     * @param null|string $tag
     * @param null|\SimpleXMLElement $obj
     * @return null|string
     */
    public function findAttribute($name, $tag = null, $obj = null)
    {
        $obj = $obj ?: $this->getObj();

        $found = $this->getAttribute($name);

        if ($found) {
            return $found;
        }

        if ($obj instanceof \SimpleXMLElement) {
            foreach ($obj->children() as $var => $element) {
                if ($tag) {
                    if ($tag === $var)
                        return $this->fetchAttr($name, $element);
                } else {
                    if ($att = $this->fetchAttr($name, $element))
                        return $att;
                }
                if ($element instanceof \SimpleXMLElement && $element->count() > 0) {
                    $found = $this->findAttribute($name, $tag, $element);
                    if (is_string($found))
                        return $found;
                }
            }
        }
        return null;
    }

    /**
     * Find attribute values
     *
     * @param array $names
     * @param null|string $tag
     * @param null|\SimpleXMLElement $obj
     * @return null|\stdClass
     */
    public function findAttributes($names, $tag = null, $obj = null)
    {
        $names = is_array($names) ? $names : [$names];
        $obj = $obj ?: $this->getObj();
        $return = new \stdClass();

        if ($obj instanceof \SimpleXMLElement) {
            foreach ($obj->children() as $var => $element) {
                if ($tag) {
                    if ($tag === $var) {
                        foreach ($names as $name) {
                            if ($val = $this->fetchAttr($name, $element))
                                $return->{$name} = $val;
                        }
                    }
                } else {
                    foreach ($names as $name) {
                        if ($val = $this->fetchAttr($name, $element))
                            $return->{$name} = $val;
                    }
                }
                if (!empty(get_object_vars($return)))
                    return $return;

                if ($element instanceof \SimpleXMLElement && $element->count() > 0) {
                    $found = $this->findAttributes($names, $tag, $element);

                    if ($found instanceof \stdClass)
                        return $found;
                }
            }
        }
        return null;
    }

    /**
     * Find attributes by matching condition
     *
     * @param $name
     * @param array $where
     * @param null|\SimpleXMLElement $obj
     * @return null|string
     */
    public function findAttributeWhere($name, $where, $obj = null)
    {
        $obj = $obj ?: $this->getObj();

        if ($obj instanceof \SimpleXMLElement) {
            foreach ($obj->children() as $var => $element) {
                if ($this->checkCondition($where, $element))
                    return $this->fetchAttr($name, $element);

                if ($element instanceof \SimpleXMLElement && $element->count() > 0) {
                    $found = $this->findAttributeWhere($name, $where, $element);
                    if (is_string($found))
                        return $found;
                }
            }
        }
        return null;
    }

    /**
     * Find attributes of a tag based on a condition
     *
     * @param array $names
     * @param array $where
     * @param null|\SimpleXMLElement $obj
     * @return null|\stdClass
     */
    public function findAttributesWhere($names, $where, $obj = null)
    {
        $names = is_array($names) ? $names : [$names];
        $obj = $obj ?: $this->getObj();
        $return = new \stdClass();

        if ($obj instanceof \SimpleXMLElement) {
            foreach ($obj->children() as $var => $element) {
                if ($this->checkCondition($where, $element)) {
                    foreach ($names as $name) {
                        $val = $this->fetchAttr($name, $element);
                        if ($val) $return->$name = $val;
                    }
                }
                if (!empty(get_object_vars($return)))
                    return $return;

                if ($element instanceof \SimpleXMLElement && $element->count() > 0) {
                    $found = $this->findAttributesWhere($names, $where, $element);
                    if ($found instanceof \stdClass)
                        return $found;
                }
            }
        }
        return null;
    }

    /**
     * Find all attributes of a tag
     *
     * @param string $tag
     * @param null|\SimpleXMLElement $obj
     * @return null|array
     */
    public function findAllAttributesOf($tag, $obj = null)
    {
        $obj = $obj ?: $this->getObj();
        $return = [];

        if ($obj instanceof \SimpleXMLElement) {
            foreach ($obj->children() as $var => $element) {
                if ($var === $tag)
                    $return[] = (object)current($element->attributes());

                if ($element instanceof \SimpleXMLElement && $element->count() > 0) {
                    $found = $this->findAllAttributesOf($tag, $element);
                    if (is_array($found))
                        return $found;
                }
            }
            if (!empty($return))
                return $return;
        }
        return null;
    }

    /**
     * Find all attributes of a tag based on a condition
     *
     * @param string $tag
     * @param array $where
     * @param null|\SimpleXMLElement $obj
     * @return array|null
     */
    public function findAllAttributesOfWhere($tag, $where, $obj = null)
    {
        $obj = $obj ?: $this->getObj();
        $return = [];

        if ($obj instanceof \SimpleXMLElement) {
            foreach ($obj->children() as $var => $element) {
                if ($var === $tag) {
                    if ($this->checkCondition($where, $element))
                        $return[] = (object)current($element->attributes());
                }
                if ($element instanceof \SimpleXMLElement && $element->count() > 0) {
                    $found = $this->findAllAttributesOfWhere($tag, $where, $element);
                    if (is_array($found))
                        return $found;
                }
            }
        }
        if (!empty($return))
            return $return;

        return null;
    }

    /**
     * Merge a new xml into the existing one
     * @param $xml
     * @param string $intoTag
     */
    public function mergeXML($xml, $intoTag)
    {
        // remove xml tag declaration if any
        $xml = preg_replace('/<\?xml.*?>/', '', $xml);

        $endTag = '</' . $intoTag . '>';
        $long = strlen($endTag);

        $pos = strpos($this->getXml(), $endTag);
        $first = substr($this->getXml(), 0, $pos);
        $second = substr($this->getXml(), $pos + $long);

        $mergedXML = $first . $xml . $endTag . $second;

        $this->loadXML($mergedXML);
    }

    /**
     * Fetch an attribute value
     *
     * @param string $name
     * @param \SimpleXMLElement $element
     * @return null|string
     */
    protected function fetchAttr($name, $element)
    {
        if ($element instanceof \SimpleXMLElement) {
            if ($attributes = $element->attributes()) {
                if (property_exists($attributes, $name))
                    return (string)$attributes->{$name};
            }
        }
        return null;
    }

    /**
     * Get SimpleXMLElement
     *
     * @param array|string $pathTags
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     * @throws XMLMapperException
     */
    protected function getXMLElement($pathTags = null)
    {
        $element = $this->getObj();
        if ($pathTags) {
            $pathTags = is_array($pathTags) ? $pathTags : [$pathTags];
            foreach ($pathTags as $tag) {
                if (!property_exists($element, $tag))
                    throw new XMLMapperException('Tag "' . $tag . '" Doesn\'t exist in the provided xml');

                $element = $element->{$tag};
            }
        }
        return $element;
    }

    /**
     * @param \SimpleXMLElement $element
     * @return string|null
     */
    protected function fetchValue($element)
    {
        return $element ? (string)$element : null;
    }

    /**
     * @param $where
     * @param $element
     * @return bool
     */
    protected function checkCondition($where, $element)
    {
        $found = true;
        foreach ($where as $key => $val) {
            if (is_array($val) && count($val) === 3) {
                $found = $this->customCondition($element, $val);
            } elseif ($val != $this->fetchAttr($key, $element)) {
                $found = false;
                break;
            }
        }
        return $found;
    }

    /**
     * @param $element
     * @param $val
     * @return bool
     */
    protected function customCondition($element, $val)
    {
        $found = true;
        switch ($val[1]) {
            case '!=':
                if ($val[2] == $this->fetchAttr($val[0], $element)) {
                    $found = false;
                }
                break;
            case '!==':
                if ($val[2] === $this->fetchAttr($val[0], $element)) {
                    $found = false;
                }
                break;
            case '===':
                if ($val[2] !== $this->fetchAttr($val[0], $element)) {
                    $found = false;
                }
                break;
            case 'contains':
                if (strpos($this->fetchAttr($val[0], $element), $val[2]) === false) {
                    $found = false;
                }
                break;
            case 'containsCaseInsensitive':
                if (stripos($this->fetchAttr($val[0], $element), $val[2]) === false) {
                    $found = false;
                }
                break;
            default:
                $found = false;
        }
        return $found;
    }

    public function __sleep()
    {
        //obj property is excluded to prevent serialization issues
        return ['xml'];
    }

    public function __wakeup()
    {
        // Generate object
        $this->loadXML($this->getXml());
    }

}