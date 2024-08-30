<?php

namespace Future\HTMLDocument\Tests\HTMLDocument\Middleware;

use Future\HTMLDocument\HTMLDocument;
use Future\HTMLDocument\Middleware\CustomVoidTags;
use PHPUnit\Framework\TestCase;

class CustomVoidTagsTest extends TestCase
{
    /** @test */
    public function custom_void_tags_on_input_are_void_on_output()
    {
        $html = '<html><head></head><body><void-tag /><not-a-void-tag></not-a-void-tag></body></html>';

        $dom = (new HTMLDocument())->withoutMiddleware();
        $dom->withMiddleware(new CustomVoidTags($dom));
        $dom->loadHTML($html);

        $this->assertSame($html, $dom->saveHTML());
    }
}
