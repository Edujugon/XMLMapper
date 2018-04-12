<?php

class XMLMapperTest extends \PHPUnit_Framework_TestCase
{

    /** @var  \Edujugon\XMLMapper\XMLMapper */
    protected $mapper;

    protected function setUp()
    {
        $this->mapper = new Edujugon\XMLMapper\XMLMapper();
    }

    /** @test */
    public function instance_has_xml_and_object()
    {
        $xml = '<xml><content></content></xml>';

        $obj = simplexml_load_string($xml);

        $this->mapper->loadXML($xml);

        $this->assertEquals($xml,$this->mapper->getXml());

        $this->assertEquals($obj,$this->mapper->getObj());
    }

    /** @test */
    public function get_value_from_content_element()
    {
        $xml = '<xml id="33"><content att="something">edujugon</content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('edujugon',$this->mapper->getvalue('content'));
    }

    /** @test */
    public function throw_exception_when_path_is_wrong()
    {
        $xml = '<xml id="33"><content att="something"><first><second><my-value>edujugon</my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->setExpectedException(\Edujugon\XMLMapper\Exceptions\XMLMapperException::class);

        $this->assertEquals('edujugon',$this->mapper->getvalue(['content','i-dont-exists','second','my-value']));
    }

    /** @test */
    public function get_value_from_a_deep_element()
    {
        $xml = '<xml id="33"><content att="something"><first><second><my-value>edujugon</my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('edujugon',$this->mapper->getvalue(['content','first','second','my-value']));
    }

    /** @test */
    public function get_attribute_from_first_node()
    {
        $xml = '<xml id="33"><content att="something"><first><second><extras><extra name="wrong" id="2" dev="other"></extra><extra name="edujugon" id="1" dev="edu"></extra></extras></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('33',$this->mapper->getAttribute('id'));
    }

    /** @test */
    public function get_attribute_from_child_node()
    {
        $xml = '<xml id="33"><content att="something"><first><second><extras><extra name="wrong" id="2" dev="other"></extra><extra name="edujugon" id="1" dev="edu"></extra></extras></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('wrong',$this->mapper->getAttribute('name',['content','first','second','extras','extra']));
    }

    /** @test */
    public function find_value_of_a_element()
    {
        $xml = '<xml id="33"><content att="something"><first><second><edu><e><d><u><edu></edu></u></d></e></edu><my-value>edujugon</my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('edujugon',$this->mapper->findValue('my-value'));
    }

    /** @test */
    public function find_value_returns_null()
    {
        $xml = '<xml id="33"><content att="something"><first><second><edu><e><d><u><edu></edu></u></d></e></edu><my-value>edujugon</my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertNull($this->mapper->findValue('no-exists'));
    }

    /** @test */
    public function find_attribute_of_a_element()
    {
        $xml = '<xml id="33"><content att="something"><first name="other"><second><my-value name="edujugon"></my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('edujugon',$this->mapper->findAttribute('name','my-value'));
    }

    /** @test */
    public function find_attribute_returns_null()
    {
        $xml = '<xml id="33"><content att="something"><first><second><edu><e><d><u><edu></edu></u></d></e></edu><my-value name="edujugon"></my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertNull($this->mapper->findAttribute('name','no-exists'));
    }

    /** @test */
    public function find_attribute_by_name()
    {
        $xml = '<xml id="33"><content att="something"><first><second><edu><e><d><u><edu></edu></u></d></e></edu><my-value name="edujugon"></my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('33',$this->mapper->findAttribute('id'));
        $this->assertEquals('something',$this->mapper->findAttribute('att'));
        $this->assertEquals('edujugon',$this->mapper->findAttribute('name'));
    }

    /** @test */
    public function find_attribute_by_where()
    {
        $xml = '<xml id="33"><content att="something"><first><second><edu><e><d><u><edu></edu></u></d></e></edu><my-value name="edujugon" id="1" dev="edu"></my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('edujugon',$this->mapper->findAttributeWhere('name',['id'=>1,'dev'=> 'edu',['name','!=','john']]));
    }

    /** @test */
    public function find_attribute_by_where_contains()
    {
        $xml = '<xml id="33"><content att="something"><first><second><edu><e><d><u><edu></edu></u></d></e></edu><my-value name="edujugon" id="1" dev="eduardo"></my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('edujugon',$this->mapper->findAttributeWhere('name',[['dev','contains','edu']]));
    }

