<?php

use yii\db\Migration;

/**
 * Class m200910_035952_users
 */
class m200910_035952_users extends Migration
{

    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->createTable('{{%user}}', [
            'id' => \yii\db\Schema::TYPE_PK,
            'fio' => $this->string()->notNull()->unique(),
            'phone' => $this->string()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'email' => $this->string()->notNull()->unique(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'create_at' => $this->integer()->notNull(),
            'company_alias' => $this->string(),
            'company' => $this->string(),
            'new_user' => $this->boolean()->defaultValue(true),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%user}}');
    }

}
