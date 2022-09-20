<?php

use yii\db\Migration;

/**
 * Class m201203_052936_complaints_type
 */
class m201203_052936_complaints_type extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->createTable('{{%complaints_type}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%complaints_type}}');
    }
}
