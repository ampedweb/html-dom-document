<?php

namespace Future\HTMLDocument\Tests\HTMLDocument;

use Future\HTMLDocument\HTMLElement;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class HTMLElementTest extends TestCase
{
    #[Test]
    public function can_be_created_from_string()
    {
        $element = HTMLElement::fromHTML('<div>Hello</div>');
        $this->assertEquals('div', $element->tagName);
        $this->assertEquals('Hello', $element->textContent);
        $this->assertEquals('<div>Hello</div>', $element->saveHTML());
    }

    #[Test]
    public function get_class_list()
    {
        $element = HTMLElement::fromHTML('<div></div>');
        $element->setAttribute('class', 'hello world');

        $this->assertCount(2, $element->getClassList());
        $this->assertSame('hello', $element->getClassList()[0]);
        $this->assertSame('world', $element->getClassList()[1]);
    }

    #[Test]
    public function can_set_class_list()
    {
        $element = HTMLElement::fromHTML('<div></div>');
        $element->setClassList(['foo', 'bar', 'baz']);

        $this->assertEquals('foo bar baz', $element->getAttribute('class'));
    }

    #[Test]
    public function can_add_to_class_list()
    {
        $element = HTMLElement::fromHTML('<div></div>');

        $element->addClass('foo');
        $this->assertEquals('foo', $element->getAttribute('class'));

        $element->addClass('bar');
        $this->assertEquals('foo bar', $element->getAttribute('class'));

        $element->addClass('baz');
        $this->assertEquals('foo bar baz', $element->getAttribute('class'));
    }

    #[Test]
    public function can_remove_from_class_list()
    {
        $element = HTMLElement::fromHTML('<div class="foo bar"></div>');

        $element->removeClass('foo');
        $this->assertEquals('bar', $element->getAttribute('class'));

        $element->removeClass('bar');
        $this->assertEquals('', $element->getAttribute('class'));

        $this->assertFalse($element->hasAttribute('class'));
    }

    #[Test]
    public function can_set_array_attributes()
    {
        $element = HTMLElement::fromHTML('<div></div>');
        $element->setAttribute('class', ['foo', 'bar', 'baz']);

        $this->assertEquals('foo bar baz', $element->getAttribute('class'));
    }

    #[Test]
    public function knows_about_void_elements()
    {
        $this->assertTrue((new HTMLElement('br'))->isVoidElement());
        $this->assertTrue((new HTMLElement('img'))->isVoidElement());
        $this->assertTrue((new HTMLElement('input'))->isVoidElement());
        $this->assertTrue((new HTMLElement('meta'))->isVoidElement());

        $this->assertFalse((new HTMLElement('div'))->isVoidElement());
        $this->assertFalse((new HTMLElement('html'))->isVoidElement());
        $this->assertFalse((new HTMLElement('p'))->isVoidElement());
        $this->assertFalse((new HTMLElement('something-weird'))->isVoidElement());
    }

    #[Test]
    public function boolean_attributes_can_be_toggled()
    {
        $element = HTMLElement::fromHTML('<input>');

        $this->assertFalse($element->hasAttribute('checked'));
        $this->assertEquals('<input>', $element->saveHTML());

        $element->toggleAttribute('checked');
        $this->assertEquals('<input checked>', $element->saveHTML());
        $this->assertTrue($element->hasAttribute('checked'));

        $element->toggleAttribute('checked');
        $this->assertFalse($element->hasAttribute('checked'));
        $this->assertEquals('<input>', $element->saveHTML());
    }

    #[Test]
    public function can_get_outer_html()
    {
        $this->assertEquals(
            '<html><p class="paragraph">Hello</p></html>',
            HTMLElement::fromHTML('<html><p class="paragraph">Hello</p></html>')->getOuterHTML(),
        );

        $this->assertEquals(
            '<html><p>Hello</p><p>Goodbye</p></html>',
            HTMLElement::fromHTML('<html><p>Hello</p><p>Goodbye</p></html>')->getOuterHTML(),
        );

        $this->assertEquals(
            '<html lang="en"><ul><li>1</li><li>2</li></ul></html>',
            HTMLElement::fromHTML('<html lang="en"><ul><li>1</li><li>2</li></ul></html>')->getOuterHTML(),
        );

        $this->assertEquals(
            '<input type="text">',
            HTMLElement::fromHTML('<input type="text">')->getOuterHTML(),
        );
    }

    #[Test]
    public function can_get_inner_html()
    {
        $this->assertEquals(
            'This is text content',
            HTMLElement::fromHTML('<div>This is text content</div>')->getInnerHTML(),
        );

        $this->assertEquals(
            '<p class="paragraph">Hello</p>',
            HTMLElement::fromHTML('<html><p class="paragraph">Hello</p></html>')->getInnerHTML(),
        );

        $this->assertEquals(
            '<p>Hello</p><p>Goodbye</p>',
            HTMLElement::fromHTML('<html><p>Hello</p><p>Goodbye</p></html>')->getInnerHTML(),
        );

        $this->assertEquals(
            '<ul><li>1</li><li>2</li></ul>',
            HTMLElement::fromHTML('<html lang="en"><ul><li>1</li><li>2</li></ul></html>')->getInnerHTML(),
        );

        $this->assertEquals(
            '',
            HTMLElement::fromHTML('<input type="text">')->getInnerHTML(),
        );
    }

    #[Test]
    public function canSaveItsOwnHTML()
    {
        $this->assertEquals(
            '<html lang="en"><ul><li>1</li><li>2</li></ul></html>',
            HTMLElement::fromHTML('<html lang="en"><ul><li>1</li><li>2</li></ul></html>')->saveHTML(),
        );

        $this->assertEquals(
            '<input type="text">',
            HTMLElement::fromHTML('<input type="text">')->saveHTML(),
        );
    }

    #[Test]
    public function canBeCastToString()
    {
        $this->assertEquals('<div>This is a <strong>test!</strong></div>', (string) HTMLElement::fromHTML('<div>This is a <strong>test!</strong></div>'));
    }

    #[Test]
    public function canGetAttributesAsArray()
    {
        $this->assertEmpty(HTMLElement::fromHTML('<div></div>')->getAttributes());
        $this->assertEquals(['id' => 'one'], HTMLElement::fromHTML('<div id="one"></div>')->getAttributes());
        $this->assertEquals(['id' => 'one', 'class' => 'two three'], HTMLElement::fromHTML('<div id="one" class="two three"></div>')->getAttributes());
    }

    #[Test]
    public function canMapRecursively()
    {
        $element = HTMLElement::fromHTML(<<<HTML
        <div>
            <p>Here, every instance of <span>Foo</span> will be replaced with Bar, unless it is in a span tag.</p>
            <p>Here is a Foo, and then here is another <u>Foo</u>.</p>
        </div>
        HTML);

        $element->mapRecursive(function ($node) {
            if ($node->nodeName === 'span') {
                return null;
            }

            if ($node->nodeType === XML_TEXT_NODE) {
                $node->textContent = str_replace('Foo', 'Bar', $node->textContent);
            }

            return $node;
        });

        $this->assertEquals(<<<HTML
        <div>
            <p>Here, every instance of <span>Foo</span> will be replaced with Bar, unless it is in a span tag.</p>
            <p>Here is a Bar, and then here is another <u>Bar</u>.</p>
        </div>
        HTML, $element->__toString());
    }

    #[Test]
    public function attributes_can_be_removed()
    {
        $element = HTMLElement::fromHTML('<div id="one" class="two three"></div>');
        $this->assertEquals('<div id="one" class="two three"></div>', $element->saveHTML());

        $element->removeAttribute('id');
        $this->assertEquals('<div class="two three"></div>', $element->saveHTML());

        $element->removeAttribute('class', 'two');
        $this->assertEquals('<div class="three"></div>', $element->saveHTML());
    }
}
