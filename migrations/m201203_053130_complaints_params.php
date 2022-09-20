<?php

use yii\db\Migration;

/**
 * Class m201203_053130_complaints_params
 */
class m201203_053130_complaints_params extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->createTable('{{%complaints_params}}', [
            'id' => $this->primaryKey(),
            'text' => $this->string()->notNull(),
            'render' => $this->string()->notNull(),
            'surely' => $this->smallInteger()->defaultValue(0),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%complaints_params}}');
    }
}
