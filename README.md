Model Change Tracking Extension for Yii2
========================================

This extension provides Yii2 model data and state change tracking.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://poser.pugx.org/yii2tech/model-change/v/stable.png)](https://packagist.org/packages/yii2tech/model-change)
[![Total Downloads](https://poser.pugx.org/yii2tech/model-change/downloads.png)](https://packagist.org/packages/yii2tech/model-change)
[![Build Status](https://travis-ci.org/yii2tech/model-change.svg?branch=master)](https://travis-ci.org/yii2tech/model-change)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yii2tech/model-change
```

or add

```json
"yii2tech/model-change": "*"
```

to the require section of your composer.json.


Usage
-----

This extension provides Yii2 model data and state change tracking. It provides solution, which works around the model classes,
allowing their events tracking from external, allowing enabling or disabling it at will.

Imagine following use case: we composing complex web pages, which content is based on database records. So we have an
administration panel, which provide setup for particular page content parts, like 'header', 'footer' etc, as well as
custom pages and main menu items. In order to keep high performance we widely use cache for different pages and page parts,
avoiding regular database queries for page contents. However, once some record is changed from admin panel the cache should
be invalidated, so changes may actually appear at the side. But it is not very practical to clear entire cache per each
content database record change as system administrator may edit several records during single user session and only after
he consider all changes are done cache should be cleared. Thus instead of clearing cache we want simply show some notification
to the user at web interface, which should remind him that cache should be cleared before changes will appear at the main site.

It is not good to place such functionality inside the model classes using model events or behaviors as functionality will
affect only administration panel and should not consume resources at main or console application. This means that model
event handlers, which respond model saving and deletion, should be assigned dynamically from outside.


## Controller Filter <span id="controller-filter"></span>

The use case described above can be solved using [[\yii2tech\modelchange\ModelChangeFilter]]. As a filter it can be attached
either to the controller or module (including application itself).


## Creating Custom Solution <span id="creating-custom-solution"></span>

This extension provides [[\yii2tech\modelchange\ModelChangeTrait]] trait, which contains basic functionality needed
for creation your own external model change tracker.