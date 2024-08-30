<?php

namespace Future\HTMLDocument\Tests\HTMLDocument\Middleware;

use Future\HTMLDocument\HTMLDocument;
use Future\HTMLDocument\Middleware\CustomVoidTags;
use Future\HTMLDocument\Middleware\NamespacedTagNames;
use PHPUnit\Framework\TestCase;

class NamespacedTagNamesTest extends TestCase
{
    /** @test */
    public function namespaced_tag_names_keep_their_namespace()
    {
        $originalHtml = '<html><head></head><body><esi:include src="..." /></body></html>';
        $expectedHtml = '<html><head></head><body><esi:include src="..."></esi:include></body></html>';

        $dom = (new HTMLDocument())->withoutMiddleware();
        $dom->withMiddleware(new NamespacedTagNames($dom));
        $dom->loadHTML($originalHtml);

        $this->assertSame($expectedHtml, $dom->saveHTML());
    }

    /** @test */
    public function void_namespaced_tag_names_keep_their_namespace_and_are_output_as_void_elements()
    {
        $html = '<html><head></head><body><esi:include src="..." /></body></html>';

        $dom = (new HTMLDocument())->withoutMiddleware();
        $dom->withMiddleware(new NamespacedTagNames($dom));
        $dom->withMiddleware(new CustomVoidTags($dom));
        $dom->loadHTML($html);

        $this->assertSame($html, $dom->saveHTML());
    }
}
