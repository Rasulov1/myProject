<?php

use yii\db\Migration;

/**
 * Class m200910_082051_complaints
 */
class m201203_073726_complaints extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->createTable('{{%complaints}}', [
            'id' => \yii\db\Schema::TYPE_PK,
            'title' => $this->text()->notNull(),
            'message' => $this->text()->notNull(),
            'user_id' => $this->integer(),
            'create_at' => $this->dateTime()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'type_id' => $this->integer()->notNull(),
            'params' => $this->string()->notNull(),
            'worker_id' => $this->integer()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%complaints}}');
    }
}
