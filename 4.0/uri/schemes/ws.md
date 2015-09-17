---
layout: default
title: Websocket URIs
---

# Websockets URI

To work with websockets URIs you can use the `League\Uri\Schemes\Ws` class.
This class handles secure and non secure websockets URI.

## Validation

Websockets URIs must contain a `ws` or the `wss` scheme. It can not contain a fragment component as per [RFC6455](https://tools.ietf.org/html/rfc6455#section-3).

<p class="message-notice">Adding contents to the fragment component throws an <code>RuntimeException</code> exception</p>

~~~php
use League\Uri\Schemes\Ws as WsUri;

$uri = WsUri::createFromString('wss://thephpleague.com/path/to?here#content');
//throw an RuntimeException - a fragment component was given


$altUri = WsUri::createFromString('//thephpleague.com/path/to?here#content');
//throw an InvalidArgumentException - no scheme was given
~~~

Apart from the fragment, the websockets URIs share the same [host validation limitation](/4.0/uri/schemes/http/#validation) as Http URIs.

## Properties

Websockets URIs share the same properties and class throught PHP `__get` methods as [Http URIs](/4.0/uri/schemes/http/#properties).