    /** @test */
    public function find_attribute_by_where_contains_case_sensitive()
    {
        $xml = '<xml id="33"><content att="something"><first><second><edu><e><d><u><edu></edu></u></d></e></edu><my-value name="edujugon" id="1" dev="eduardo"></my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertNull($this->mapper->findAttributeWhere('name',[['dev','contains','Edu']]));
    }

    /** @test */
    public function find_attribute_by_where_contains_case_insensitive()
    {
        $xml = '<xml id="33"><content att="something"><first><second><edu><e><d><u><edu></edu></u></d></e></edu><my-value name="edujugon" id="1" dev="eduardo"></my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('edujugon',$this->mapper->findAttributeWhere('name',[['dev','containsCaseInsensitive','Edu']]));
    }

    /** @test */
    public function find_attribute_by_where_with_multiple_extras()
    {
        $xml = '<xml id="33"><content att="something"><first><second><extras><extra name="wrong" id="2" dev="other"></extra><extra name="edujugon" id="1" dev="edu"></extra></extras></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('edujugon',$this->mapper->findAttributeWhere('name',[['id','!=',2],'dev'=> 'edu']));
    }

    /** @test */
    public function find_attributes_without_tag()
    {
        $xml = '<xml id="33"><content att="something"><first><second><extras><extra name="edujugon" id="1" dev="edu"></extra></extras></second></first></content></xml>';

        $this->mapper->loadXML($xml);
        $this->assertInstanceOf(\stdClass::class,$this->mapper->findAttributes(['name','dev']));
        $this->assertEquals('edujugon',$this->mapper->findAttributes(['name','dev'])->name);
        $this->assertEquals('edu',$this->mapper->findAttributes(['name','dev'])->dev);
    }

    /** @test */
    public function find_attributes_with_tag()
    {
        $xml = '<xml id="33"><content att="something"><first name="other" id="2" dev="john"><second><extras><extra name="edujugon" id="1" dev="edu"></extra></extras></second></first></content></xml>';

        $this->mapper->loadXML($xml);
        $this->assertInstanceOf(\stdClass::class,$this->mapper->findAttributes(['name','dev'],'extra'));
        $this->assertEquals('edujugon',$this->mapper->findAttributes(['name','dev'],'extra')->name);
        $this->assertEquals('edu',$this->mapper->findAttributes(['name','dev'],'extra')->dev);
    }

    /** @test */
    public function find_attributes_where_condition()
    {
        $xml = '<xml id="33"><content att="something"><first><second><extras><extra name="f" id="2" dev="a"></extra><extra name="edujugon" id="1" dev="edu"></extra></extras></second></first></content></xml>';

        $this->mapper->loadXML($xml);
        $this->assertInstanceOf(\stdClass::class,$this->mapper->findAttributesWhere(['name','dev'],['id'=>'1']));
        $this->assertEquals('edujugon',$this->mapper->findAttributesWhere(['name','dev'],['id'=>'1'])->name);
        $this->assertEquals('edu',$this->mapper->findAttributesWhere(['name','dev'],['id'=>'1'])->dev);
    }

    /** @test */
    public function get_all_attr_for_a_node()
    {
        $xml = '<xml id="33"><content att="something"><first><second><extras><extra name="f" id="2" dev="a"></extra><extra name="edujugon" id="1" dev="edu"></extra></extras></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $result = $this->mapper->findAllAttributesOf('extra');

        $this->assertCount(2,$result);
        $this->assertEquals('edujugon',$result[1]->name);
    }

    /** @test */
    public function get_all_attr_by_condition()
    {
        $xml = '<xml id="33"><content att="something"><first><second><extras><extra name="f" id="2" company="acne"></extra><extra name="edujugon" id="1" company="acne"></extra></extras></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $result = $this->mapper->findAllAttributesOfWhere('extra',['company'=>'acne']);

        $this->assertCount(2,$result);
        $this->assertEquals('edujugon',$result[1]->name);
    }

