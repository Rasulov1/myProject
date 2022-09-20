<?php

use yii\db\Migration;

/**
 * Class m201203_053236_complaints_type_params
 */
class m201203_053236_complaints_type_params extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->createTable('{{%complaints_type_params}}', [
            'id' => $this->primaryKey(),
            'type_id' => $this->integer()->notNull(),
            'params_id' => $this->integer()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%complaints_type_params}}');
    }
}
