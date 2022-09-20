<?php

use yii\db\Migration;

/**
 * Class m201203_053334_complaints_params_value
 */
class m201203_053334_complaints_params_value extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->createTable('{{%complaints_params_value}}', [
            'id' => $this->primaryKey(),
            'value' => $this->string()->notNull(),
            'params_id' => $this->integer()->notNull(),
            'default' => $this->integer()->notNull()->defaultValue(0),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%complaints_params_value}}');
    }
}
