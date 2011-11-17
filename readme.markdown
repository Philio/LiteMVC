LiteMVC
=======

The current leading MVC frameworks are bloated and slow and are riddled with bad programming techniques such as 1000's of globals and masses of singletons.  

The aim of this project is simply to provide a fast and simple, yet functional, MVC framework that takes advantage of the latest features of PHP 5.3.

Planned features:

* Framework overhead of < 5ms per page.
* Easy ini config driven application/module setup.
* Built in config caching (as ini parsing is very very slow)
* Custom session support.
* Custom error handling.
* Support for MySQL with data mapping to provide a fully OO interface.
* Support for caching with files and Memcache.