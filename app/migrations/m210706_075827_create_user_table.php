<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 */
class m210706_075827_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'iduser' => $this->string(200)->notNull()->unique(),
            'token' => $this->string(200),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
