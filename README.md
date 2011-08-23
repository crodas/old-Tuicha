Tuicha
===

Tuicha is an extension to the PHP's Mongo Driver to make it more friendly.

Tuicha introduces the `MongoDocument` object, an ActiveRecord like object for each result instead of an array.

MongoCollection
---


+ `MongoDocument newDocument()`
+ `MongoDocument create()`

MongoDocument
---

Each result from a `MongoCursor` or `MongoCollection::findOne` is an object of MongoDocument rather than array. This object implements the `ArrayAccess` interface, so it can be used as an array.

`MongoDocument Save($safe=true, $sync=true)`

> It will do an insert or update. If the operation to 
> perform is an update `$set`, `$unset` or `$pull` 
> will be used instead of replace the entire object.

`Event Listener()`

> Will return an `Event` object, a simple and lightweight layer that implements the [Observer Pattern](http://en.wikipedia.org/wiki/Observer_pattern). Currently only `preSave`, `preInsert` and `preUpdate` events are supported. More events will be supported in a near future.

Event
---

+ `Event bind($name, $callback)`
+ `Event unbind($name, $callback)`
+ `Event unbindAll($name)`
+ `Event dispatch($name)`
