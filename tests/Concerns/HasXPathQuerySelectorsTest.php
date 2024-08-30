<?php

namespace Future\HTMLDocument\Tests\HTMLDocument\Middleware;

use Future\HTMLDocument\HTMLDocument;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class HasXPathQuerySelectorsTest extends TestCase
{
    #[Test]
    public function documents_can_extract_with_xpath_selectors()
    {
        $dom = HTMLDocument::loadFromFile(__DIR__ . '/../fixtures/example.html');

        $this->assertSame('Meta title', $dom->xpath('//title')->item(0)->textContent);
        $this->assertSame('Meta title', $dom->query('//title')->item(0)->textContent);
        $this->assertSame('Meta title', $dom->evaluate('//title')->item(0)->textContent);
    }
}
