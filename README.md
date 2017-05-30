NewsFlash
=========
NewsFlash is a multi-source news aggregator.

Built-in sources are:

 - RSS
 - Twitter
 - MailChimp
 - Tumblr

NewsFlash may be easily extended to support other sources.

Feeds
-----
A feed is composed of one or more sources. The `getItems()` method will return
a heterogeneous collection of items suitable for display, or for embedding
into another RSS feed.

Installation
------------
Make sure the silverorange composer repository is added to the `composer.json`
for the project and then run:

```sh
composer require silverorange/news-flash
```