    /** @test */
    public function get_an_element_by_tag()
    {
        $xml = $this->loadXML();

        $this->mapper->loadXML($xml);

        $result = $this->mapper->getElement('book2');

        $this->assertInstanceOf(\Edujugon\XMLMapper\XMLMapper::class,$result);
        $this->assertEquals('WEB',$result->getAttribute('category'));
    }

    /** @test */
    public function returns_null_when_wrong_tag_name()
    {
        $xml = $this->loadXML();

        $this->mapper->loadXML($xml);

        $result = $this->mapper->getElement('books');

        $this->assertNull($result);
    }

    /** @test */
    public function get_all_elements_by_tag()
    {
        $xml = $this->loadXML();

        $this->mapper->loadXML($xml);

        $result = $this->mapper->getElements('book');

        $this->assertInternalType('array',$result);
        $this->assertCount(4,$result);
        foreach ($result as $item) {
            $this->assertInstanceOf(\Edujugon\XMLMapper\XMLMapper::class,$item);
        }
    }

    /** @test */
    public function replate_tag_name()
    {
        $xml = $this->loadXMLWithNamespace();

        $this->mapper->loadXML($xml);

        $this->mapper->replaceTagName(
            [
                'a10:author' => 'author',
                'a10:name' => 'name',
                'a10:updated' => 'updated'
            ]
        );
        $result = $this->mapper->getElement('item');

        $this->assertInstanceOf(\Edujugon\XMLMapper\XMLMapper::class,$result);

        $name = $result->findValue('name');

        $this->assertEquals('Netybox Group',$name);
    }

    /** @test */
    public function merge_xmls()
    {
        $first = $this->loadXML();
        $second = '<?xml id="33"?><content att="something"><first><second><extras><extra name="f" id="2" company="acne"></extra><extra name="edujugon" id="1" company="acne"></extra></extras></second></first></content>';

        $this->mapper->loadXML($first);

        $this->mapper->mergeXML($second,'bookstore');

        $this->assertInstanceOf(\Edujugon\XMLMapper\XMLMapper::class, $this->mapper->getElements('extra')[0]);
        $this->assertEquals('acne', $this->mapper->findAttributeWhere('company',['name'=>'f','id'=>'2']));
    }

    private function loadXMLWithNamespace()
    {
        return '<?xml version="1.0" encoding="utf-8"?>
        <rss xmlns:a10="http://www.w3.org/2005/Atom" version="2.0">
        <item>
        <guid isPermaLink="false">123123</guid>
        <link>
        mylink.com
        </link>
        <a10:author>
        <a10:name>Netybox Group</a10:name>
        </a10:author>
        <category>javascript</category>
        <category>html</category>
        <category>angularjs</category>
        <title>
        Senior JavaScript Developer
        </title>
        <description>
        <p>Netybox is looking for a Senior JavaScript Developer </p>
        </description>
        <pubDate>Wed, 27 Dec 2017 00:56:02 Z</pubDate>
        <a10:updated>2017-12-27T00:56:02Z</a10:updated>
        </item>
        </rss>';
    }

    private function loadXML()
    {
        return '<?xml version="1.0" encoding="utf-8"?>
        <bookstore>
          <book category="COOKING">
            <title lang="en">Everyday Italian</title>
            <author>Giada De Laurentiis</author>
            <year>2005</year>
            <price>30.00</price>
          </book>
          <book category="CHILDREN">
            <title lang="en">Harry Potter</title>
            <author>J K. Rowling</author>
            <year>2005</year>
            <price>29.99</price>
          </book>
          <section>
              <book category="WEB">
                <title lang="en-us">XQuery Kick Start</title>
                <author>James McGovern</author>
                <year>2003</year>
                <price>49.99</price>
              </book>
              <book category="WEB">
                <title lang="en-us">Learning XML</title>
                <author>Erik T. Ray</author>
                <year>2003</year>
                <price>39.95</price>
              </book>
          </section>
          <subsection>
              <book2 category="WEB">
                <title lang="en-us">XQuery Kick Start</title>
                <author>James McGovern</author>
                <year>2003</year>
                <price>49.99</price>
              </book2>
              <book2 category="WEB">
                <title lang="en-us">Learning XML</title>
                <author>Erik T. Ray</author>
                <year>2003</year>
                <price>39.95</price>
              </book2>
          </subsection>
        </bookstore>';
    }
}