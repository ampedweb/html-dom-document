<?php

namespace Future\HTMLDocument\Concerns;

use Future\HTMLDocument\HTMLDocument;
use Future\HTMLDocument\HTMLNodeList;
use Future\HTMLDocument\HTMLXPath;

trait HasXPathQuerySelectors
{
    abstract protected function getDocument(): HTMLDocument;

    public function xpath(string $xpathSelector): HTMLNodeList
    {
        $xpath = new HTMLXPath($this->getDocument());

        return $xpath->query($xpathSelector, $this->getDocument());
    }

    public function query(string $xpathSelector): HTMLNodeList
    {
        return $this->xpath($xpathSelector);
    }

    public function evaluate(string $xpathSelector): HTMLNodeList
    {
        return $this->xpath($xpathSelector);
    }
}
