<?php

class EventTest extends PHPUnit_Framework_TestCase
{
    protected $event;

    public function testInit()
    {
        global $event, $eventUpdate, $eventInsert;
        $that = $this;
        if (empty($event)) {
            $event = function($args) use ($that) {
                $that->assertTrue(!empty($args['document']['foo']));
                $that->assertTrue(!empty($args['collection']));
                $that->assertTrue($args['collection'] instanceof MongoCollection);
                $that->assertTrue($args['collection'] instanceof \Tuicha\MongoCollection);
                $that->assertTrue($args['collection']->getName() == "events");
                $GLOBALS['i']+=1;
            };
            $eventInsert = function($args) use ($that) {
                $that->assertTrue(!empty($args['document']['foo']));
                $that->assertTrue(!empty($args['collection']));
                $that->assertTrue($args['collection'] instanceof MongoCollection);
                $that->assertTrue($args['collection'] instanceof \Tuicha\MongoCollection);
                $that->assertTrue($args['collection']->getName() == "events");
                $GLOBALS['i']+=2;
            };
            $eventUpdate = function($args) use ($that) {
                $that->assertTrue(!empty($args['document']['$set']));
                $that->assertTrue(!empty($args['collection']));
                $that->assertTrue($args['collection'] instanceof MongoCollection);
                $that->assertTrue($args['collection'] instanceof \Tuicha\MongoCollection);
                $that->assertTrue($args['collection']->getName() == "events");
                $GLOBALS['i']+=3;
            };

            Tuicha\MongoDocument::Listener()
                ->bind('preInsert.events', $eventInsert)
                ->bind('preUpdate.events', $eventUpdate);
        }
        Tuicha\MongoDocument::Listener()
            ->bind('preSave.events', $event);
    }

    /**
     *  @expectedException \RuntimeException
     */
    public function testInvalidCallback() 
    {
        Tuicha\MongoDocument::Listener()
            ->bind('preSave.events', false);
    }

    /**
     *  @depends testInit
     */
    public function testunbind()
    {
        $this->assertFalse(Tuicha\MongoDocument::Listener()
            ->unbind('preSave.events', function() { }));

        $this->assertTrue(Tuicha\MongoDocument::Listener()
            ->unbind('preSave.events', $GLOBALS['event']));

        $this->assertFalse(Tuicha\MongoDocument::Listener()
            ->unbindAll('preSave.events'));

        // rebind event
        $this->testInit();

        $this->assertTrue(Tuicha\MongoDocument::Listener()
            ->unbindAll('preSave.events'));

        // rebind event
        $this->testInit();
    }

    /**
     *  @depends testInit
     */
    public function testSave() 
    {
        global $db1, $i;
        $doc = $db1->events->newDocument();
        $doc->foo = 1;

        $i = $e = rand();
        $doc->save();
        $this->assertEquals($e+ (1+2), $i);

        $doc->bar = 1;

        $i = $e = rand();
        $doc->save();
        $this->assertEquals($e+ (1+3), $i);
    }
}
