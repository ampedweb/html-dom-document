<?php

namespace Future\HTMLDocument\Middleware;

/**
 * Ensure tag names with an XML-style namespace, such as `<esi:include />`
 * don't get malformed to `<include />` by DOMDocument.
 */
class NamespacedTagNames extends AbstractMiddleware
{
    public function beforeLoadHTML(string $source): string
    {
        return (string) preg_replace_callback('/<\/?([a-zA-Z]+):([a-zA-Z]+)([^>]*)\/?>/i', function ($matches) {
            $tagName = str_replace(':', '--', $matches[1] . ':' . $matches[2]);
            $tagContent = $matches[3];
            if (substr($matches[0], 1, 1) === '/') {
                return "</$tagName$tagContent>";
            } else {
                return "<$tagName$tagContent>";
            }
        }, $source);
    }

    public function afterSaveHTML(string $source): string
    {
        return (string) preg_replace_callback('/<\/?([a-zA-Z]+)--([a-zA-Z]+)([^>]*)\/?>/i', function ($matches) {
            $tagName = str_replace('--', ':', $matches[1] . '--' . $matches[2]);
            $tagContent = $matches[3];
            if (substr($matches[0], 1, 1) === '/') {
                return "</$tagName$tagContent>";
            } else {
                return "<$tagName$tagContent>";
            }
        }, $source);
    }
}
