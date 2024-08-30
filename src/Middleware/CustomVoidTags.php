<?php

namespace Future\HTMLDocument\Middleware;

/**
 * If a custom tag is a self-closing void tag in the input, ensure it is in the output too.
 */
class CustomVoidTags extends AbstractMiddleware
{
    public function beforeLoadHTML(string $source): string
    {
        return (string) preg_replace_callback('/<([a-zA-Z0-9\-]+)([^>]*)\/>/i', function ($matches) {
            $tagName = $matches[1];
            $attributes = $matches[2];
            return "<$tagName$attributes data-is-void-tag=\"true\"></$tagName>";
        }, $source);
    }

    public function afterSaveHTML(string $source): string
    {
        return (string) preg_replace_callback('/<([a-zA-Z0-9\-]+)([^>]*) data-is-void-tag="true"><\/\1>/i', function ($matches) {
            $tagName = $matches[1];
            $attributes = $matches[2];
            return "<$tagName$attributes />";
        }, $source);
    }
}
