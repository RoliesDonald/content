<?php

namespace yii2tech\tests\unit\content;

use Yii;
use yii2tech\content\ActiveRecordStorage;
use yii2tech\tests\unit\content\data\ContentActiveRecord;

/**
 * @group db
 */
class ActiveRecordStorageTest extends AbstractStorageTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->setupTestDbData();
    }

    /**
     * {@inheritdoc}
     */
    protected function createStorage()
    {
        $storage = new ActiveRecordStorage();
        $storage->activeRecordClass = ContentActiveRecord::className();
        $storage->idAttribute = 'id';
        $storage->contentAttributes = [
            'title',
            'body',
        ];
        return $storage;
    }

    /**
     * Setup tables for test ActiveRecord
     */
    protected function setupTestDbData()
    {
        $db = Yii::$app->getDb();
        // Structure :
        $table = 'Content';
        $columns = [
            'id' => 'string',
            'title' => 'string',
            'body' => 'text',
            'PRIMARY KEY(id)'
        ];
        $db->createCommand()->createTable($table, $columns)->execute();
    }
}