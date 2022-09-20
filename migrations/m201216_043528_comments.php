<?php

use yii\db\Migration;

/**
 * Class m201216_043528_comments
 */
class m201216_043528_comments extends Migration
{

    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->createTable('{{%comments}}', [
            'id' => \yii\db\Schema::TYPE_PK,
            'comment' => $this->text()->notNull(),
            'user_id' => $this->integer(),
            'create_at' => $this->dateTime()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%comments}}');
    }

}
