# HTML5 DOM Document

[![Latest Version on Packagist](https://img.shields.io/packagist/v/futureplc/html-dom-document.svg?style=flat-square)](https://packagist.org/packages/futureplc/html-dom-document)
[![Tests](https://img.shields.io/github/actions/workflow/status/futureplc/html-dom-document/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/futureplc/html-dom-document/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/futureplc/html-dom-document.svg?style=flat-square)](https://packagist.org/packages/futureplc/html-dom-document)

The HTMLDocument package has one primary purpose: to act as a stand-in replacement for the core `DOMDocument` and related DOM classes that come with PHP.

> ⚠️ If you just need to crawl the DOM and not manipulate it in-place, consider using a package like the [Symfony DOM Crawler component](https://symfony.com/doc/current/components/dom_crawler.html).

While the builtin DOM-related classes with PHP are a great way to parse XML, they quickly fall apart when trying to parse modern HTML5 markup. This package makes it more intuitive to work with, and handles some of the quirks behind-the-scenes.

This package provides a series of classes to replace the DOM ones in a backward-compatible fashion but with a tighter interface and additional utilities bundled in to make working with HTML a breeze. These classes will return instances of the equivalent `HTML*` class instead of the `DOM*` one:

- `DOMDocument` -> `HTMLDocument`
- `DOMElement` -> `HTMLElement`
- `DOMNode` -> `HTMLElement`
- `DOMText` -> `HTMLText`
- `DOMNodeList` -> `HTMLNodeList`
- `DOMXPath` -> `HTMLXPath`

## Installation

You can install the package via Composer:

```bash
composer require futureplc/html-dom-document
```

## Features

### Sensible return values

There's nothing more annoying than having to check union types on every operation because of PHP's legacy of using falsey return types. We've sorted this by making sure there are sensible defaults:

- If a return value expects `DOMNodeList` or `false`, we'll return an empty `DOMNodeList` if there are no values to return
-  If a return value could be a `string` or `false`, we'll either throw an exception on failure or return an empty string
- No more differentiating between `DOMNode` and `DOMElement`; we have a single `HTMLElement` class that handles all scenarios of the two combined

You'll notice this philosophy throughout the interface - if there's a sensible type to return, we'll ensure you get that instead of dealing with unions.

### Easily create HTML documents and elements

`DOMDocument` typically has a terse, antiquated interface that requires a lot of setup and repetition to do even basic and commonly needed tasks like creating a `DOMElement` class from a plain HTML string.

All the old `DOMDocument` style methods still work, so you can drop this package in as a replacement for existing `DOMDocument` implementations. However, we have added new ways to create HTML documents and elements without the verbosity usually required for some operations.

```php
$dom = new HTMLDocument(); $dom->loadHTML($html);
$dom = HTMLDocument::fromHTML($html);
$dom = HTMLDocument::loadFromFile($filePath);

$element = HTMLElement::fromNode($domNode);
$element = HTMLElement::fromHTML($html);

$element = $dom->createElement('p', 'This is a paragraph.');
$element = $dom->createElementFromNode($domNode);
$element = $dom->createElementFromHTML('<p>This is a paragraph.</p>');
```

### Additional behaviour to support HTML5

The majority of the custom behaviour to allow DOMDocument to parse any HTML string comes from a series of "middleware" classes that manipulate the HTML before it's loaded and before it's emitted as a plain HTML string again.

These middleware do various things, such as:
- Assuming HTML5 behaviour if no `<!doctype>` is present, by adding one
- Ignoring LibXML errors (as LibXML complains about certain HTML5 tags even though it can parse them properly)
- Treating `<template>` and `<script>` tags as verbatim so their contents aren't changed by the rest of the document

These will be enabled by default if you use the `HTMLDocument` class, but you can disable them as needed.
- Calling `->withoutMiddleware()` without any arguments before loading the HTML will result in no middleware applying, essentially resulting in just the additional utility methods with none of the extra HTML5 support
- Calling `->withoutMiddleware(MiddlewareName::class)`, using the class name of a middleware, will disable that specific one

Getting a plain HTML string back out of `DOMDocument` can be a bit tricky if you need something specific like a specific element, so we have added some options to make it easier.

```php
$html = (string) $dom; // Cast the HTMLDocument to a string
$html = $dom->saveHTML();

$html = (string) $element; // Cast the HTMLElement to a string
$html = $element->saveHTML();
$html = $element->getInnerHTML(); // Gets the HTML of the element without the wrapping node
$html = $element->getOuterHTML(); // Gets the HTML of the element with the wrapping node
```

### Check if HTML5

If you need to know whether you're working with an HTML5 document or not, the `isHTML5()` method will tell you.

```php
$dom->isHtml5(); // true
```

### Void elements

If working with HTML5, you may want to know if a given node is a "void element", meaning it needs no closing tag. This can be checked with the `isVoidElement()` method.

```php
$element->isVoidElement(); // true
```

Normally when saving the HTML, `DOMDocument` would output void elements as `<example></example>`, but this package will output them as `<example>`, even for custom elements, maintaining how they were input originally.

### Working with attributes

The `HTMLElement` class has a series of methods to help you work with attributes on elements.

```php
$element->getAttributes(); // Returns an array of all attributes
$element->getAttribute('class'); // Returns the value of the class attribute

$element->setAttribute('class', 'foo'); // Sets the class attribute to "foo"
$element->addAttribute('class', 'foo'); // Adds the "foo" value as a space-separated value to the class attribute, appending it if the attribute already exists

$element->removeAttribute('ref'); // Removes the ref attribute entirely
$element->removeAttribute('ref', 'noreferrer'); // Removes the "noreferrer" value from the ref attribute if it exists - if the attribute is now empty, it will be removed entirely

$element->toggleAttribute('checked'); // Toggles the "checked" attribute
```

As we often work with CSS classes in HTML, there are also some methods to help with this.

```php
$element->getClassList(); // Returns an array of CSS classes
$element->setClassList(['foo', 'bar']); // Sets the CSS classes
$element->hasClass('foo'); // Returns true if the element has the class "foo"
$element->addClass('baz'); // Adds the class "baz"
$element->removeClass('bar'); // Removes the class "bar"
```

### Removing parts of a document

There are some helpful utilities for quickly removing parts of a document as required.

```php
$element->wihoutSelector('p'); // Removes all child `<p>` element
$element->withoutComments(); // Removes all HTML comments
```

### Utility methods

There are a couple of additional utility methods to help build attribute strings from PHP arrays.

`Utility::attribute()` will take a single key/value pair and turn it into an HTML attribute, regardless of whether the value is a string, array, or boolean. A boolean value can be used to conditionally add attributes.

```php
Utility::attribute('class', ['foo', 'bar']); // class="foo bar"
Utility::attribute('id', 'baz'); // id="baz"
Utility::attribute('required', true); // disabled
```

`Utility::attributes()` will take this further by doing the same with an array of key/value pairs, turning them into an HTML attribute string altogether.

```php
Utility::attributes([
    'class' => ['foo', 'bar'],
    'id' => 'baz',
    'required' => true,
    'checked' => false,
]);

// class="foo bar" id="baz" required
```

`Utility::nodeMapRecursive()` gives the ability to run a callback on every node in a document, including all child nodes. You can use this callback to inspect the nodes, modify them, replace one node with another entirely, or remove them from the document.

This is also available on `HTMLElement` and `HTMLDocument` objects through the `mapRecursive` method.

```php
$dom = HTMLDocument::fromHTML('<p><span>foo</span></p>');

// Make sure every element has a class of "bar"
$dom->mapRecursive(function ($node) {
    if ($node instanceof HTMLElement) {
        $node->setAttribute('class', 'bar');
    }
});

// <p class="bar"><span class="bar">foo</span></p>
```

`Utility::countRootNodes()` will tell you how many root nodes are in a document.

```php
Utility::countRootNodes('<p>foo</p>'); // 1
Utility::countRootNodes('<p>foo</p><p>bar</p>'); // 2
```

If working with source HTML that contains multiple root nodes, you can use the `Utility::wrap($html)` and `Utility::unwrap($html)` methods to ensure a single root node or remove the root node, respectively.

### Working with CSS classes

The `HTMLElement` class has several methods to help you work with CSS classes.

```php
$element->setClassList(['foo', 'bar']);
$element->getClassList(); // ['foo', 'bar']
$element->hasClass('foo'); // true
$element->addClass('foo'); // ['foo', 'bar', 'baz']
$element->removeClass('baz'); // ['foo', 'bar']
```

### Toggling boolean attributes

In the case where you need to toggle some boolean attributes on or off, the `toggleAttribute()` method is available.

```php
$element = HTMLElement::fromString('<input type="checkbox">');
$element->toggleAttribute('checked'); // <input type="checkbox" checked>
$element->toggleAttribute('checked'); // <input type="checkbox">
```

### Querying on CSS selectors and XPath
Most people working with HTML know how to use most CSS selectors, but many have never touched XPath. We've added handy `querySelector()`  and `querySelectorAll()` methods to the `HTMLDocument` and `HTMLElement` classes, allowing you to use CSS selectors directly to get the needed elements, courtesy of the [Symfony CSS Selector](https://github.com/symfony/css-selector) package.

```php
$dom->querySelector('head > title'); // Returns the first `<title>` element
$dom->querySelectorAll('.foo'); // Returns all elements with the class `foo`
```

If you still need to work with XPath, there is a convenient `xpath()` method on both `HTMLDocument` and `HTMLElement` classes.

```php
$dom->xpath('//a'); // Returns all `<a>` elements
```

### Working with text nodes

Working with text nodes can be tricky if you ever want to change something in the text to another node entirely. The `replaceTextWithNode()` method on `HTMLText` lets you do just that.

This is particularly useful if you use the `Utility::nodeMapRecursive()` function, which will traverse through text nodes.

```php
$textNode->replaceTextWithNode('example', HTMLElement::fromHTML('<strong>example</strong>'));
```

### Other Notes

`HTMLDocument` also has some other benefits over `DOMDocument`:

- Tags with an XML-style namespace get maintained, whereas `DOMDocument` would typically only keep the last part of the tag name. This is useful when working with standards such as edge-side-includes and have markup such as `<esi:include src="..." />`
- Attributes starting with `@` get maintained, whereas `DOMDocument` would typically remove them. This is useful when working with HTML that has Alpine.js or Vue.js markup such as `<button @click="doSomething">`
- Any void tags on the input HTML will also be output as void tags

## Drawbacks

Because of all the extra checks and type conversions, this package is a bit slower than the native `DOMDocument` classes. However, the difference is negligible in most cases, and the benefits of the additional features and ease of use far outweigh the performance hit unless you are processing millions of large HTML documents at once.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Future PLC](https://github.com/futureplc)
- [Liam Hammett](https://github.com/imliam)
- [Chris Powell](https://github.com/ampedweb)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
