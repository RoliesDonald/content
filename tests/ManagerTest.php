<?php

namespace yii2tech\tests\unit\content;

use yii2tech\content\Item;
use yii2tech\content\Manager;
use yii2tech\content\PhpStorage;
use yii2tech\content\PlaceholderRenderer;

class ManagerTest extends TestCase
{
    /**
     * @return Manager test manager instance.
     */
    protected function createManager()
    {
        return new Manager([
            'sourceStorage' => [
                'class' => PhpStorage::className(),
                'filePath' => $this->getTestFilePath() . DIRECTORY_SEPARATOR . 'source'
            ],
            'overrideStorage' => [
                'class' => PhpStorage::className(),
                'filePath' => $this->getTestFilePath() . DIRECTORY_SEPARATOR . 'override'
            ],
        ]);
    }

    /**
     * @param Manager $manager
     */
    protected function createTestSource(Manager $manager)
    {
        $storage = $manager->getSourceStorage();
        $storage->save('item1', [
            'title' => 'Item 1',
            'body' => 'Item 1 Body',
        ]);
        $storage->save('item2', [
            'title' => 'Item 2',
            'body' => 'Item 2 Body',
        ]);
    }

    // Tests :

    public function testSetupRenderer()
    {
        $manager = new Manager();

        $manager->setRenderer(['class' => PlaceholderRenderer::className()]);
        $this->assertTrue($manager->getRenderer() instanceof PlaceholderRenderer);

        $renderer = new PlaceholderRenderer();
        $manager->setRenderer($renderer);
        $this->assertSame($renderer, $manager->getRenderer());
    }

    public function testSetupSourceStorage()
    {
        $manager = new Manager();

        $manager->setSourceStorage(['class' => PhpStorage::className()]);
        $this->assertTrue($manager->getSourceStorage() instanceof PhpStorage);

        $storage = new PhpStorage();
        $manager->setSourceStorage($storage);
        $this->assertSame($storage, $manager->getSourceStorage());
    }

    public function testSetupOverrideStorage()
    {
        $manager = new Manager();

        $manager->setOverrideStorage(['class' => PhpStorage::className()]);
        $this->assertTrue($manager->getOverrideStorage() instanceof PhpStorage);

        $storage = new PhpStorage();
        $manager->setOverrideStorage($storage);
        $this->assertSame($storage, $manager->getOverrideStorage());
    }

    /**
     * @depends testSetupSourceStorage
     * @depends testSetupOverrideStorage
     */
    public function testGet()
    {
        $manager = $this->createManager();
        $this->createTestSource($manager);

        $item = $manager->get('item1');
        $this->assertTrue($item instanceof Item);
        $this->assertEquals('item1', $item->id);
        $this->assertSame($manager, $item->manager);
        $this->assertEquals('Item 1', $item->get('title'));
        $this->assertEquals('Item 1 Body', $item->get('body'));

        $this->expectException('yii2tech\content\ItemNotFoundException');
        $manager->get('un-existing');
    }

    /**
     * @depends testGet
     */
    public function testSave()
    {
        $manager = $this->createManager();
        $this->createTestSource($manager);

        $manager->save('item1', [
            'title' => 'override'
        ]);

        $item = $manager->get('item1');
        $this->assertEquals('override', $item->get('title'));
    }

    /**
     * @depends testSave
     */
    public function testReset()
    {
        $manager = $this->createManager();
        $this->createTestSource($manager);

        $manager->save('item1', [
            'title' => 'override'
        ]);

        $manager->reset('item1');

        $item = $manager->get('item1');
        $this->assertEquals('Item 1', $item->get('title'));
    }

    /**
     * @depends testSave
     */
    public function testGetAll()
    {
        $manager = $this->createManager();
        $this->createTestSource($manager);

        $manager->save('item1', [
            'title' => 'override'
        ]);

        $items = $manager->getAll();
        $this->assertCount(2, $items);
        $this->assertTrue(isset($items['item1'], $items['item2']));
        $this->assertEquals('override', $items['item1']->get('title'));
        $this->assertEquals('Item 2', $items['item2']->get('title'));
    }

    /**
     * @depends testGetAll
     */
    public function testMetaData()
    {
        $manager = $this->createManager();
        $this->createTestSource($manager);

        $manager->metaDataContentParts = ['body'];
        $item = $manager->get('item1');

        $this->assertFalse($item->has('body'));

        $items = $manager->getAll();
        $this->assertFalse($items['item1']->has('body'));

        $this->assertEquals(['body' => 'Item 1 Body'], $manager->getMetaData('item1'));

        $manager->save('new-item', [
            'title' => 'new item'
        ]);
        $this->assertEquals([], $manager->getMetaData('new-item'));
    }
}