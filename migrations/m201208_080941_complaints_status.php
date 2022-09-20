<?php

use yii\db\Migration;

/**
 * Class m201208_080941_complaints_status
 */
class m201208_080941_complaints_status extends Migration
{
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->createTable('{{%complaints_status}}', [
            'id' => \yii\db\Schema::TYPE_PK,
            'status' => $this->text()->notNull(),
            'alias' => $this->text()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%complaints_status}}');
    }
}
