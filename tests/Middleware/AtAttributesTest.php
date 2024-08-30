<?php

namespace Future\HTMLDocument\Tests\HTMLDocument\Middleware;

use Future\HTMLDocument\HTMLDocument;
use Future\HTMLDocument\Middleware\AtAttributes;
use PHPUnit\Framework\TestCase;

class AtAttributesTest extends TestCase
{
    /** @test */
    public function at_attributes_get_kept_in_saved_html()
    {
        $html = '<html><head></head><body><button @click="doSomething">Click me</button></body></html>';

        $dom = (new HTMLDocument())->withoutMiddleware();
        $dom->withMiddleware(new AtAttributes($dom));
        $dom->loadHTML($html);

        $this->assertSame($html, $dom->saveHTML());
    }
}
