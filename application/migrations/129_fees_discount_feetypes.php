<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Link fee discounts to specific fee types (feetype.id).
 * Empty link list = discount applies to all fee types (backward compatible).
 */
class Migration_Fees_discount_feetypes extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('fees_discount_feetypes')) {
            $this->db->query("CREATE TABLE `fees_discount_feetypes` (
                `id` int NOT NULL AUTO_INCREMENT,
                `fees_discount_id` int NOT NULL,
                `feetype_id` int NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_discount_feetype` (`fees_discount_id`,`feetype_id`),
                KEY `fees_discount_id` (`fees_discount_id`),
                KEY `feetype_id` (`feetype_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");
        }
    }

    public function down()
    {
        if ($this->db->table_exists('fees_discount_feetypes')) {
            $this->db->query('DROP TABLE `fees_discount_feetypes`');
        }
    }
}
