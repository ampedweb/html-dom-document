<?php

namespace Future\HTMLDocument\Tests\HTMLDocument;

use Future\HTMLDocument\HTMLNodeList;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class HTMLNodeListTest extends TestCase
{
    #[Test]
    public function can_be_created_from_html()
    {
        $htmlNodeList = HTMLNodeList::fromString('<div></div><p></p>');

        $this->assertCount(2, $htmlNodeList);
    }
}
