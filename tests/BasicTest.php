<?php

class BasicTest extends PHPUnit_Framework_TestCase
{
    public function testInit()
    {
        global $conn1, $conn2;
        global $db1, $db2;
        $this->assertTrue($conn1 instanceof \Mongo);
        $this->assertTrue($db1 instanceof \MongoDB);
        $this->assertEquals(get_object_vars($conn1), get_object_vars($conn2));

        $db1->drop();
        $db2->drop();
    }

    public function compareFindOne($obj, $wait=true)
    {
        global $db1, $db2;

        if ($wait) {
            // wait to mongo to sincronize between the connections
            usleep(200000); 
        }

        $this->assertFalse(is_null($db2->foo->findOne($obj)));
        $this->assertFalse(is_null($db1->foo->findOne($obj)));
        $this->assertEquals($db2->foo->findOne($obj), $db1->foo->findOne($obj));

        return $db2->foo->findOne($obj);
    }

    /**
     *  @depends testInit
     */
    public function testSave()
    {
        global $db1;

        $obj = array('_id' => 1);
        // save sometime with db1
        $db1->foo->save($obj);
        $tmp = $this->compareFindOne(array('_id' => 1));

        $doc = $db1->foo->newDocument();
        $doc['_id'] = 2;
        $doc['foo'] = 'bar';
        $doc->save();

        $tmp = $this->compareFindOne(array('_id' => 2));
        $this->assertEquals($tmp['foo'], $doc['foo']);

        $doc['foo'] = array('foo', 'bar');
        $doc->save();
        $tmp = $this->compareFindOne(array('_id' => 2));
        $this->assertEquals($tmp['foo'], $doc['foo']);

        $doc['foo'] = array('foo', array(1,2), 'bar');
        $doc->save();
        $tmp = $this->compareFindOne(array('_id' => 2));
        $this->assertEquals($tmp['foo'], $doc['foo']);

        $doc['foo'] = array('foo', 'bar');
        $doc->save();
        $tmp = $this->compareFindOne(array('_id' => 2));
        $this->assertEquals($tmp['foo'], $doc['foo']);

        $doc['foo'] = array('foo' => 'zbar');
        $doc->save(true);
        $tmp = $this->compareFindOne(array('_id' => 2));
        $this->assertEquals($tmp['foo'], $doc['foo']);

        $doc['foo'] = array('bar' => 'foo');
        $doc->save();
        $tmp = $this->compareFindOne(array('_id' => 2));
        $this->assertEquals($tmp['foo'], $doc['foo']);
    }

    /**
     *  @depends testSave
     */
    public function testFindAndSave()
    {
        global $db1;

        foreach ($db1->foo->find() as $doc) {
            $doc['xx'] = time();
            $doc->save();
            $this->compareFindOne(array('_id' => $doc['_id']));
        }

        foreach ($db1->foo->find(array(), array('xx' => 1)) as $doc) {
            $this->assertTrue(isset($doc['xx']));
            $this->assertTrue(is_null($doc['xx_yy']));
            $doc['xx'] = microtime();
            $doc->save();
            $this->compareFindOne(array('_id' => $doc['_id']));
        }

        foreach ($db1->foo->find(array(), array('xx' => 1)) as $doc) {
            unset($doc['xx']);
            $doc->save();
            $this->compareFindOne(array('_id' => $doc['_id']));
        }

        foreach ($db1->foo->find() as $doc) {
            $this->assertEquals($doc['_id'], $doc->_id);
            $this->assertEquals($doc['xx'], $doc->xx);
            $doc->xx = rand();
            $doc->save();
            $this->compareFindOne(array('_id' => $doc['_id']));
        }
    }

    /**
     *  @depends testSave
     */
    public function testFindAndSaveSafe()
    {
        global $db1;

        foreach ($db1->foo->find() as $doc) {
            $doc['xx'] = time();
            $doc->save(true);
            $this->compareFindOne(array('_id' => $doc['_id']), false);
        }

        foreach ($db1->foo->find(array(), array('xx' => 1)) as $doc) {
            $doc['xx'] = microtime();
            $doc->save(true);
            $this->compareFindOne(array('_id' => $doc['_id']), false);
        }

        foreach ($db1->foo->find(array(), array('xx' => 1)) as $doc) {
            unset($doc['xx']);
            $doc->save(true);
            $this->compareFindOne(array('_id' => $doc['_id']), false);
        }

        foreach ($db1->foo->find() as $doc) {
            $this->assertEquals($doc['_id'], $doc->_id);
            $this->assertEquals($doc['xx'], $doc->xx);
            $doc->xx = rand();
            $doc->save(true);
            $this->compareFindOne(array('_id' => $doc['_id']), false);
        }
    }

}
